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
 * versions in the future. If you wish to customize this module for your
 * needs please refer to CustomizationPolicy.txt file inside our module for more information.
 *
 * @author Webkul IN
 * @copyright Since 2010 Webkul
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

/**
 * WkWABHelper class
 *
 * This class is used as helper class for this module
 */
class WkWABHelper
{
    // Template name should not be greater than 512 characters
    public const WAB_ORDER_CONF_TPL_NAME = 'wab_order_create';
    public const WAB_ORDER_UPD_TPL_NAME = 'wab_order_update';
    public const WAB_ORDER_TRACK_TPL_NAME = 'wab_order_track';
    public const WAB_VERIFY_OTP_TPL_NAME = 'wab_verify_otp';
    public const WAB_OTP_VALIDITY = 5; // in minutes

    /**
     * Get all available languages ISO codes
     *
     * @return array
     */
    public static function getInstalledLangIsoCodes()
    {
        $langIsoCode = [];
        foreach (Language::getLanguages() as $lang) {
            if ($lang['iso_code'] == 'en') {
                $langIsoCode[$lang['id_lang']] = 'en_US';
            } elseif ($lang['iso_code'] == 'gb') {
                $langIsoCode[$lang['id_lang']] = 'en_GB';
            } elseif ($lang['iso_code'] == 'br') {
                $langIsoCode[$lang['id_lang']] = 'pt_BR';
            } elseif ($lang['iso_code'] == 'cn') {
                $langIsoCode[$lang['id_lang']] = 'zh_CN';
            } elseif ($lang['iso_code'] == 'hk') {
                $langIsoCode[$lang['id_lang']] = 'zh_HK';
            } elseif ($lang['iso_code'] == 'tw') {
                $langIsoCode[$lang['id_lang']] = 'zh_TW';
            } elseif ($lang['iso_code'] == 'rw') {
                $langIsoCode[$lang['id_lang']] = 'rw_RW';
            } elseif ($lang['iso_code'] == 'kg') {
                $langIsoCode[$lang['id_lang']] = 'ky_KG';
            } elseif ($lang['iso_code'] == 'pt') {
                $langIsoCode[$lang['id_lang']] = 'pt_PT';
            } elseif ($lang['iso_code'] == 'ar') {
                $langIsoCode[$lang['id_lang']] = 'es_AR';
            } elseif ($lang['iso_code'] == 'es') {
                $langIsoCode[$lang['id_lang']] = 'es_ES';
            } elseif ($lang['iso_code'] == 'mx') {
                $langIsoCode[$lang['id_lang']] = 'es_MX';
            } else {
                $langIsoCode[$lang['id_lang']] = $lang['iso_code'];
            }
        }

        return $langIsoCode;
    }

    /**
     * Log API response (messages & templates)
     *
     * @param string $logMsg Log message (json encoded)
     * @param bool $newLine If true then log message in new line
     *
     * @return void
     */
    public static function logMsg($logMsg, $newLine = false)
    {
        $file = fopen(dirname(__FILE__) . '/../log/wab.log', 'a');
        $error_msg = $newLine ? "\r\n\n" : "\n";
        $error_msg .= date('d-m-Y H:i:s') . '  ----  ' . $logMsg;
        fwrite($file, $error_msg);
        fclose($file);

        return true;
    }

    /**
     * Send order confirmation notification to the customer
     *
     * @param object $objOrder Order object
     *
     * @return void
     */
    public function sendOrderConfirmationNotification($objOrder)
    {
        $idLang = $objOrder->id_lang;
        $idShop = $objOrder->id_shop;
        $objCustomer = new Customer((int) $objOrder->id_customer);
        $mobileNumber = $this->getMobileNumber($objCustomer->id, $idShop);
        if ($mobileNumber) {
            $objSms = new WkWhatsAppMessage();
            $orderLink = WkWABHelper::getUrlShopLangString($idLang, $idShop) .
            'index.php?controller=order-detail?id_order=' . $objOrder->id;
            $data = [
                'var_1' => $objCustomer->firstname . ' ' . $objCustomer->lastname,
                'var_2' => $objOrder->reference,
                'var_3' => Tools::displayPrice($objOrder->total_paid_tax_incl),
                'var_4' => $orderLink,
            ];
            $objSms->sendOrderMessage(
                $mobileNumber,
                self::WAB_ORDER_CONF_TPL_NAME,
                $idLang,
                $data
            );
        }
    }

