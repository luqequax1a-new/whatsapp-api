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
if (!defined('_PS_VERSION_')) {
    exit;
}

include_once 'classes/WkWABInstall.php';
include_once 'classes/WkWABCustomer.php';
include_once 'classes/WkWABCampaign.php';
include_once 'classes/WkWABWebhook.php';
include_once 'classes/WkWABHelper.php';
include_once 'libs/WkWhatsAppMessage.php';
include_once 'libs/WkWhatsAppTemplate.php';

class WkWhatsAppBusiness extends Module
{
    /** @var string */
    private $html = '';
    private $errors = [];
    public $secure_key;

    public function __construct()
    {
        $this->name = 'wkwhatsappbusiness';
        $this->tab = 'front_office_features';
        $this->version = '4.0.1';
        $this->author = 'Webkul';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->secure_key = Tools::hash($this->name);
        $this->ps_versions_compliancy = ['min' => '1.7', 'max' => _PS_VERSION_];
        parent::__construct();
        $this->displayName = $this->l('WhatsApp Business Module');
        $this->description = $this->l('This module helps to send order notifications to the customers');
        $this->confirmUninstall = $this->l('Are you sure?');
    }

    /**
     * Module installation script
     *
     * @return bool
     */
    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }
        $db = new WkWABInstall();
        if (!parent::install()
            || !$db->createTables()
            || !$this->registerPsHooks()
            || !$this->callInstallTab()
            || !$this->defaultConfiguration()
        ) {
            return false;
        }

        return true;
    }

    /**
     * Register module hooks
     *
     * @return bool
     */
    private function registerPsHooks()
    {
        return $this->registerHook(
            [
                'actionValidateOrder',
                'actionOrderStatusPostUpdate',
                'actionAdminOrdersTrackingNumberUpdate',
                'displayCustomerAccount',
                'registerGDPRConsent',
                'actionDeleteGDPRCustomer',
                'actionExportGDPRData',
            ]
        );
    }

    public function hookRegisterGDPRConsent()
    {
    }

    /**
     * Delete customer data of this module if consent deleted
     *
     * @param array $customer Customer array
     *
     * @return string
     */
    public function hookActionDeleteGDPRCustomer($customer)
    {
        if (!empty($customer['id']) && Validate::isUnsignedInt($customer['id'])) {
            $idCustomer = (int) $customer['id'];
            $customerData = WkWABCustomer::getCustomerData($idCustomer);
            if ($customerData) {
                $objCustomer = new WkWABCustomer((int) $customerData['id_wk_wab_customer']);
                if ($objCustomer->delete()) {
                    return json_encode(true);
                }
            }

            return json_encode($this->l('WhatsApp Businnes Module: Unable to delete customer using customer ID.'));
        }
    }

    /**
     * Export module customer data through GDPR module
     *
     * @param array $customer Customer array
     *
     * @return string Customer data
     */
    public function hookActionExportGDPRData($customer)
    {
        if (!Tools::isEmpty($customer['id']) && Validate::isUnsignedInt($customer['id'])) {
            $idCustomer = (int) $customer['id'];
            $customerData = WkWABCustomer::getCustomerData($idCustomer);
            if ($customerData) {
                return json_encode($customerData);
            }

            return json_encode($this->l('WhatsApp Businnes Module: Unable to export customer using customer ID.'));
        }
    }

    /**
     * Display module link in the customer account section
     *
     * @return string
     */
    public function hookDisplayCustomerAccount()
    {
        if (WkWABHelper::isModuleConfigured()) {
            $idCustomer = $this->context->customer->id;
            if ($idCustomer) {
                $this->context->smarty->assign([
                    'wabCustomerLink' => $this->context->link->getModuleLink(
                        'wkwhatsappbusiness',
                        'wabnotification'
                    ),
                ]);

                return $this->display(__FILE__, 'wab_account.tpl');
            }
        }
    }

    /**
     * Load the configuration page
     */
    public function getContent()
    {
        $moduleAdminLink = Context::getContext()->link->getAdminLink(
            'AdminModules',
            true,
            false,
            ['configure' => $this->name]
        );
        Media::addJsDef([
            'wkModuleAddonKey' => $this->module_key,
            'wkModuleAddonsId' => 0,
            'wkModuleTechName' => $this->name,
            'wkModuleDoc' => file_exists(_PS_MODULE_DIR_ . $this->name . '/doc_en.pdf'),
        ]);
        $this->context->controller->addJs('https://prestashop.webkul.com/crossselling/wkcrossselling.min.js?t=' . time());

        if (Tools::isSubmit('submitApi')
            || Tools::isSubmit('submitNotification')
            || Tools::isSubmit('submitTemplate')
            || Tools::isSubmit('submitNewToken')
        ) {
            $this->postValidation();
            if (empty($this->errors)) {
                $this->postProcess();
            } else {
                foreach ($this->errors as $error) {
                    $this->html .= $this->displayError($error);
                }
            }
        } else {
            if (WkWABHelper::isModuleConfigured()) {
                $phoneNumber = Configuration::get('WK_WAB_PHONE_NUMBER_ID');
                $accountID = Configuration::get('WK_WAB_ACCOUNT_ID');
                $token = Configuration::get('WK_WAB_TOKEN');
                if ($phoneNumber && $accountID && $token) {
                    $objHelper = new WkWABHelper();
                    $response = $objHelper->validateAPICredentials(
                        $phoneNumber,
                        $accountID,
                        $token
                    );
                    if (is_array($response) && count($response)) {
                        $this->html .= $this->displayError($response);
                    }
                }
            }
        }

        Media::addJsDef([
            'module_token' => $this->secure_key,
            'wkWabOrderStaus' => (int) Configuration::get('WK_WAB_SEND_ORDER_UPDATE'),
        ]);

        $this->context->controller->addCSS([
            $this->_path . 'views/css/admin/menu.css',
        ]);
        $this->context->controller->addJS([
            $this->_path . 'views/js/admin/vue.min.js',
            $this->_path . 'views/js/admin/menu.js',
            $this->_path . 'views/js/admin/wabconfig.js',
        ]);
        // get current page
        $currentPage = 'wkapi';
        $page = Tools::getValue('page');
        if (!empty($page)) {
            $currentPage = Tools::getValue('page');
        }

        $this->context->smarty->assign([
            'module_version' => $this->version,
            'ps_base_dir' => Tools::getHttpHost(true),
            'currentPage' => $currentPage,
            'module_dir' => $this->_path,
            'ps_module_dir' => _PS_MODULE_DIR_,
            'moduleAdminLink' => $moduleAdminLink,
            'module_name' => $this->name,
            'apiSettingForm' => $this->renderApiSettingForm(),
            'templateSettingBlock' => $this->renderTemplateBlock(),
            'notificationSettingForm' => $this->renderNotificationSettingForm(),
            'webhookSettingForm' => $this->renderWebhookSettingForm(),
        ]);

        $this->html .= $this->context->smarty->fetch($this->local_path . 'views/templates/admin/menu.tpl');

        return $this->html;
    }

    /**
     * Validate module configuration form
     *
     * @return array|void
     */
    public function postValidation()
    {
        if (Tools::isSubmit('submitApi')) {
            $phoneNumber = trim(Tools::getValue('WK_WAB_PHONE_NUMBER_ID'));
            if (!$phoneNumber) {
                $this->errors[] = $this->l('WhatsApp phone number ID is required.');
            } elseif ($phoneNumber && !is_numeric($phoneNumber)) {
                $this->errors[] = $this->l('Invalid WhatsApp phone number ID.');
            }

            $accountID = trim(Tools::getValue('WK_WAB_ACCOUNT_ID'));
            if (!$accountID) {
                $this->errors[] = $this->l('WhatsApp account ID is required.');
            } elseif ($accountID && !is_numeric($accountID)) {
                $this->errors[] = $this->l('Invalid WhatsApp account ID.');
            }

            $token = trim(Tools::getValue('WK_WAB_TOKEN'));
            if (!$token) {
                $this->errors[] = $this->l('WhatsApp permanent token is required.');
            }
            if ($phoneNumber && $accountID && $token) {
                $objHelper = new WkWABHelper();
                $response = $objHelper->validateAPICredentials(
                    $phoneNumber,
                    $accountID,
                    $token
                );
                if (is_array($response) && count($response)) {
                    $this->errors[] = $response;
                }
            }
        } elseif (Tools::isSubmit('submitTemplate')) {
            $this->validateTemplateString();
            if (empty($this->errors)) {
                $templates = [];
                $tplArr = [
                    'create_order_conf' => WkWABHelper::WAB_ORDER_CONF_TPL_NAME,
                    'update_order' => WkWABHelper::WAB_ORDER_UPD_TPL_NAME,
                    'track_order' => WkWABHelper::WAB_ORDER_TRACK_TPL_NAME,
                    'verify_otp' => WkWABHelper::WAB_VERIFY_OTP_TPL_NAME,
                ];
                $objHelper = new WkWABHelper();
                foreach ($tplArr as $key => $template) {
                    $templates[$key] = $objHelper->prepareTemplateData($template, $key);
                }
                $tplLog = [];
                if ($templates) {
                    foreach ($templates as $template => $contents) {
                        if ($contents) {
                            foreach ($contents as $langCode => $tplArr) {
                                $response = $this->createMessageTemplate($tplArr);
                                $tplLog[] = $response;
                                if (!$response['success']) {
                                    if (isset($response['response']['error']['error_user_msg'])) {
                                        $this->errors[] = $template . ' - ' . $response['response']['error']['error_user_msg'];
                                    } else {
                                        $this->errors[] = $template . ' - ' . $response['response']['error']['message'];
                                    }
                                }
                            }
                        }
                    }
                }
                WkWABHelper::logMsg(json_encode($tplLog), true);
                if (empty($this->errors)) {
                    $this->html .= $this->displayConfirmation($this->l('Templates have been created successfully.'));
                }
            }
        }
    }

    /**
     * Validate order message templates
     *
     * @return void|array
     */
    private function validateTemplateString()
    {
        $tplArr = [
            'create_order_conf' => WkWABHelper::WAB_ORDER_CONF_TPL_NAME,
            'update_order' => WkWABHelper::WAB_ORDER_UPD_TPL_NAME,
            'track_order' => WkWABHelper::WAB_ORDER_TRACK_TPL_NAME,
            'verify_otp' => WkWABHelper::WAB_VERIFY_OTP_TPL_NAME,
        ];
        $objHelper = new WkWABHelper();
        foreach ($tplArr as $key => $template) {
            if ($errors = $objHelper->validateOrderTemplateString($template, $key)) {
                $this->errors[] = $errors;
            }
        }
    }

    /**
     * Render API configuration form
     *
     * @return string HTML form content
     */
    protected function renderApiSettingForm()
    {
        $fieldsForm['form'] = [
            'legend' => [
                'title' => $this->l('API details'),
                'icon' => 'icon-cogs',
            ],
            'description' => $this->context->smarty->fetch(
                _PS_MODULE_DIR_ . $this->name . '/views/templates/admin/getApiInfo.tpl'
            ),
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->l('Phone number ID'),
                    'name' => 'WK_WAB_PHONE_NUMBER_ID',
                    'required' => true,
                    'col' => 4,
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Business account ID'),
                    'name' => 'WK_WAB_ACCOUNT_ID',
                    'required' => true,
                    'col' => 4,
                ],
                [
                    'type' => 'textarea',
                    'label' => $this->l('Permanent token'),
                    'name' => 'WK_WAB_TOKEN',
                    'required' => true,
                    'col' => 6,
                ],
            ],
            'submit' => [
                'name' => 'submitApiSettings',
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right',
            ],
        ];

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitApi';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name . '&page=wkapi';
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'fields_value' => $this->getConfigApiFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm([$fieldsForm]);
    }

    /**
     * Render order template form
     *
     * @return string HTML form
     */
    protected function renderTemplateBlock()
    {
        $this->context->smarty->assign(
            [
                'self' => dirname(__FILE__),
                'languages' => $this->context->controller->getLanguages(),
            ]
        );

        $this->assignDefaultTemplateMessage();

        $fieldsForm['form'] = [
            'legend' => [
                'title' => $this->l('Templates'),
                'icon' => 'icon-file',
            ],
            'warning' => $this->context->smarty->fetch(
                _PS_MODULE_DIR_ . 'wkwhatsappbusiness/views/templates/admin/createTemplateMsg.tpl'
            ),
            'input' => [
                [
                    'type' => 'html',
                    'name' => 'order_message_templates',
                    'html_content' => $this->context->smarty->fetch(
                        _PS_MODULE_DIR_ . 'wkwhatsappbusiness/views/templates/admin/createTemplateForm.tpl'
                    ),
                ],
            ],
            'submit' => [
                'name' => 'submitTemplateSettings',
                'title' => $this->l('Create templates'),
                'class' => 'btn btn-default pull-right',
            ],
        ];

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitTemplate';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name . '&page=wktemplate';
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'fields_value' => [],
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm([$fieldsForm]);
    }

    /**
     * Get order template messages from API for available languages
     * If template not available for any specific language, then return
     * default template message
     *
     * @return array
     */
    protected function assignDefaultTemplateMessage()
    {
        $messages = [];
        $disableTplBtn = false;
        $availOrderConfTplLangs = [];
        $availOrderUpdTplLangs = [];
        $availOrderTrackTplLangs = [];
        $availOtpTplLangs = [];
        $objWABTpl = new WkWhatsAppTemplate();
        $installedLangs = WkWABHelper::getInstalledLangIsoCodes();
        $orderConfTpl = [];
        $orderUpdTpl = [];
        $orderTrackTpl = [];
        $otpTpl = [];
        if (WkWABHelper::isModuleConfigured()) {
            $orderConfTpl = $objWABTpl->getWhatsAppTemplates(WkWABHelper::WAB_ORDER_CONF_TPL_NAME);
            $orderUpdTpl = $objWABTpl->getWhatsAppTemplates(WkWABHelper::WAB_ORDER_UPD_TPL_NAME);
            $orderTrackTpl = $objWABTpl->getWhatsAppTemplates(WkWABHelper::WAB_ORDER_TRACK_TPL_NAME);
            $otpTpl = $objWABTpl->getWhatsAppTemplates(WkWABHelper::WAB_VERIFY_OTP_TPL_NAME);
        }

        if (($orderConfTpl && $orderConfTpl['success'] && $orderConfTpl['response']['data'])
            && ($orderUpdTpl && $orderUpdTpl['success'] && $orderUpdTpl['response']['data'])
            && ($orderTrackTpl && $orderTrackTpl['success'] && $orderTrackTpl['response']['data'])
            && ($otpTpl && $otpTpl['success'] && $otpTpl['response']['data'])
        ) {
            if ($installedLangs) {
                $disableTplBtn = 1;
                foreach ($installedLangs as $idLang => $lang) {
                    foreach ($orderConfTpl['response']['data'] as $value) {
                        if ($lang == $value['language']) {
                            $availOrderConfTplLangs[] = $idLang;
                            $messages['order_conf']['status'][$idLang] = [
                                'id' => $value['id'],
                                'status' => $value['status'],
                                'lang' => $lang,
                            ];
                            $components = $value['components'];
                            foreach ($components as $comp) {
                                if (strtoupper($comp['type']) == 'HEADER') {
                                    $messages['order_conf']['header'][$idLang] = $comp['text'];
                                } elseif (strtoupper($comp['type']) == 'BODY') {
                                    $messages['order_conf']['body'][$idLang] = $comp['text'];
                                } elseif (strtoupper($comp['type']) == 'FOOTER') {
                                    $messages['order_conf']['footer'][$idLang] = $comp['text'];
                                } elseif (strtoupper($comp['type']) == 'BUTTONS') {
                                    $messages['order_conf']['btn_text'][$idLang] = $comp['buttons']['0']['text'];
                                }
                            }
                        }
                    }
                }
                foreach ($installedLangs as $idLang => $lang) {
                    foreach ($orderUpdTpl['response']['data'] as $value) {
                        if ($lang == $value['language']) {
                            $availOrderUpdTplLangs[] = $idLang;
                            $messages['order_update']['status'][$idLang] = [
                                'id' => $value['id'],
                                'status' => $value['status'],
                                'lang' => $lang,
                            ];
                            $components = $value['components'];
                            foreach ($components as $comp) {
                                if (strtoupper($comp['type']) == 'HEADER') {
                                    $messages['order_update']['header'][$idLang] = $comp['text'];
                                } elseif (strtoupper($comp['type']) == 'BODY') {
                                    $messages['order_update']['body'][$idLang] = $comp['text'];
                                } elseif (strtoupper($comp['type']) == 'FOOTER') {
                                    $messages['order_update']['footer'][$idLang] = $comp['text'];
                                } elseif (strtoupper($comp['type']) == 'BUTTONS') {
                                    $messages['order_update']['btn_text'][$idLang] = $comp['buttons']['0']['text'];
                                }
                            }
                        }
                    }
                }
                foreach ($installedLangs as $idLang => $lang) {
                    foreach ($orderTrackTpl['response']['data'] as $value) {
                        if ($lang == $value['language']) {
                            $availOrderTrackTplLangs[] = $idLang;
                            $messages['order_track']['status'][$idLang] = [
                                'id' => $value['id'],
                                'status' => $value['status'],
                                'lang' => $lang,
                            ];
                            $components = $value['components'];
                            foreach ($components as $comp) {
                                if (strtoupper($comp['type']) == 'HEADER') {
                                    $messages['order_track']['header'][$idLang] = $comp['text'];
                                } elseif (strtoupper($comp['type']) == 'BODY') {
                                    $messages['order_track']['body'][$idLang] = $comp['text'];
                                } elseif (strtoupper($comp['type']) == 'FOOTER') {
                                    $messages['order_track']['footer'][$idLang] = $comp['text'];
                                } elseif (strtoupper($comp['type']) == 'BUTTONS') {
                                    $messages['order_track']['btn_text'][$idLang] = $comp['buttons']['0']['text'];
                                }
                            }
                        }
                    }
                }

                foreach ($installedLangs as $idLang => $lang) {
                    foreach ($otpTpl['response']['data'] as $value) {
                        if ($lang == $value['language']) {
                            $availOtpTplLangs[] = $idLang;
                            $messages['verify_otp']['status'][$idLang] = [
                                'id' => $value['id'],
                                'status' => $value['status'],
                                'lang' => $lang,
                            ];
                            $components = $value['components'];
                            foreach ($components as $comp) {
                                if (strtoupper($comp['type']) == 'BUTTONS') {
                                    $messages['verify_otp']['btn_text'][$idLang] = $comp['buttons']['0']['text'];
                                }
                            }
                        }
                    }
                }
                $notAvailOrderConfTplLang = array_diff(
                    array_values(array_flip($installedLangs)),
                    $availOrderConfTplLangs
                );
                $notAvailOrderUpdTplLang = array_diff(
                    array_values(array_flip($installedLangs)),
                    $availOrderUpdTplLangs
                );
                $notAvailOrderTrackTplLang = array_diff(
                    array_values(array_flip($installedLangs)),
                    $availOrderTrackTplLangs
                );
                $notAvailOtpTplLang = array_diff(
                    array_values(array_flip($installedLangs)),
                    $availOtpTplLangs
                );
                if ($notAvailOrderConfTplLang) {
                    $disableTplBtn = 0;
                    foreach ($notAvailOrderConfTplLang as $idLang) {
                        $templateString = $this->getDefaultOrderMessages($idLang);
                        foreach ($messages['order_conf'] as $k => &$msg) {
                            if ($k == 'header') {
                                $msg[$idLang] = $templateString['order_conf']['header'][$idLang];
                            }
                            if ($k == 'footer') {
                                $msg[$idLang] = $templateString['order_conf']['footer'][$idLang];
                            }
                            if ($k == 'btn_text') {
                                $msg[$idLang] = $templateString['order_conf']['btn_text'][$idLang];
                            }
                            if ($k == 'body') {
                                $msg[$idLang] = $templateString['order_conf']['body'][$idLang];
                            }
                        }
                    }
                }
                if ($notAvailOrderUpdTplLang) {
                    $disableTplBtn = 0;
                    foreach ($notAvailOrderUpdTplLang as $idLang) {
                        $templateString = $this->getDefaultOrderMessages($idLang);
                        foreach ($messages['order_update'] as $k => &$msg) {
                            if ($k == 'header') {
                                $msg[$idLang] = $templateString['order_update']['header'][$idLang];
                            }
                            if ($k == 'footer') {
                                $msg[$idLang] = $templateString['order_update']['footer'][$idLang];
                            }
                            if ($k == 'btn_text') {
                                $msg[$idLang] = $templateString['order_update']['btn_text'][$idLang];
                            }
                            if ($k == 'body') {
                                $msg[$idLang] = $templateString['order_update']['body'][$idLang];
                            }
                        }
                    }
                }
                if ($notAvailOrderTrackTplLang) {
                    $disableTplBtn = 0;
                    foreach ($notAvailOrderTrackTplLang as $idLang) {
                        $templateString = $this->getDefaultOrderMessages($idLang);
                        foreach ($messages['order_track'] as $k => &$msg) {
                            if ($k == 'header') {
                                $msg[$idLang] = $templateString['order_track']['header'][$idLang];
                            }
                            if ($k == 'footer') {
                                $msg[$idLang] = $templateString['order_track']['footer'][$idLang];
                            }
                            if ($k == 'btn_text') {
                                $msg[$idLang] = $templateString['order_track']['btn_text'][$idLang];
                            }
                            if ($k == 'body') {
                                $msg[$idLang] = $templateString['order_track']['body'][$idLang];
                            }
                        }
                    }
                }

                if ($notAvailOtpTplLang) {
                    $disableTplBtn = 0;
                    foreach ($notAvailOtpTplLang as $idLang) {
                        $templateString = $this->getDefaultOrderMessages($idLang);
                        foreach ($messages['verify_otp'] as $k => &$msg) {
                            if ($k == 'btn_text') {
                                $msg[$idLang] = $templateString['verify_otp']['btn_text'][$idLang];
                            }
                        }
                    }
                }
            }
        } else {
            $messages = $this->getDefaultOrderMessages();
        }
        $this->context->smarty->assign(
            [
                'messages' => $messages,
            ]
        );
        Media::addJsDef([
            'isWabTplExists' => $disableTplBtn,
        ]);
    }

    /**
     * Get default order template messages through language
     *
     * @param int $idLang Language ID
     *
     * @return array
     */
    protected function getDefaultOrderMessages($idLang = null)
    {
        $messages = [];
        $orderAllFooterTxt = $this->l('Thanks for shopping with us.');
        $orderAllViewBtnTxt = $this->l('View order');
        $orderAllHelloTxt = $this->l('Hello {{1}},') . "\n\n";
        $orderConfHeaderTxt = $this->l('Order confirmation!');
        $orderConfBodyTxt = $orderAllHelloTxt .
        $this->l('Your order is successfully created with reference no *{{2}}* and total amount *{{3}}*.');
        $orderConfBodyTxt .= "\n\n" . $this->l('Hope to serve you again.');
        $orderUpdHeaderTxt = $this->l('Order status updated!');
        $orderUpdBodyTxt = $orderAllHelloTxt .
        $this->l('Your order status has been updated to *{{2}}* for your order reference *{{3}}*.');
        $orderUpdBodyTxt .= "\n\n" . $this->l('Hope to serve you again.');
        $orderTrackHeaderTxt = $this->l('Track order!');
        $orderTrackBodyTxt = $orderAllHelloTxt .
        $this->l('Your tracking number is *{{2}}* for your order reference *{{3}}*.');
        $orderTrackBodyTxt .= "\n\n" . $this->l('Hope to serve you again.');
        $otpCopyText = $this->l('Copy code');

        if ($idLang == null) {
            foreach (Language::getLanguages() as $lang) {
                // Order create
                $messages['order_conf']['header'][$lang['id_lang']] = $orderConfHeaderTxt;
                $messages['order_conf']['footer'][$lang['id_lang']] = $orderAllFooterTxt;
                $messages['order_conf']['btn_text'][$lang['id_lang']] = $orderAllViewBtnTxt;
                $messages['order_conf']['body'][$lang['id_lang']] = $orderConfBodyTxt;

                // Order status update
                $messages['order_update']['header'][$lang['id_lang']] = $orderUpdHeaderTxt;
                $messages['order_update']['footer'][$lang['id_lang']] = $orderAllFooterTxt;
                $messages['order_update']['btn_text'][$lang['id_lang']] = $orderAllViewBtnTxt;
                $messages['order_update']['body'][$lang['id_lang']] = $orderUpdBodyTxt;

                // Order tracking
                $messages['order_track']['header'][$lang['id_lang']] = $orderTrackHeaderTxt;
                $messages['order_track']['footer'][$lang['id_lang']] = $orderAllFooterTxt;
                $messages['order_track']['btn_text'][$lang['id_lang']] = $orderAllViewBtnTxt;
                $messages['order_track']['body'][$lang['id_lang']] = $orderTrackBodyTxt;

                // One time password
                $messages['verify_otp']['btn_text'][$lang['id_lang']] = $otpCopyText;
            }
        } else {
            // Order create
            $messages['order_conf']['header'][$idLang] = $orderConfHeaderTxt;
            $messages['order_conf']['footer'][$idLang] = $orderAllFooterTxt;
            $messages['order_conf']['btn_text'][$idLang] = $orderAllViewBtnTxt;
            $messages['order_conf']['body'][$idLang] = $orderConfBodyTxt;

            // Order status update
            $messages['order_update']['header'][$idLang] = $orderUpdHeaderTxt;
            $messages['order_update']['footer'][$idLang] = $orderAllFooterTxt;
            $messages['order_update']['btn_text'][$idLang] = $orderAllViewBtnTxt;
            $messages['order_update']['body'][$idLang] = $orderUpdBodyTxt;

            // Order tracking
            $messages['order_track']['header'][$idLang] = $orderTrackHeaderTxt;
            $messages['order_track']['footer'][$idLang] = $orderAllFooterTxt;
            $messages['order_track']['btn_text'][$idLang] = $orderAllViewBtnTxt;
            $messages['order_track']['body'][$idLang] = $orderTrackBodyTxt;

            // One time password
            $messages['verify_otp']['copy'][$idLang] = $otpCopyText;
        }

        return $messages;
    }

    protected function getConfigApiFormValues()
    {
        $keys = [
            'WK_WAB_PHONE_NUMBER_ID',
            'WK_WAB_ACCOUNT_ID',
            'WK_WAB_TOKEN',
        ];
        $formValues = [];
        foreach ($keys as $key) {
            $formValues[$key] = Configuration::get($key);
        }

        return $formValues;
    }

    /**
     * Render notification configuration form
     *
     * @return string HTML form content
     */
    protected function renderNotificationSettingForm()
    {
        $orderStates = OrderState::getOrderStates($this->context->language->id);
        $fieldsForm['form'] = [
            'legend' => [
                'title' => $this->l('Notifications'),
                'icon' => 'icon-whatsapp',
            ],
            'input' => [
                [
                    'type' => 'switch',
                    'name' => 'WK_WAB_SEND_ORDER_CREATE',
                    'label' => $this->l('Send notification on order create'),
                    'desc' => $this->l('If yes, customer will receive notification when order is created.'),
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'WK_WAB_SEND_ORDER_CREATE_on',
                            'value' => true,
                            'label' => $this->l('Yes'),
                        ],
                        [
                            'id' => 'WK_WAB_SEND_ORDER_CREATE_off',
                            'value' => false,
                            'label' => $this->l('No'),
                        ],
                    ],
                ],
                [
                    'type' => 'switch',
                    'name' => 'WK_WAB_SEND_ORDER_TRACK',
                    'label' => $this->l('Send order track notification'),
                    'desc' => $this->l('If yes, customer will receive notification when tracking number added.'),
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'WK_WAB_SEND_ORDER_TRACK_on',
                            'value' => true,
                            'label' => $this->l('Yes'),
                        ],
                        [
                            'id' => 'WK_WAB_SEND_ORDER_TRACK_off',
                            'value' => false,
                            'label' => $this->l('No'),
                        ],
                    ],
                ],
                [
                    'type' => 'switch',
                    'name' => 'WK_WAB_SEND_ORDER_UPDATE',
                    'label' => $this->l('Send notification on order status update'),
                    'desc' => $this->l('If yes, customer will receive notification when order status is updated.'),
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'WK_WAB_SEND_ORDER_UPDATE_on',
                            'value' => true,
                            'label' => $this->l('Yes'),
                        ],
                        [
                            'id' => 'WK_WAB_SEND_ORDER_UPDATE_off',
                            'value' => false,
                            'label' => $this->l('No'),
                        ],
                    ],
                ],
                [
                    'type' => 'select',
                    'name' => 'WK_WAB_SEND_ORDER_STATUS',
                    'label' => $this->l('Order status'),
                    'class' => 'chosen',
                    'multiple' => true,
                    'desc' => $this->l('Select order statuses on which you want to send notifications.') . ' ' .
                    $this->l('If no status selected, notification will be sent for all statuses.'),
                    'options' => [
                        'id' => 'id_order_state',
                        'name' => 'name',
                        'query' => $orderStates,
                    ],
                ],
            ],
            'submit' => [
                'name' => 'submitNotificationSettings',
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right',
            ],
        ];

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitNotification';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name . '&page=wknotification';
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'fields_value' => $this->getConfigNotificationFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm([$fieldsForm]);
    }

    /**
     * Get notification configuration values
     *
     * @return array
     */
    protected function getConfigNotificationFormValues()
    {
        $keys = [
            'WK_WAB_SEND_ORDER_CREATE',
            'WK_WAB_SEND_ORDER_TRACK',
            'WK_WAB_SEND_ORDER_UPDATE',
            'WK_WAB_SEND_ORDER_STATUS',
        ];
        $formValues = [];
        foreach ($keys as $key) {
            if ($key == 'WK_WAB_SEND_ORDER_STATUS') {
                $formValues['WK_WAB_SEND_ORDER_STATUS[]'] = json_decode(
                    Configuration::get($key),
                    true
                );
            } else {
                $formValues[$key] = Configuration::get($key);
            }
        }

        return $formValues;
    }

    /**
     * Render webhook form
     *
     * @return string HTML form content
     */
    protected function renderWebhookSettingForm()
    {
        $this->context->smarty->assign([
            'webhookVerified' => Configuration::get('WAB_WEBHOOK_TOKEN_VERIFIED'),
        ]);
        $fieldsForm['form'] = [
            'legend' => [
                'title' => $this->l('Webhook'),
                'icon' => 'icon-retweet',
            ],
            'description' => $this->context->smarty->fetch(
                _PS_MODULE_DIR_ . 'wkwhatsappbusiness/views/templates/admin/webhooklink.tpl'
            ),
            'error' => $this->context->smarty->fetch(
                _PS_MODULE_DIR_ . 'wkwhatsappbusiness/views/templates/admin/webhookverified.tpl'
            ),
            'input' => [
                [
                    'type' => 'text',
                    'name' => 'WAB_WEBHOOK_URL',
                    'label' => $this->l('Callback URL'),
                    'desc' => $this->l('Fill this url in WhatsApp webhook configuration in Callback URL.'),
                    'readonly' => true,
                ],
                [
                    'type' => 'text',
                    'name' => 'WAB_WEBHOOK_TOKEN',
                    'label' => $this->l('Verify token'),
                    'desc' => $this->l('Fill this token in WhatsApp webhook configuration in verify token.'),
                    'readonly' => true,
                ],
            ],
            'submit' => [
                'name' => 'submitGenerateNewToken',
                'title' => $this->l('Regenerate token'),
                'class' => 'btn btn-default pull-right',
            ],
        ];
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitNewToken';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name . '&page=wkwebhook';
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'fields_value' => $this->getWebhookFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm([$fieldsForm]);
    }

    /**
     * Get webhook values
     *
     * @return array
     */
    protected function getWebhookFormValues()
    {
        $keys = [
            'WAB_WEBHOOK_URL',
            'WAB_WEBHOOK_TOKEN',
        ];
        $formValues = [];
        foreach ($keys as $key) {
            if ($key == 'WAB_WEBHOOK_URL') {
                $pageURL = (@$_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://';
                $pageURL .= $_SERVER['SERVER_NAME'] . _MODULE_DIR_ . 'wkwhatsappbusiness/wkWhatsAppListener.php';
                $formValues['WAB_WEBHOOK_URL'] = $pageURL;
            } else {
                $formValues['WAB_WEBHOOK_TOKEN'] = Configuration::get('WAB_WEBHOOK_TOKEN');
            }
        }

        return $formValues;
    }

    /**
     * Save configuration form data
     *
     * @return bool|array
     */
    protected function postProcess()
    {
        if (Tools::isSubmit('submitNewToken')) {
            $token = sprintf(
                '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xFFFF),
                mt_rand(0, 0xFFFF),
                mt_rand(0, 0xFFFF),
                mt_rand(0, 0x0FFF) | 0x4000,
                mt_rand(0, 0x3FFF) | 0x8000,
                mt_rand(0, 0xFFFF),
                mt_rand(0, 0xFFFF),
                mt_rand(0, 0xFFFF)
            );
            Configuration::updateValue('WAB_WEBHOOK_TOKEN', $token);
            Configuration::updateValue('WAB_WEBHOOK_TOKEN_VERIFIED', 0);
            $this->html .= $this->displayConfirmation($this->l('New token generated successfully.'));
        } elseif (Tools::isSubmit('submitApi')) {
            Configuration::updateGlobalValue(
                'WK_WAB_PHONE_NUMBER_ID',
                Tools::getValue('WK_WAB_PHONE_NUMBER_ID')
            );
            Configuration::updateGlobalValue(
                'WK_WAB_ACCOUNT_ID',
                Tools::getValue('WK_WAB_ACCOUNT_ID')
            );
            Configuration::updateGlobalValue(
                'WK_WAB_TOKEN',
                Tools::getValue('WK_WAB_TOKEN')
            );
            $this->html .= $this->displayConfirmation($this->l('API details updated successfully.'));
        } elseif (Tools::isSubmit('submitNotification')) {
            Configuration::updateValue(
                'WK_WAB_SEND_ORDER_CREATE',
                Tools::getValue('WK_WAB_SEND_ORDER_CREATE')
            );
            Configuration::updateValue(
                'WK_WAB_SEND_ORDER_UPDATE',
                Tools::getValue('WK_WAB_SEND_ORDER_UPDATE')
            );
            Configuration::updateValue(
                'WK_WAB_SEND_ORDER_TRACK',
                Tools::getValue('WK_WAB_SEND_ORDER_TRACK')
            );
            Configuration::updateValue(
                'WK_WAB_SEND_ORDER_STATUS',
                json_encode(Tools::getValue('WK_WAB_SEND_ORDER_STATUS'), true)
            );
            $this->html .= $this->displayConfirmation($this->l('Notification setting updated successfully.'));
        }
    }

    /**
     * Create message templates through API on WhatsApp
     *
     * @param array $tplArr Formatted template array
     *
     * @return array API response
     */
    private function createMessageTemplate($tplArr)
    {
        $objTmpl = new WkWhatsAppTemplate();

        return $objTmpl->createWhatsAppTemplate($tplArr);
    }

    /**
     * Send order update notification
     *
     * @param array $params Order status data
     *
     * @return void
     */
    public function hookActionOrderStatusPostUpdate($params)
    {
        $controller = Tools::getValue('controller');
        if (empty($controller)) {
            $uri = $_SERVER['REQUEST_URI']; // it will print full url
            $uriArray = explode('/', $uri); // convert string into array with explode
            foreach ($uriArray as $index => $string) {
                if ($string == 'sell') {
                    $controller = $uriArray[$index + 1];
                    break;
                }
            }
        }
        $orderStatuses = json_decode(Configuration::get('WK_WAB_SEND_ORDER_STATUS'), true);
        $idOrderState = $params['newOrderStatus']->id;
        if (Configuration::get('WK_WAB_SEND_ORDER_UPDATE')
            && (empty($orderStatuses) || in_array($idOrderState, $orderStatuses))
        ) {
            if ($controller != 'order'
                && $controller != 'payment'
                && $controller != 'validation'
                && $controller != 'order-confirmation'
            ) {
                $order = new Order($params['id_order']);
                $objHelper = new WkWABHelper();
                $objHelper->sendOrderUpdateNotification($order);
            }
        }
    }

    /**
     * Send order confirmation notification to customer
     *
     * @param array $params Order data
     *
     * @return void
     */
    public function hookActionValidateOrder($params)
    {
        if (Configuration::get('WK_WAB_SEND_ORDER_CREATE')) {
            $objHelper = new WkWABHelper();
            $objHelper->sendOrderConfirmationNotification($params['order']);
        }
    }

    /**
     * Send order tracking notification to the customer
     *
     * @param array $params Order data
     *
     * @return void
     */
    public function hookActionAdminOrdersTrackingNumberUpdate($params)
    {
        if (Configuration::get('WK_WAB_SEND_ORDER_TRACK')) {
            $objHelper = new WkWABHelper();
            $objHelper->sendOrderTrackingNotification($params['order']);
        }
    }

    /**
     * Module default configuration
     *
     * @return bool
     */
    public function defaultConfiguration()
    {
        $token = sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xFFFF),
            mt_rand(0, 0xFFFF),
            mt_rand(0, 0xFFFF),
            mt_rand(0, 0x0FFF) | 0x4000,
            mt_rand(0, 0x3FFF) | 0x8000,
            mt_rand(0, 0xFFFF),
            mt_rand(0, 0xFFFF),
            mt_rand(0, 0xFFFF)
        );
        Configuration::updateValue('WAB_WEBHOOK_TOKEN', $token);
        Configuration::updateValue('WK_WAB_SEND_ORDER_CREATE', 1);
        Configuration::updateValue('WK_WAB_SEND_ORDER_TRACK', 1);
        Configuration::updateValue('WK_WAB_SEND_ORDER_UPDATE', 1);

        return true;
    }

    /**
     * Create module tabs
     *
     * @return bool
     */
    public function callInstallTab()
    {
        $this->installTab('AdminWkWhatsAppBusiness', 'WhatsApp Business');
        $this->installTab('AdminWkWhatsAppBusinessModule', 'WhatsApp Notification', 'AdminWkWhatsAppBusiness');
        $this->installTab('AdminWABConfig', 'Configuration', 'AdminWkWhatsAppBusinessModule');
        $this->installTab('AdminWABCampaign', 'Campaign', 'AdminWkWhatsAppBusinessModule');
        $this->installTab('AdminWABAnalytics', 'Message Analytics', 'AdminWkWhatsAppBusinessModule');

        return true;
    }

    /**
     * Create module tab
     *
     * @param string $className
     * @param string $tabName
     * @param bool $tabParentName
     *
     * @return bool
     */
    public function installTab($className, $tabName, $tabParentName = false)
    {
        $tab = new Tab();
        $tab->name = [];
        $tab->class_name = $className;
        $tab->active = 1;
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $tabName;
        }

        if ($tabParentName) {
            $tab->id_parent = (int) Tab::getIdFromClassName($tabParentName);
        } else {
            $tab->id_parent = 0;
        }

        if ($className == 'AdminWkWhatsAppBusinessModule') {
            $tab->icon = 'message';
        }

        $tab->module = $this->name;

        return $tab->add();
    }

    /**
     * Uninstall module
     *
     * @return bool
     */
    public function uninstall()
    {
        $objInstall = new WkWABInstall();
        if (!parent::uninstall()
            || !$objInstall->deleteTables()
            || !$this->uninstallTab()
            || !$this->deleteConfiguration()
        ) {
            return false;
        }

        return true;
    }

    /**
     * Uninstall module tabs
     *
     * @return bool
     */
    public function uninstallTab()
    {
        $moduleTabs = Tab::getCollectionFromModule($this->name);
        if (!empty($moduleTabs)) {
            foreach ($moduleTabs as $moduleTab) {
                $moduleTab->delete();
            }
        }

        return true;
    }

    /**
     * Delete module configuration variables
     *
     * @return bool
     */
    public function deleteConfiguration()
    {
        $keys = [
            'WK_WAB_PHONE_NUMBER_ID',
            'WK_WAB_ACCOUNT_ID',
            'WK_WAB_TOKEN',
            'WK_WAB_SEND_ORDER_CREATE',
            'WK_WAB_SEND_ORDER_TRACK',
            'WK_WAB_SEND_ORDER_UPDATE',
            'WK_WAB_SEND_ORDER_STATUS',
            'WAB_WEBHOOK_TOKEN',
            'WAB_WEBHOOK_TOKEN_VERIFIED',
        ];
        foreach ($keys as $key) {
            if (!Configuration::deleteByName($key)) {
                return false;
            }
        }

        return true;
    }
}
