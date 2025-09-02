<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.txt
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to a newer
 * versions in the future. If you wish to customize this module for your needs
 * please refer to CustomizationPolicy.txt file inside our module for more information.
 *
 * @author Webkul IN
 * @copyright Since 2010 Webkul
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

/**
 * AdminWABCampaignController class
 *
 * This class is used to manage campaigns
 */
class AdminWABCampaignController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;
        $this->lang = true;
        $this->table = 'wk_wab_campaign';
        $this->className = 'WkWABCampaign';
        $this->identifier = 'id_wk_wab_campaign';
        $this->_orderBy = $this->identifier;
        $this->_orderWay = 'ASC';
        $this->list_no_link = true;
        $this->context = Context::getContext();
        if (Shop::getContext() == Shop::CONTEXT_SHOP) {
            $idShop = min(Shop::getContextListShopID());
            $this->_where = ' AND a.`id_shop` = ' . $idShop;
        }
        if (Shop::getContext() == Shop::CONTEXT_GROUP) {
            $idShops = Shop::getContextListShopID();
            $this->_where = ' AND a.`id_shop` IN (' . implode(',', $idShops) . ')';
        }
        $this->toolbar_title = $this->l('Campaigns');
        $this->fields_list = [
            'id_wk_wab_campaign' => [
                'title' => $this->l('ID'),
                'align' => 'center',
                'havingFilter' => true,
            ],
            'campaign_name' => [
                'title' => $this->l('Campaign name'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
                'havingFilter' => true,
                'callback' => 'getCampaignName',
            ],
            'status' => [
                'title' => $this->l('Status'),
                'align' => 'center',
                'type' => 'text',
                'search' => false,
                'callback' => 'getTemplateStatus',
            ],
            'expiry_date' => [
                'title' => $this->l('Expiry date'),
                'align' => 'center',
                'havingFilter' => true,
            ],
        ];
    }

    public function initContent()
    {
        if (!WkWABHelper::isModuleConfigured()) {
            $this->warnings[] = $this->l('Please configure this module first.');

            return false;
        }
        parent::initContent();
    }

    public function getCampaignName($val)
    {
        return Tools::ucwords(str_replace('_', ' ', $val));
    }

    public function getTemplateStatus($val, $col)
    {
        if (WkWABHelper::isModuleConfigured() == false) {
            $this->context->controller->errors[] = $this->l('Please configure the module first');
        }
        $camapaignNameOnApi = $col['campaign_name'];
        if (isset($val, $col)) {
            $expiryDate = $col['expiry_date'];
            if (strtotime($expiryDate) > strtotime(date('Y-m-d h:i:sa'))) {
                $objWABTpl = new WkWhatsAppTemplate();
                $tplDetail = $objWABTpl->getWhatsAppTemplates($camapaignNameOnApi);
                if ($tplDetail['success'] == true) {
                    if (isset($tplDetail['success'], $tplDetail['response']['data'], $tplDetail['response']['data'][0], $tplDetail['response']['data'][0]['status'])
                    ) {
                        if (!empty($tplDetail['response']['data'])) {
                            if ($tplDetail['response']['data'][0]['status'] == 'APPROVED') {
                                return $this->l('Approved');
                            } elseif ($tplDetail['response']['data'][0]['status'] == 'PENDING') {
                                return $this->l('Pending');
                            } elseif ($tplDetail['response']['data'][0]['status'] == 'REJECTED') {
                                return $this->l('Rejected');
                            }
                        }
                    } else {
                        return $this->l('Drafted');
                    }
                } elseif ($tplDetail['success'] == false) {
                    if (isset($tplDetail['response'], $tplDetail['response']['error'], $tplDetail['response']['error']['message'])
                    ) {
                        $this->context->controller->errors[] = $tplDetail['response']['error']['message'];
                    }
                }
            } else {
                return $this->l('EXPIRED');
            }
        }
    }

    public function initPageHeaderToolbar()
    {
        if (WkWABHelper::isModuleConfigured()) {
            if (empty($this->display)) {
                $this->page_header_toolbar_btn['Add'] = [
                    'href' => self::$currentIndex . '&addwk_wab_campaign&token=' . $this->token,
                    'desc' => $this->l('Add'),
                    'icon' => 'process-icon-new',
                ];
            } elseif ($this->display == 'edit' || 'add') {
                $this->page_header_toolbar_btn['Back'] = [
                    'href' => self::$currentIndex . '&token=' . $this->token,
                    'desc' => $this->l('Back'),
                    'icon' => 'process-icon-back',
                ];
            }
        }
        parent::initPageHeaderToolbar();
    }

    public function renderList()
    {
        $this->addRowAction('send');
        $this->addRowAction('edit');
        $this->context->smarty->assign([
            'loaderPopUpImg' => _MODULE_DIR_ . 'wkwhatsappbusiness/views/img/loading.gif',
        ]);
        $modal = $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'wkwhatsappbusiness/views/templates/admin/confimation.tpl'
        );

        return parent::renderList() . $modal;
    }

    public function displayEditLink($token = null, $id, $name = null)
    {
        $url = self::$currentIndex . '&updatewk_wab_campaign=&id_wk_wab_campaign=' . $id . '&token=' . $this->token;
        $this->context->smarty->assign([
            'token' => $token,
            'name' => $this->l('Edit campaign'),
            'url' => $url,
            'linkType' => 'edit',
        ]);

        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'wkwhatsappbusiness/views/templates/admin/sendLink.tpl'
        );
    }

    public function displaySendLink($token = null, $id, $name = null)
    {
        $url = '';
        $this->context->smarty->assign([
            'token' => $token,
            'name' => $this->l('Send to customer'),
            'url' => $url,
            'linkType' => 'send',
            'idCampaign' => $id,
        ]);

        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'wkwhatsappbusiness/views/templates/admin/sendLink.tpl'
        );
    }

    public function renderForm()
    {
        if (Shop::getContext() == Shop::CONTEXT_GROUP) {
            $this->content = $this->context->smarty->fetch(
                _PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/shop_warning.tpl'
            );

            return;
        }
        if ($this->loadObject(true) && $this->display == 'edit') {
            if ($this->loadObject(true)->id_shop != $this->context->shop->id) {
                $this->content = $this->context->smarty->fetch(
                    _PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/shop_warning.tpl'
                );

                return;
            }
        }
        $headerTypes = [
            ['id' => 0, 'name' => $this->l('Select')],
            ['id' => 'text', 'name' => $this->l('Text')],
            ['id' => 'media', 'name' => $this->l('Media')],
        ];

        $buttonTypes = [
            ['id' => 0, 'name' => $this->l('Select')],
            ['id' => 'call_to_action', 'name' => $this->l('Call to action')],
            ['id' => 'quick_reply', 'name' => $this->l('Quick reply')],
        ];

        $buttonActionType = [
            ['id' => 0, 'name' => $this->l('Select')],
            ['id' => 'call', 'name' => $this->l('Call')],
            ['id' => 'visit_website', 'name' => $this->l('Visit website')],
        ];

        $urlType = [
            ['id' => 0, 'name' => $this->l('Select')],
            ['id' => 'static', 'name' => $this->l('Static')],
            ['id' => 'dynamic', 'name' => $this->l('Dynamic')],
        ];

        $countries = Country::getCountries((int) $this->context->language->id);
        $callPrefix = [];
        foreach ($countries as $country) {
            $callPrefix[] = [
                'id' => $country['call_prefix'],
                'name' => '+' . $country['call_prefix'] . ' (' . $country['name'] . ')',
            ];
        }

        $customers = [];
        $allCustomers = Customer::getCustomers(true);
        foreach ($allCustomers as $key => $customer) {
            $customers[$key]['id'] = $customer['id_customer'];
            if (!isset($customer['firstname'])) {
                $customer['firstname'] = '';
            }
            if (!isset($customer['lastname'])) {
                $customer['lastname'] = '';
            }
            if (!isset($customer['email'])) {
                $customer['email'] = '';
            }
            $customers[$key]['name'] = $customer['firstname'] . ' ' . $customer['lastname'] . ' (' . $customer['email'] . ')';
        }

        if (!$this->loadObject(true)) {
            return;
        }

        if ($this->display == 'edit') {
            $objWABTpl = new WkWhatsAppTemplate();
            $templateInfo = $objWABTpl->getWhatsAppTemplates($this->object->campaign_name);
            $this->context->smarty->assign(
                [
                    'templateInfo' => $templateInfo,
                ]
            );
            $langsForJs = [];
            $langsForJs = WkWABHelper::getInstalledLangIsoCodes();
            Media::addJsDef(
                [
                    'templateInfo' => $templateInfo,
                    'langsForJs' => $langsForJs,
                ]
            );
        }

        if ($this->display == 'add' || $this->display == 'edit') {
            $this->fields_form = [
                'legend' => [
                    'title' => $this->display == 'add' ? $this->l('Add') : $this->l('Edit'),
                    'icon' => $this->display == 'add' ? 'icon-plus' : 'icon-pencil',
                ],
                'warning' => $this->context->smarty->fetch(
                    _PS_MODULE_DIR_ . 'wkwhatsappbusiness/views/templates/admin/createCampaignMsg.tpl'
                ),
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->l('Name'),
                        'name' => 'campaign_name',
                        'required' => true,
                        'desc' => $this->getGeneralHtml('campaignname', null),
                        'hint' => $this->l('Message template is generated with the same name through API.'),
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Header type'),
                        'name' => 'header_type',
                        'options' => [
                            'query' => $headerTypes,
                            'id' => 'id',
                            'name' => 'name',
                        ],
                        'hint' => $this->l('If you want to show header on WhatsApp message.'),
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Header'),
                        'name' => 'campaign_header',
                        'class' => 'wk_campaign_header',
                        'lang' => true,
                        'desc' => $this->getGeneralHtml('header', null),
                        'required' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Header image url'),
                        'name' => 'header_media_url',
                        'required' => true,
                        'desc' => $this->l('Must be less than 255 character.'),
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->l('Description'),
                        'name' => 'campaign_description',
                        'rowspan' => 10,
                        'required' => true,
                        'lang' => true,
                        'desc' => $this->getGeneralHtml('description', null),
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Footer'),
                        'name' => 'campaign_footer',
                        'lang' => true,
                        'desc' => $this->l('Must be less than 60 char including spaces.'),
                    ],
                    [
                        'type' => 'switch',
                        'required' => true,
                        'label' => $this->l('Show buttons'),
                        'name' => 'button_status',
                        'values' => [
                            [
                                'id' => 'type_switch_on',
                                'value' => 1,
                            ],
                            [
                                'id' => 'type_switch_off',
                                'value' => 0,
                            ],
                        ],
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Button type'),
                        'name' => 'button_type',
                        'class' => 'wk-show-button',
                        'required' => true,
                        'options' => [
                            'query' => $buttonTypes,
                            'id' => 'id',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Button action type'),
                        'name' => 'button_action_type',
                        'class' => 'wk-show-button',
                        'required' => true,
                        'options' => [
                            'query' => $buttonActionType,
                            'id' => 'id',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Call button text'),
                        'name' => 'call_button_text',
                        'class' => 'wk-show-button wk-call',
                        'required' => true,
                        'lang' => true,
                        'desc' => $this->l('Must be less than 25 char including spaces.'),
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Country call prefix'),
                        'name' => 'coutry_code',
                        'required' => true,
                        'class' => 'wkchosen fixed-width-sm wk-show-button wk-call',
                        'options' => [
                            'query' => $callPrefix,
                            'id' => 'id',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Phone number'),
                        'name' => 'phone',
                        'required' => true,
                        'class' => 'fixed-width-xxl wk-show-button wk-call',
                        'desc' => $this->l('Must be less than 20 char including spaces.'),
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Visit button text'),
                        'name' => 'visit_button_text',
                        'required' => true,
                        'class' => 'wk-show-button wk-website',
                        'lang' => true,
                        'desc' => $this->l('Must be less than 25 char including spaces.'),
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('URL type'),
                        'name' => 'url_type',
                        'required' => true,
                        'class' => 'wk-show-button wk-website',
                        'options' => [
                            'query' => $urlType,
                            'id' => 'id',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('URL'),
                        'name' => 'url',
                        'required' => true,
                        'class' => 'wk-show-button wk-website',
                        'desc' => $this->getGeneralHtml('url', null),
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('First quick reply button text'),
                        'name' => 'first_quick_reply_text',
                        'required' => true,
                        'class' => 'wk-show-button button_action_type first_quick_reply_text',
                        'desc' => $this->l('Must be less than 25 char including spaces.'),
                        'lang' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Second quick reply button text'),
                        'name' => 'second_quick_reply_text',
                        'required' => true,
                        'class' => 'wk-show-button button_action_type second_quick_reply_text',
                        'desc' => $this->l('Must be less than 25 char including spaces.'),
                        'lang' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Third quick reply button text'),
                        'name' => 'third_quick_reply_text',
                        'required' => true,
                        'class' => 'wk-show-button button_action_type third_quick_reply_text',
                        'desc' => $this->l('Must be less than 25 char including spaces.'),
                        'lang' => true,
                    ],
                    [
                        'type' => 'categories',
                        'label' => $this->l('Select categories'),
                        'name' => 'categories',
                        'required' => true,
                        'tree' => [
                            'root_category' => 2,
                            'id' => 'id_category',
                            'name' => 'name_category',
                            'selected_categories' => $this->display == 'add' ?
                                [json_decode(Tools::getValue('categories'))] :
                                [json_decode($this->object->categories)],
                        ],
                        'desc' => $this->l('Must select a category.'),
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Select customers'),
                        'name' => 'customers',
                        'class' => 'wkchosen',
                        'multiple' => true,
                        'options' => [
                            'query' => $customers,
                            'id' => 'id',
                            'name' => 'name',
                        ],
                        'desc' => $this->l('If no customer is selected this campaign will be sent to all customers.'),
                    ],
                    [
                        'type' => 'datetime',
                        'label' => $this->l('Expiry date and time'),
                        'name' => 'expiry_date',
                        'required' => true,
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Draft'),
                    'name' => 'submitAdd' . $this->table,
                ],
                'buttons' => [
                    'save-and-stay' => [
                        'title' => $this->l('Send to WhatsApp'),
                        'name' => 'submitAdd' . $this->table . 'AndStay',
                        'type' => 'submit',
                        'class' => 'btn btn-default pull-right',
                        'icon' => 'process-icon-save',
                    ],
                ],
            ];

            $customers = Tools::getValue('customers');
            if (!$customers) {
                if (Validate::isLoadedObject($this->object)) {
                    $customers = json_decode($this->object->customers);
                } else {
                    $customers = json_decode($customers);
                }
            }
            $this->fields_value['customers[]'] = $customers;

            $campaignName = Tools::getValue('campaign_name');
            if (!$campaignName) {
                if (Validate::isLoadedObject($this->object)) {
                    $campaignName = $this->getCampaignName($this->object->campaign_name);
                } else {
                    $campaignName = $this->getCampaignName($campaignName);
                }
            }
            $this->fields_value['campaign_name'] = $campaignName;
        }

        return parent::renderForm();
    }

    public function getGeneralHtml($wktype, $wkdata = null)
    {
        $this->context->smarty->assign(
            [
                'wktype' => $wktype,
                'wkdata' => $wkdata,
            ]
        );

        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'wkwhatsappbusiness/views/templates/admin/generalHtml.tpl'
        );
    }

    public function processSave()
    {
        if (Tools::isSubmit('submitAddwk_wab_campaignAndStay')
            || Tools::isSubmit('submitAddwk_wab_campaign')
        ) {
            $idWkWabCampaign = trim(Tools::getValue('id_wk_wab_campaign'));
            $langIds = Language::getIDs(true);
            if (trim(Tools::getValue('campaign_name')) == '') {
                $this->errors[] = $this->l('Please enter campaign name.');
            } elseif (Tools::strlen(trim(Tools::getValue('campaign_name'))) >= 500) {
                $this->errors[] = $this->l('Campaign name must be less than 500 char.');
            } elseif (!Validate::isGenericName(Tools::getValue('campaign_name'))) {
                $this->errors[] = $this->l('Campaign name must be alpha numeric character only.');
            }
            $camapaignName = trim(Tools::getValue('campaign_name'));
            $camapaignName = str_replace(
                ' ',
                '_',
                Tools::strtolower($camapaignName)
            );
            $pattern = '/^[A-Za-z0-9_]+$/';
            if (preg_match($pattern, $camapaignName) == false) {
                $this->errors[] = $this->l('Campaign name must be alpha numeric character only.');
            }
            if ($this->display !== 'edit' && $this->display !== 'list') {
                if (!empty(WkWABCampaign::getCampaignDetailsByName($camapaignName))) {
                    $this->errors[] =
                    $this->l('Campaign with same campaign name already exists, please try another name.');
                }
            }

            if (Tools::getValue('header_type') == 'media'
            && trim(Tools::getValue('header_media_url')) == '') {
                $this->errors[] = $this->l('Please enter header image url.');
            } elseif (!Validate::isAbsoluteUrl(Tools::getValue('header_media_url'))) {
                $this->errors[] = $this->l('Please enter correct header image url.');
            } elseif (Tools::strlen(Tools::getValue('header_media_url')) > 255) {
                $this->errors[] = $this->l('Header image URL length must be less that 255 character.');
            }

            foreach ($langIds as $key => $id) {
                $idLang = Language::getIsoById($id);
                $headerText = trim(Tools::getValue('campaign_header_' . $id));
                if (Tools::getValue('header_type') == 'text') {
                    if ($headerText == '') {
                        $this->errors[] = sprintf(
                            $this->l('Please enter header in %s language.'),
                            $idLang
                        );
                    } elseif (Tools::strlen($headerText) >= 60) {
                        $this->errors[] = sprintf(
                            $this->l('Header text length must be less than 60 characters in %s language.'),
                            $idLang
                        );
                    } elseif (strpos($headerText, '{{1}}') === false) {
                        $this->errors[] = sprintf(
                            $this->l('Header text must contain variable %s only, in %s language.'),
                            '{{1}}',
                            $idLang
                        );
                    }
                }

                $descriptionText = trim(Tools::getValue('campaign_description_' . $id));
                if ($descriptionText == '') {
                    $this->errors[] = sprintf(
                        $this->l('Please enter description in %s language.'),
                        $idLang
                    );
                } elseif (Tools::strlen($descriptionText) >= 1024) {
                    $this->errors[] = sprintf(
                        $this->l('Description text length must be less than 1024 characters in %s language.'),
                        $idLang
                    );
                } elseif ((strpos($descriptionText, '{{1}}') === false)
                || (strpos($descriptionText, '{{2}}') === false)
                || (strpos($descriptionText, '{{3}}') === false)
                || (strpos($descriptionText, '{{4}}') === false)
                ) {
                    $this->errors[] = sprintf(
                        $this->l('Variables are missing from description text in %s language.'),
                        $idLang
                    );
                }

                $footerText = trim(Tools::getValue('campaign_footer_' . $id));
                if ($footerText != '') {
                    if (Tools::strlen($footerText) >= 60) {
                        $this->errors[] = sprintf(
                            $this->l('Footer text length must be less than 60 characters in %s language.'),
                            $idLang
                        );
                    } elseif (!Validate::isCatalogName($footerText)) {
                        $this->errors[] = sprintf(
                            $this->l('Footer text must be alpha numeric only.'),
                            $idLang
                        );
                    }
                }

                if (Tools::getValue('button_status')) {
                    if (Tools::getValue('button_type') == '0') {
                        $this->errors[] = $this->l('Please select button type.');
                    } elseif (Tools::getValue('button_type') == 'call_to_action') {
                        if (Tools::getValue('button_action_type') == '0') {
                            $this->errors[] = $this->l('Please select button action type.');
                        } elseif (Tools::getValue('button_action_type') == 'call') {
                            $callButtonText = trim(Tools::getValue('call_button_text_' . $id));
                            if ($callButtonText == '') {
                                $this->errors[] = sprintf(
                                    $this->l('Please enter call button text in %s language.'),
                                    $idLang
                                );
                            } elseif (Tools::strlen($callButtonText) >= 25) {
                                $this->errors[] = sprintf(
                                    $this->l('Call button text length must be less than 25 characters in %s language.'),
                                    $idLang
                                );
                            } elseif (!Validate::isCatalogName($callButtonText)) {
                                $this->errors[] = sprintf(
                                    $this->l('Call button text must be alpha numeric only.'),
                                    $idLang
                                );
                            }
                        } elseif (Tools::getValue('button_action_type') == 'visit_website') {
                            $visitBtnText = trim(Tools::getValue('visit_button_text_' . $id));
                            if ($visitBtnText == '') {
                                $this->errors[] = sprintf(
                                    $this->l('Please enter visit button text in %s language.'),
                                    $idLang
                                );
                            } elseif (Tools::strlen($visitBtnText) >= 25) {
                                $this->errors[] = sprintf(
                                    $this->l('Visit button text length must be less than 25 characters in %s language.'),
                                    $idLang
                                );
                            } elseif (!Validate::isGenericName($visitBtnText)) {
                                $this->errors[] = sprintf(
                                    $this->l('Visit button text must be alpha numeric only.'),
                                    $idLang
                                );
                            }
                        }
                    } elseif (Tools::getValue('button_type') == 'quick_reply') {
                        $quickReplyOne = trim(Tools::getValue('first_quick_reply_text_' . $id));
                        if ($quickReplyOne == '') {
                            $this->errors[] = sprintf(
                                $this->l('Please enter first quick reply text in %s language.'),
                                $idLang
                            );
                            if (Tools::strlen($quickReplyOne) >= 25) {
                                $this->errors[] = sprintf(
                                    $this->l('First quick reply length must be less than 25 in %s language.'),
                                    $idLang
                                );
                            }
                            if (!Validate::isGenericName($quickReplyOne)) {
                                $this->errors[] = sprintf(
                                    $this->l('First quick reply text must be alpha numeric only.'),
                                    $idLang
                                );
                            }
                        }
                        $quickReplyTwo = trim(Tools::getValue('second_quick_reply_text_' . $id));
                        if ($quickReplyTwo == '') {
                            $this->errors[] = sprintf(
                                $this->l('Please enter second quick reply text in %s language.'),
                                $idLang
                            );
                            if (Tools::strlen($quickReplyTwo) >= 25) {
                                $this->errors[] = sprintf(
                                    $this->l('Second quick reply length must be less than 25 in %s language.'),
                                    $idLang
                                );
                            }
                            if (!Validate::isGenericName($quickReplyTwo)) {
                                $this->errors[] = sprintf(
                                    $this->l('Second quick reply text must be alpha numeric only.'),
                                    $idLang
                                );
                            }
                        }
                        $quickReplyThree = trim(Tools::getValue('third_quick_reply_text_' . $id));
                        if ($quickReplyThree == '') {
                            $this->errors[] = sprintf(
                                $this->l('Please enter third quick reply text in %s language.'),
                                $idLang
                            );
                            if (Tools::strlen($quickReplyThree) >= 25) {
                                $this->errors[] = sprintf(
                                    $this->l('Third quick reply length must be less than 25 in %s language.'),
                                    $idLang
                                );
                            }
                            if (!Validate::isGenericName($quickReplyThree)) {
                                $this->errors[] = sprintf(
                                    $this->l('Third quick reply must be alpha numeric only.'),
                                    $idLang
                                );
                            }
                        }
                    }
                }
            }

            if (Tools::getValue('button_status') == 1) {
                if (Tools::getValue('button_type') == 'call_to_action') {
                    if (Tools::getValue('button_action_type') == 'call') {
                        if (trim(Tools::getValue('coutry_code')) == '') {
                            $this->errors[] = $this->l('Please select country code.');
                        }
                        if (trim(Tools::getValue('phone')) == '') {
                            $this->errors[] = $this->l('Please enter phone number to call.');
                        } elseif (!Validate::isPhoneNumber(Tools::getValue('phone'))) {
                            $this->errors[] = $this->l('Please enter numeric value in phone number.');
                        } elseif (Tools::strlen(trim(Tools::getValue('phone'))) >= 20) {
                            $this->errors[] = $this->l('Phone number must be less than 20 number.');
                        }
                    } elseif (Tools::getValue('button_action_type') == 'visit_website') {
                        $urlType = Tools::getValue('url_type');
                        $url = trim(Tools::getValue('url'));
                        if ($urlType != 'static' && $urlType != 'dynamic') {
                            $this->errors[] = $this->l('Please select url type.');
                        } elseif ($urlType == 'static') {
                            if ($url == '') {
                                $this->errors[] = $this->l('Please enter url.');
                            } elseif (!Validate::isAbsoluteUrl($url)) {
                                $this->errors[] = $this->l('Please enter correct url to visit website.');
                            }
                        } elseif ($urlType == 'dynamic') {
                            if ($url == '') {
                                $this->errors[] = $this->l('Please enter url.');
                            } elseif (strpos($url, '{{1}}') === false) {
                                $this->errors[] = $this->l('Please define at least one variable.');
                            } else {
                                $url = str_replace('{{1}}', '', $url);
                                if (!Validate::isAbsoluteUrl($url)) {
                                    $this->errors[] = $this->l('Please enter correct url to visit website.');
                                }
                            }
                        }
                    }
                }
            }
            if (Tools::getIsset('categories') == false) {
                $this->errors[] = $this->l('Please select one of the category.');
            }
            if (Tools::getValue('expiry_date') == '') {
                $this->errors[] = $this->l('Please enter expiry date and time.');
            } elseif (strtotime(Tools::getValue('expiry_date')) < strtotime(date('Y-m-d h:i:sa'))) {
                $this->errors[] = $this->l('Expiry datetime must be greater than current datetime.');
            }
            if (empty($this->errors)) {
                if (Tools::isSubmit('submitAddwk_wab_campaignAndStay')) {
                    $_POST['campaign_name'] = str_replace(
                        ' ',
                        '_',
                        Tools::strtolower(trim(Tools::getValue('campaign_name')))
                    );
                    $_POST['id_shop'] = (int) $this->context->shop->id;
                    $_POST['status'] = 1;
                    $_POST['customers'] = json_encode(Tools::getValue('customers'));
                    if (!count($this->errors)) {
                        $this->sendTemplateToApi();
                    }
                } elseif (Tools::isSubmit('submitAddwk_wab_campaign')
                && Tools::getValue('submitAddwk_wab_campaign') == 1) {
                    $_POST['campaign_name'] = str_replace(
                        ' ',
                        '_',
                        Tools::strtolower(trim(Tools::getValue('campaign_name')))
                    );
                    $_POST['id_shop'] = (int) $this->context->shop->id;
                    $_POST['status'] = 0;
                    $_POST['customers'] = json_encode(Tools::getValue('customers'));
                }
                parent::processSave();
            } else {
                if ($idWkWabCampaign) {
                    $this->display = 'edit';
                } else {
                    $this->display = 'add';
                }

                return $this->errors;
            }
        }
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        Media::addJsDef(
            [
                'wabToken' => $this->module->secure_key,
                'wabCampaignUrl' => $this->context->link->getAdminLink('AdminWABCampaign'),
                'chosen_no_results_text' => addslashes($this->l('No results matched')),
                'chosen_placeholder_text_single' => addslashes($this->l('Select an option')),
                'chosen_placeholder_text_multiple' => addslashes($this->l('Select options')),
            ]
        );
        $this->addJqueryPlugin('growl', null, false);
        $this->context->controller->addJS(_MODULE_DIR_ . 'wkwhatsappbusiness/views/js/admin/campaign.js');
        $this->context->controller->addCSS(_MODULE_DIR_ . 'wkwhatsappbusiness/views/css/admin/campaign.css');
    }

    public function sendTemplateToApi()
    {
        $templates = $this->getTemplateJson();
        $langIsoCodes = WkWABHelper::getInstalledLangIsoCodes();
        $tplLog = [];
        $i = 0;
        if ($templates && $langIsoCodes) {
            foreach ($templates as $template => $contents) {
                $j = 0;
                $tplLog[$i][$j]['name'] = $template;
                if ($contents) {
                    foreach ($contents as $langCode => $tplArr) {
                        if (in_array($langCode, $langIsoCodes)) {
                            $tplLog[$i][$j]['lang'] = $langCode;
                            $response = $this->createMessageTemplate($tplArr);
                            $tplLog[$i][$j]['response'] = $response;
                            if (isset($response) && $response != null) {
                                if (!$response['success']) {
                                    $this->errors[] = $response['response']['error']['error_user_msg'];
                                }
                            } else {
                                $this->errors[] = $this->l('Application request limit reached.');
                            }
                            ++$j;
                        }
                    }
                }
            }
            ++$i;
        }
        if (empty($this->errors)) {
            $this->success[] = $this->l('Templates are created successfully.');
        } else {
            $this->errors;
        }
    }

    public function createMessageTemplate($tplArr)
    {
        $objTmpl = new WkWhatsAppTemplate();

        return $objTmpl->createWhatsAppTemplate($tplArr);
    }

    public function getTemplateJson()
    {
        $shopUrl = Context::getContext()->link->getBaseLink();
        $langIds = Language::getIDs(true);
        $installedLangs = WkWABHelper::getInstalledLangIsoCodes();
        $finalArray = [];
        $templateName = str_replace(' ', '_', Tools::strtolower(Tools::getValue('campaign_name')));
        foreach ($installedLangs as $id => $isoCode) {
            $templateBody = Tools::getValue('campaign_description_' . $id);
            $templateHeader = Tools::getValue('campaign_header_' . $id);
            $templateFooter = Tools::getValue('campaign_footer_' . $id);
            $components = [];
            if (Tools::getValue('header_type') == 'text') {
                $components[] = [
                    'type' => 'HEADER',
                    'format' => 'TEXT',
                    'text' => $templateHeader,
                    'example' => [
                        'header_text' => [
                            'Offer',
                        ],
                    ],
                ];
            }
            if (Tools::getValue('header_type') == 'media') {
                $campaignHeaderFile = Tools::getValue('header_media_url');
                $components[] = [
                    'type' => 'HEADER',
                    'format' => 'IMAGE',
                    'text' => $templateHeader,
                    'example' => [
                        'header_handle' => [
                            $campaignHeaderFile,
                        ],
                    ],
                ];
            }
            $components[] = [
                'type' => 'BODY',
                'text' => $templateBody,
                'example' => [
                    'body_text' => [
                        ['Customer Name', 'Category Name', $shopUrl, $shopUrl],
                    ],
                ],
            ];
            if ($templateFooter != '') {
                $components[] = [
                    'type' => 'FOOTER',
                    'text' => $templateFooter,
                ];
            }
            if (Tools::getValue('button_status') == 1) {
                if (Tools::getValue('button_type') == 'call_to_action') {
                    if (Tools::getValue('button_action_type') == 'call') {
                        $phoneNumber = Tools::getValue('phone');
                        $coutryCode = Tools::getValue('coutry_code');
                        $callButtonText = Tools::getValue('call_button_text_' . $id);
                        $components[] = [
                            'type' => 'BUTTONS',
                            'buttons' => [
                                [
                                    'type' => 'PHONE_NUMBER',
                                    'text' => $callButtonText,
                                    'phone_number' => '+' . $coutryCode . $phoneNumber,
                                ],
                            ],
                        ];
                    }
                    if (Tools::getValue('button_action_type') == 'visit_website') {
                        $visitBtnText = Tools::getValue('visit_button_text_' . $id);
                        $getUrl = Tools::getValue('url');
                        $urlType = Tools::getValue('url_type');
                        if ($urlType == 'static') {
                            $url = $getUrl;
                        } elseif ($urlType == 'dynamic') {
                            $url = $getUrl;
                        }
                        $components[] = [
                            'type' => 'BUTTONS',
                            'buttons' => [
                                [
                                    'type' => 'URL',
                                    'text' => $visitBtnText,
                                    'url' => $url,
                                    'example' => [$shopUrl],
                                ],
                            ],
                        ];
                    }
                } elseif (Tools::getValue('button_type') == 'quick_reply') {
                    $txtOne = Tools::getValue('first_quick_reply_text_' . $id);
                    $txtTwo = Tools::getValue('second_quick_reply_text_' . $id);
                    $txtThree = Tools::getValue('third_quick_reply_text_' . $id);
                    $components[] = [
                        'type' => 'BUTTONS',
                        'buttons' => [
                            [
                                'type' => 'QUICK_REPLY',
                                'text' => $txtOne,
                            ],
                            [
                                'type' => 'QUICK_REPLY',
                                'text' => $txtTwo,
                            ],
                            [
                                'type' => 'QUICK_REPLY',
                                'text' => $txtThree,
                            ],
                        ],
                    ];
                }
            }
            $finalArray[$id] = $components;
        }
        $templates = [];
        foreach ($installedLangs as $id => $isoCode) {
            $templates['campaign_creation'][$isoCode] = [
                'name' => $templateName,
                'language' => $isoCode,
                'category' => 'MARKETING',
                'components' => $finalArray[$id],
            ];
        }

        return $templates;
    }

    public function sendTemplateMessageToCustomers($idWkWabCampaign)
    {
        $customers = [];
        $objCampaign = new WkWABCampaign($idWkWabCampaign);
        $customers = json_decode($objCampaign->customers);
        $allCustomers = WkWABCustomer::getAllCustomerId();
        if (empty($customers)) {
            $allCustomers = WkWABCustomer::getAllCustomerId();
            foreach ($allCustomers as $customer) {
                $customers[] = $customer['id_customer'];
            }
        }
        foreach ($customers as $idCustomer) {
            $objCustomer = new Customer($idCustomer);
            if ($objCustomer->email != null
                && $objCustomer->firstname != null
                && $objCustomer->active != 0
                && $objCustomer->deleted != 1
            ) {
                $this->sendMessageJson($objCampaign, $idCustomer);
            }
        }
    }

    private function checkIfTemplateExists($template, $idReqLang)
    {
        $objWABTpl = new WkWhatsAppTemplate();
        $installedLangs = WkWABHelper::getInstalledLangIsoCodes();
        $templateInfo = $objWABTpl->getWhatsAppTemplates($template);
        $isApproved = false;
        if ($templateInfo['success'] && $templateInfo['response']['data']) {
            foreach ($installedLangs as $idLang => $lang) {
                foreach ($templateInfo['response']['data'] as $value) {
                    if (($lang == $value['language']) && ($idLang == $idReqLang)) {
                        if (strtoupper($value['status']) == 'APPROVED') {
                            $isApproved = $value['language'];
                            break 2;
                        }
                    }
                }
            }
        }

        return $isApproved;
    }

    public function sendMessageJson($objCampaign, $idCustomer)
    {
        $idLang = Context::getContext()->language->id;
        $customerData = WkWABCustomer::getCustomerData($idCustomer);
        if ($customerData) {
            $customerWANumber = $customerData['mobile'];
            $customerCallPrefix = $customerData['call_prefix'];
            $objCustomer = new Customer($idCustomer);
            $mobileNumber = $customerCallPrefix . $customerWANumber;
            $template = $objCampaign->campaign_name;
            $isoCode = $this->checkIfTemplateExists($template, $idLang);
            $templateData = [];
            $header = [];
            if ($objCampaign->header_type == 'media' || $objCampaign->header_type == 'text') {
                $header['type'] = $objCampaign->header_type;
                $header['media'] = $objCampaign->header_media_url;
                $header['text'] = $objCampaign->campaign_header[$idLang];
                $header['var_1'] = Tools::ucwords(str_replace('_', ' ', $template));
            }
            $body = [];
            $Category = Category::getCategoryInformation([$objCampaign->categories], $idLang);
            foreach ($Category as $key => $cat) {
                $catName = $cat['name'];
                $catId = $cat['id_category'];
                $catLinkRewrite = $cat['link_rewrite'];
                $catLink = Context::getContext()->link->getCategoryLink($catId, $catLinkRewrite);
            }
            $shopUrl = Context::getContext()->link->getBaseLink();
            $body['var_1'] = $objCustomer->firstname . ' ' . $objCustomer->lastname;
            $body['var_2'] = $catName;
            $body['var_3'] = $catLink;
            $body['var_4'] = $shopUrl;
            if (!empty($objCampaign->campaign_footer)) {
                $footer = $objCampaign->campaign_footer[$idLang];
            }
            $button = [];
            if ($objCampaign->button_status == 1) {
                $button['button_status'] = $objCampaign->button_status;
                $button['button_type'] = $objCampaign->button_type;
                if ($objCampaign->button_type == 'quick_reply') {
                    $button['first_quick_reply_text'] = $objCampaign->first_quick_reply_text[$idLang];
                    $button['second_quick_reply_text'] = $objCampaign->second_quick_reply_text[$idLang];
                    $button['third_quick_reply_text'] = $objCampaign->third_quick_reply_text[$idLang];
                }
                if ($objCampaign->button_type == 'call_to_action') {
                    $button['button_action_type'] = $objCampaign->button_action_type;
                    if ($objCampaign->button_action_type == 'call') {
                        $button['call_button_text'] = $objCampaign->call_button_text[$idLang];
                        $button['coutry_code'] = $objCampaign->coutry_code;
                        $button['phone'] = $objCampaign->phone;
                    }
                    if ($objCampaign->button_action_type == 'visit_website') {
                        $button['visit_button_text'] = $objCampaign->visit_button_text[$idLang];
                        $button['url_type'] = $objCampaign->url_type;
                        $button['url'] = $objCampaign->url;
                    }
                }
            }
            $templateData = [
                'header' => $header,
                'body' => $body,
                'footer' => $footer,
                'button' => $button,
                'templateName' => $template,
            ];

            $payload = $this->prepareTemplateData($mobileNumber, $template, $isoCode, $templateData);
            $objSms = new WkWhatsAppMessage();
            $objSms->sendCampaignMessage($payload);
        }
    }

    private function prepareTemplateData($mobileNumber, $template, $isoCode, $templateData)
    {
        $messagePaylaod['messaging_product'] = 'whatsapp';
        $messagePaylaod['to'] = $mobileNumber;
        $messagePaylaod['type'] = 'template';
        $messagePaylaod['template']['name'] = $template;
        $messagePaylaod['template']['language']['code'] = $isoCode;
        if ($templateData) {
            if (!empty($templateData['header'])) {
                $messagePaylaod['template']['components'][] = $this->getTemplateHeaderComponents(
                    $templateData['header']
                );
            }
            if (!empty($templateData['body'])) {
                $messagePaylaod['template']['components'][] = $this->getTemplateBodyComponents(
                    $templateData['body']
                );
            }
            // $messagePaylaod['template']['components'][] = $this->getTemplateFooterComponents(
            //     $templateData['footer']
            // ); no need to send footer because it is defined at the time of template creation
            if (!empty($templateData['button'])) {
                if ($templateData['button']['button_status'] == 1) {
                    if ($templateData['button']['button_type'] == 'quick_reply') {
                        $quickReplies = $this->getTemplateButtonComponents(
                            $templateData['button'],
                            $templateData['templateName']
                        );
                        foreach ($quickReplies as $quickReply) {
                            $messagePaylaod['template']['components'][] = $quickReply;
                        }
                    }
                    if ($templateData['button']['button_type'] == 'call_to_action') {
                        if ($templateData['button']['button_action_type'] == 'visit_website') {
                            $messagePaylaod['template']['components'][] =
                            $this->getTemplateButtonComponents($templateData['button'], $templateData['templateName']);
                        }
                    }
                }
            }
        }
        WkWABHelper::logMsg('--Sending notification--', true);
        WkWABHelper::logMsg('Payload:');
        WkWABHelper::logMsg(json_encode($messagePaylaod));

        return $messagePaylaod;
    }

    private function getTemplateHeaderComponents($templateData)
    {
        $components = [];
        $components['type'] = 'header';
        if ($templateData['type'] == 'text') {
            $components['parameters'] = [
                [
                    'type' => 'text',
                    'text' => $templateData['var_1'],
                ],
            ];
        }
        if ($templateData['type'] == 'media') {
            $components['parameters'] = [
                [
                    'type' => 'image',
                    'image' => [
                        'link' => $templateData['media'],
                    ],
                ],
            ];
        }

        return $components;
    }

    private function getTemplateBodyComponents($templateData)
    {
        $components = [];
        $components['type'] = 'body';
        $components['parameters'] = [
            [
                'type' => 'text',
                'text' => $templateData['var_1'],
            ],
            [
                'type' => 'text',
                'text' => $templateData['var_2'],
            ],
            [
                'type' => 'text',
                'text' => $templateData['var_3'],
            ],
            [
                'type' => 'text',
                'text' => $templateData['var_4'],
            ],
        ];

        return $components;
    }

    private function getTemplateFooterComponents($templateData)
    {
        $components = [];
        $components['type'] = 'footer';
        $components['parameters'] = [
            [
                'type' => 'text',
                'text' => $templateData,
            ],
        ];

        return $components;
    }

    private function getTemplateButtonComponents($templateData, $templateName)
    {
        $campaignDetails = WkWABCampaign::getCampaignDetailsByName($templateName);
        $components = [];
        if ($templateData['button_type'] == 'call_to_action' && $templateData['button_action_type'] == 'call') {
            $components['type'] = 'button';
            $components['sub_type'] = 'call';
        }
        if ($templateData['button_type'] == 'call_to_action'
        && $templateData['button_action_type'] == 'visit_website'
        && $templateData['url_type'] == 'static') {
            $components['type'] = 'button';
            $components['sub_type'] = 'url';
            $components['index'] = 1;
        }
        if ($templateData['button_type'] == 'call_to_action'
        && $templateData['button_action_type'] == 'visit_website'
        && $templateData['url_type'] == 'dynamic') {
            $components['type'] = 'button';
            $components['sub_type'] = 'url';
            $components['index'] = 0;
            $components['parameters'] = [
                [
                    'type' => 'payload',
                    'payload' => '1',
                ],
            ];
        }
        if ($templateData['button_type'] == 'quick_reply') {
            if ($templateData['first_quick_reply_text'] != '') {
                $quickReply1['type'] = 'button';
                $quickReply1['sub_type'] = 'QUICK_REPLY';
                $quickReply1['index'] = 0;
                $quickReply1['parameters'] = [
                    [
                        'type' => 'payload',
                        'payload' => 'view_' . $campaignDetails['categories'],
                    ],
                ];
            }
            if ($templateData['second_quick_reply_text'] != '') {
                $quickReply2['type'] = 'button';
                $quickReply2['sub_type'] = 'QUICK_REPLY';
                $quickReply2['index'] = 1;
                $quickReply2['parameters'] = [
                    [
                        'type' => 'payload',
                        'payload' => 'chat',
                    ],
                ];
            }
            if ($templateData['third_quick_reply_text'] != '') {
                $quickReply3['type'] = 'button';
                $quickReply3['sub_type'] = 'QUICK_REPLY';
                $quickReply3['index'] = 2;
                $quickReply3['parameters'] = [
                    [
                        'type' => 'payload',
                        'payload' => 'site',
                    ],
                ];
            }
            $components = [$quickReply1, $quickReply2, $quickReply3];
        }

        return $components;
    }

    public function ajaxProcessGetSendCampaignToCustomer()
    {
        if (Tools::getValue('wabToken') == $this->module->secure_key) {
            $response = [];
            $idWABCampaign = Tools::getValue('idCampaign');
            $idLang = Context::getContext()->language->id;
            $objCampaign = new WkWABCampaign($idWABCampaign);
            if (strtotime($objCampaign->expiry_date) > strtotime(date('Y-m-d h:i:sa'))) {
                $categoryInfo = Category::getCategoryInformation([$objCampaign->categories]);
                if (empty($categoryInfo)) {
                    $response['status'] = 200;
                    $response['hasError'] = true;
                    $response['message'] = $this->l('Category selected in campaign has been deleted.');
                    $this->ajaxRender(json_encode($response));
                } else {
                    $objWABTpl = new WkWhatsAppTemplate();
                    $templateInfo = $objWABTpl->getWhatsAppTemplates($objCampaign->campaign_name);
                    if ($templateInfo['success'] == false) {
                        $response['status'] = 200;
                        $response['hasError'] = true;
                        $response['message'] = $templateInfo['response']['error']['message'];
                        $this->ajaxRender(json_encode($response));
                    }
                    if ($templateInfo['success'] == true) {
                        if (isset($templateInfo['response'], $templateInfo['response']['data'], $templateInfo['response']['data'][0], $templateInfo['response']['data'][0]['status'])
                        ) {
                            if ($templateInfo['response']['data'][0]['status'] == 'PENDING') {
                                $response['status'] = 200;
                                $response['hasError'] = true;
                                $response['message'] = $this->l('Campaign is still pending on WhatsApp Buisness.');
                                $this->ajaxRender(json_encode($response));
                            } elseif ($templateInfo['response']['data'][0]['status'] == 'APPROVED') {
                                $this->sendTemplateMessageToCustomers($idWABCampaign);
                                $response['status'] = 200;
                                $response['hasError'] = false;
                                $response['message'] = $this->l('WhatsApp message sent succesfully.');
                                $this->ajaxRender(json_encode($response));
                            } elseif ($templateInfo['response']['data'][0]['status'] == 'REJECTED') {
                                $response['status'] = 200;
                                $response['hasError'] = true;
                                $response['message'] = $this->l('Campaign is rejected on WhatsApp Buisness.');
                                $this->ajaxRender(json_encode($response));
                            }
                        } elseif (isset($templateInfo['response'], $templateInfo['response']['data'])
                            && empty($templateInfo['response']['data'])
                        ) {
                            $response['status'] = 200;
                            $response['hasError'] = true;
                            $response['message'] = $this->l('Campaign is not sent on WhatsApp Business.');
                            $this->ajaxRender(json_encode($response));
                        }
                    }
                }
            } else {
                $response['status'] = 200;
                $response['hasError'] = true;
                $response['message'] = $this->l('Campaign is expired.');
                $this->ajaxRender(json_encode($response));
            }
        } else {
            $response['status'] = 200;
            $response['hasError'] = true;
            $response['message'] = $this->l('Please try after some time.');
            $this->ajaxRender(json_encode($response));
        }
    }
}