    /**
     * Send order update notification to the customer
     *
     * @param object $objOrder Order data
     *
     * @return void
     */
    public function sendOrderUpdateNotification($objOrder)
    {
        $idLang = $objOrder->id_lang;
        $idShop = $objOrder->id_shop;
        $objCustomer = new Customer((int) $objOrder->id_customer);
        $mobileNumber = $this->getMobileNumber($objCustomer->id, $idShop);
        if ($mobileNumber) {
            $objSms = new WkWhatsAppMessage();
            $orderLink = WkWABHelper::getUrlShopLangString($idLang, $idShop) .
            'index.php?controller=order-detail?id_order=' . $objOrder->id;
            $newOrderState = (new OrderState($objOrder->current_state, $idLang))->name;
            $data = [
                'var_1' => $objCustomer->firstname . ' ' . $objCustomer->lastname,
                'var_2' => $newOrderState,
                'var_3' => $objOrder->reference,
                'var_4' => $orderLink,
            ];
            $objSms->sendOrderMessage(
                $mobileNumber,
                self::WAB_ORDER_UPD_TPL_NAME,
                $idLang,
                $data
            );
        }
    }

    /**
     * Send order tracking notification to the customer
     *
     * @param object $objOrder Order data
     *
     * @return void
     */
    public function sendOrderTrackingNotification($objOrder)
    {
        $idLang = $objOrder->id_lang;
        $idShop = $objOrder->id_shop;
        $objCustomer = new Customer((int) $objOrder->id_customer);
        $mobileNumber = $this->getMobileNumber($objCustomer->id, $idShop);
        if ($mobileNumber) {
            $objSms = new WkWhatsAppMessage();
            $orderLink = WkWABHelper::getUrlShopLangString($idLang, $idShop) .
            'index.php?controller=order-detail?id_order=' . $objOrder->id;
            $trackingNumber = $this->getOrderTrackingNumber($objOrder->id);
            if (!$trackingNumber) {
                $trackingNumber = $objOrder->shipping_number;
            }
            $data = [
                'var_1' => $objCustomer->firstname . ' ' . $objCustomer->lastname,
                'var_2' => $trackingNumber,
                'var_3' => $objOrder->reference,
                'var_4' => $orderLink,
            ];
            $objSms->sendOrderMessage(
                $mobileNumber,
                self::WAB_ORDER_TRACK_TPL_NAME,
                $idLang,
                $data
            );
        }
    }

    /**
     * Get order tracking number by order ID
     *
     * @param int $idOrder Order ID
     *
     * @return array
     */
    public function getOrderTrackingNumber($idOrder)
    {
        return Db::getInstance()->getValue(
            'SELECT oc.`tracking_number`
            FROM `' . _DB_PREFIX_ . 'order_carrier` oc
            JOIN `' . _DB_PREFIX_ . 'orders` o
            ON (o.`id_order` = oc.`id_order`)
            WHERE oc.`id_order` = ' . (int) $idOrder
        );
    }

    /**
     * Send simple text message to mobile number
     *
     * @param int $mobileNumber Mobile number with country prefix
     * @param string $textMsg Text message
     *
     * @return void
     */
    public function sendSimpleTextMsg($mobileNumber, $textMsg)
    {
        $objSms = new WkWhatsAppMessage();

        return $objSms->sendSimpleTxtMessage(
            $mobileNumber,
            $textMsg
        );
    }

    /**
     * Send OTP code to the customer for verification
     *
     * @param int $mobileNumber Mobile number
     * @param int $otpCode OTP code
     * @param string $template Name of the template
     * @param int $idLang Language ID
     *
     * @return array API response
     */
    public function sendOtpCode($mobileNumber, $otpCode, $template, $idLang)
    {
        $objSms = new WkWhatsAppMessage();

        return $objSms->sendOtpCode(
            $mobileNumber,
            $otpCode,
            $template,
            $idLang
        );
    }

    /**
     * Get customer mobile number using customer ID
     *
     * @param int $idCustomer Customer ID
     * @param int $idShop Shop ID
     *
     * @return bool|int Return customer mobile number
     */
    public function getMobileNumber($idCustomer, $idShop = null)
    {
        $customerData = WkWABCustomer::getCustomerData($idCustomer, $idShop);
        if ($customerData && $customerData['active'] && $customerData['is_verified'] && $customerData['mobile']) {
            return $customerData['call_prefix'] . $customerData['mobile'];
        }

        return false;
    }

    /**
     * Check if module is configured or not
     *
     * @return bool
     */
    public static function isModuleConfigured()
    {
        if (Configuration::get('WK_WAB_PHONE_NUMBER_ID')
            && Configuration::get('WK_WAB_ACCOUNT_ID')
            && Configuration::get('WK_WAB_TOKEN')
        ) {
            return true;
        }

        return false;
    }

    /**
     * Validate API credentials provided by the admin
     *
     * @param int $phoneNumber Phone number ID
     * @param int $accountID Account ID
     * @param string $token Permanent token
     *
     * @return array Return API response
     */
    public function validateAPICredentials($phoneNumber, $accountID, $token)
    {
        $errors = [];
        $businessData = $this->validateCredentials($accountID, $token);
        $validatePhoneNumber = $this->validateCredentials($phoneNumber, $token);
        if ($businessData && array_key_exists('error', $businessData)) {
            $errors[] = $businessData['error']['message'];
        } elseif ($validatePhoneNumber && array_key_exists('error', $validatePhoneNumber)) {
            $errors[] = $validatePhoneNumber['error']['message'];
        }

        return $errors;
    }

    /**
     * Curl request to validate API credentials
     *
     * @param string $param Phone number or account ID
     * @param string $token Permanent token
     *
     * @return array API response
     */
    public function validateCredentials($param, $token)
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://graph.facebook.com/v17.0/' . $param,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $token,
            ],
        ]);
        $response = json_decode(curl_exec($curl), true);
        curl_close($curl);

        return $response;
    }

    /**
     * Get languages for the template on WhatsApp
     *
     * @param string $template Template name
     *
     * @return array Language IDs
     */
    public static function getAvailableTemplateLangIds($template)
    {
        $objWABTpl = new WkWhatsAppTemplate();
        $installedLangs = WkWABHelper::getInstalledLangIsoCodes();
        $orderTpl = [];
        $availOrderTplLangs = [];
        if (WkWABHelper::isModuleConfigured() && $template) {
            $orderTpl = $objWABTpl->getWhatsAppTemplates($template);
            if ($orderTpl && $orderTpl['success'] && $orderTpl['response']['data']) {
                foreach ($installedLangs as $idLang => $lang) {
                    foreach ($orderTpl['response']['data'] as $value) {
                        if ($lang == $value['language']) {
                            $availOrderTplLangs[] = $idLang;
                        }
                    }
                }
            }
        }

        return $availOrderTplLangs;
    }

    /**
     * Validate order and OTP templates string
     *
     * @param string $template Template name
     * @param string $key Template key (template key name used in the form)
     *
     * @return array Return error array if any
     */
    public function validateOrderTemplateString($template, $key)
    {
        $errors = [];
        $availOrderTplLangs = WkWABHelper::getAvailableTemplateLangIds($template);
        $objMod = Module::getInstanceByName('wkwhatsappbusiness');
        if ($key == 'create_order_conf') {
            $templateName = $objMod->l('Order confirmation', 'wkwabhelper');
        } elseif ($key == 'update_order') {
            $templateName = $objMod->l('Order status update', 'wkwabhelper');
        } elseif ($key == 'track_order') {
            $templateName = $objMod->l('Order tracking', 'wkwabhelper');
        } elseif ($key == 'verify_otp') {
            $templateName = $objMod->l('One time password', 'wkwabhelper');
        }
        foreach (Language::getLanguages() as $lang) {
            if (in_array($template, [self::WAB_VERIFY_OTP_TPL_NAME])) {
                if (!$availOrderTplLangs
                    || ($availOrderTplLangs && !in_array($lang['id_lang'], $availOrderTplLangs))
                ) {
                    $btnText = Tools::getValue($key . '_btn_txt_' . $lang['id_lang']);
                    if (Tools::strlen(trim($btnText)) == 0) {
                        $errors[] = sprintf(
                            $objMod->l('%s copy button text required for %s language.', 'wkwabhelper'),
                            $templateName,
                            $lang['name']
                        );
                    } elseif (Tools::strlen(trim($btnText)) > 25) {
                        $errors[] = sprintf(
                            $objMod->l(
                                '%s copy button text cannot be greater than 25 characters for %s language.',
                                'wkwabhelper'
                            ),
                            $templateName,
                            $lang['name']
                        );
                    } elseif ($btnText && !Validate::isCatalogName($btnText)) {
                        $errors[] = sprintf(
                            $objMod->l(
                                '%s copy button text invalid for %s language.',
                                'wkwabhelper'
                            ),
                            $templateName,
                            $lang['name']
                        );
                    }
                }
            } else {
                if (!$availOrderTplLangs
                    || ($availOrderTplLangs && !in_array($lang['id_lang'], $availOrderTplLangs))
                ) {
                    $headerText = Tools::getValue($key . '_header_' . $lang['id_lang']);
                    $messageBody = Tools::getValue($key . '_body_' . $lang['id_lang']);
                    $footerText = Tools::getValue($key . '_footer_' . $lang['id_lang']);
                    $btnText = Tools::getValue($key . '_btn_txt_' . $lang['id_lang']);
                    if (Tools::strlen(trim($headerText)) == 0) {
                        $errors[] = sprintf(
                            $objMod->l(
                                '%s header message required for %s language.',
                                'wkwabhelper'
                            ),
                            $templateName,
                            $lang['name']
                        );
                    } elseif (Tools::strlen(trim($headerText)) > 60) {
                        $errors[] = sprintf(
                            $objMod->l(
                                '%s header message cannot be greater than 60 characters for %s language.',
                                'wkwabhelper'
                            ),
                            $templateName,
                            $lang['name']
                        );
                    } elseif ($headerText && !Validate::isCatalogName($headerText)) {
                        $errors[] = sprintf(
                            $objMod->l(
                                '%s header message invalid for %s language.',
                                'wkwabhelper'
                            ),
                            $templateName,
                            $lang['name']
                        );
                    }
                    if (Tools::strlen(trim($messageBody)) == 0) {
                        $errors[] = sprintf(
                            $objMod->l('%s body message required for %s language.', 'wkwabhelper'),
                            $templateName,
                            $lang['name']
                        );
                    } elseif (Tools::strlen(trim($messageBody))) {
                        if ((strpos(trim($messageBody), '{{1}}') === false)
                            || (strpos(trim($messageBody), '{{2}}') === false)
                            || (strpos(trim($messageBody), '{{3}}') === false)
                        ) {
                            $errors[] = sprintf(
                                $objMod->l('%s template variable missing for %s language.', 'wkwabhelper'),
                                $templateName,
                                $lang['name']
                            );
                        }
                    } elseif (Tools::strlen(trim($messageBody)) > 1024) {
                        $errors[] = sprintf(
                            $objMod->l(
                                '%s body message cannot be greater than 1024 characters for %s language.',
                                'wkwabhelper'
                            ),
                            $templateName,
                            $lang['name']
                        );
                    }
                    if (Tools::strlen(trim($footerText)) == 0) {
                        $errors[] = sprintf(
                            $objMod->l('%s footer message required for %s language.', 'wkwabhelper'),
                            $templateName,
                            $lang['name']
                        );
                    } elseif (Tools::strlen(trim($footerText)) > 60) {
                        $errors[] = sprintf(
                            $objMod->l(
                                '%s footer message cannot be greater than 60 characters for %s language.',
                                'wkwabhelper'
                            ),
                            $templateName,
                            $lang['name']
                        );
                    } elseif ($footerText && !Validate::isCatalogName($footerText)) {
                        $errors[] = sprintf(
                            $objMod->l(
                                '%s footer message invalid for %s language.',
                                'wkwabhelper'
                            ),
                            $templateName,
                            $lang['name']
                        );
                    }
                    if (Tools::strlen(trim($btnText)) == 0) {
                        $errors[] = sprintf(
                            $objMod->l('%s button text required for %s language.', 'wkwabhelper'),
                            $templateName,
                            $lang['name']
                        );
                    } elseif (Tools::strlen(trim($btnText)) > 25) {
                        $errors[] = sprintf(
                            $objMod->l(
                                '%s button text cannot be greater than 25 characters for %s language.',
                                'wkwabhelper'
                            ),
                            $templateName,
                            $lang['name']
                        );
                    } elseif ($btnText && !Validate::isCatalogName($btnText)) {
                        $errors[] = sprintf(
                            $objMod->l(
                                '%s button text invalid for %s language.',
                                'wkwabhelper'
                            ),
                            $templateName,
                            $lang['name']
                        );
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Prepare template array as per API documentation
     *
     * @param string $template Template name
     * @param string $key Template key name
     *
     * @return array Formatted template array
     */
    public function prepareTemplateData($template, $key)
    {
        $tplArr = [];
        $langIsoCodes = WkWABHelper::getInstalledLangIsoCodes();
        $shopUrl = WkWABHelper::getDefaultShopUrl();
        $availableTempl = WkWABHelper::getAvailableTemplateLangIds($template);
        foreach ($langIsoCodes as $idLang => $isoCode) {
            if (in_array($template, [self::WAB_VERIFY_OTP_TPL_NAME])) {
                if (!$availableTempl
                    || ($availableTempl && !in_array($idLang, $availableTempl))
                ) {
                    $tplArr[$isoCode] = [
                        'name' => $template,
                        'language' => $isoCode,
                        'category' => 'AUTHENTICATION',
                        'components' => [
                            [
                                'type' => 'BODY',
                                'add_security_recommendation' => true,
                            ],
                            [
                                'type' => 'FOOTER',
                                'code_expiration_minutes' => self::WAB_OTP_VALIDITY,
                            ],
                            [
                                'type' => 'BUTTONS',
                                'buttons' => [
                                    [
                                        'type' => 'OTP',
                                        'otp_type' => 'COPY_CODE',
                                        'text' => trim(Tools::getValue($key . '_btn_txt_' . $idLang)),
                                    ],
                                ],
                            ],
                        ],
                    ];
                }
            } else {
                if (!$availableTempl
                    || ($availableTempl && !in_array($idLang, $availableTempl))
                ) {
                    $tplArr[$isoCode] = [
                        'name' => $template,
                        'language' => $isoCode,
                        'category' => 'UTILITY',
                        'components' => [
                            [
                                'type' => 'BODY',
                                'text' => trim(Tools::getValue($key . '_body_' . $idLang)),
                                'example' => self::getTemplateExampleValues($template),
                            ],
                            [
                                'type' => 'HEADER',
                                'format' => 'TEXT',
                                'text' => trim(Tools::getValue($key . '_header_' . $idLang)),
                            ],
                            [
                                'type' => 'FOOTER',
                                'text' => trim(Tools::getValue($key . '_footer_' . $idLang)),
                            ],
                            [
                                'type' => 'BUTTONS',
                                'buttons' => [
                                    [
                                        'type' => 'URL',
                                        'url' => $shopUrl . '{{1}}',
                                        'text' => trim(Tools::getValue($key . '_btn_txt_' . $idLang)),
                                        'example' => ['index.php?controller=order-detail&id_order=123'],
                                    ],
                                ],
                            ],
                        ],
                    ];
                }
            }
        }

        return $tplArr;
    }

    /**
     * Log webhook response
     *
     * @param string $logMsg Webhook log message (json encoded)
     * @param bool $newLine Log message in new line
     *
     * @return bool
     */
    public static function logWebhookResponse($logMsg, $newLine = false)
    {
        $file = fopen(dirname(__FILE__) . '/../log/webhook.log', 'a');
        $error_msg = $newLine ? "\r\n\n" : "\n";
        $error_msg .= date('d-m-Y H:i:s') . '  ----  ' . $logMsg;
        fwrite($file, $error_msg);
        fclose($file);

        return true;
    }

    /**
     * Get shop URL
     *
     * @return string
     */
    public static function getDefaultShopUrl()
    {
        return Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__;
    }

    /**
     * Get shop virtual URI (shop slug)
     *
     * @param int $idShop Shop ID
     *
     * @return string
     */
    public static function getShopSlug($idShop = null)
    {
        if (Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') && $idShop !== null) {
            $shop = new Shop($idShop);
        } else {
            $shop = Context::getContext()->shop;
        }

        return $shop->virtual_uri;
    }

    /**
     * Get language ISO code
     *
     * @param int $idLang Language ID
     * @param Context|null $context
     * @param int $idShop Shop ID
     *
     * @return string Language ISO code
     */
    public static function getLangLink($idLang = null, Context $context = null, $idShop = null)
    {
        static $psRewritingSettings = null;
        if ($psRewritingSettings === null) {
            $psRewritingSettings = (int) Configuration::get('PS_REWRITING_SETTINGS', null, null, $idShop);
        }

        if (!$context) {
            $context = Context::getContext();
        }

        if ((!Configuration::get('PS_REWRITING_SETTINGS')
            && in_array($idShop, [$context->shop->id,  null]))
            || !Language::isMultiLanguageActivated($idShop) || !$psRewritingSettings
        ) {
            return '';
        }

        if (!$idLang) {
            $idLang = $context->language->id;
        }

        return Language::getIsoById($idLang) . '/';
    }

    /**
     * Get URL string with available shop and language slug
     *
     * @param int $idLang Language ID
     * @param int $idShop Shop ID
     *
     * @return string
     */
    public static function getUrlShopLangString($idLang, $idShop)
    {
        return WkWABHelper::getShopSlug($idShop) . WkWABHelper::getLangLink($idLang);
    }

    /**
     * Get template example values
     *
     * @param string $template Name of the template
     *
     * @return array
     */
    public static function getTemplateExampleValues($template)
    {
        $templates = [
            self::WAB_ORDER_CONF_TPL_NAME => [
                'body_text' => [
                    [
                        'John Doe',
                        'ABCXYZ123',
                        '$123.45',
                    ],
                ],
            ],
            self::WAB_ORDER_UPD_TPL_NAME => [
                'body_text' => [
                    [
                        'John Doe',
                        'Shipped',
                        'ABCXYZ123',
                    ],
                ],
            ],
            self::WAB_ORDER_TRACK_TPL_NAME => [
                'body_text' => [
                    [
                        'John Doe',
                        'AA0123456789BB',
                        'ABCXYZ123',
                    ],
                ],
            ],
        ];

        return $templates[$template];
    }
}
