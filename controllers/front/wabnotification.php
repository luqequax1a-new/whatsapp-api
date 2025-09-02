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
 * WkWhatsAppBusinessWABNotificationModuleFrontController class
 *
 * This class is used to verify and save customer WhatsApp details
 */
class WkWhatsAppBusinessWABNotificationModuleFrontController extends ModuleFrontController
{
    /**
     * Force user to login
     */
    public $auth = true;
    public $guestAllowed = false;

    public function initContent()
    {
        parent::initContent();
        if (!WkWABHelper::isModuleConfigured()) {
            Tools::redirect($this->context->link->getPageLink('index'));
        }
        $idCustomer = (int) $this->context->customer->id;
        $customerData = WkWABCustomer::getCustomerData($idCustomer);
        if (Tools::getValue('verify_otp')
            && $customerData
            && $customerData['otp']
            && ($customerData['otp_validity'] > time())
            && !$customerData['is_verified']
        ) {
            $this->context->smarty->assign(
                [
                    'action' => $this->context->link->getModuleLink(
                        'wkwhatsappbusiness',
                        'wabnotification'
                    ),
                    'customerData' => $customerData,
                    'token' => $this->module->secure_key,
                    'wkpage' => 'verify_otp',
                ]
            );
        } else {
            $countries = Country::getCountries((int) $this->context->language->id);
            $this->context->smarty->assign(
                [
                    'action' => $this->context->link->getModuleLink(
                        'wkwhatsappbusiness',
                        'wabnotification'
                    ),
                    'token' => $this->module->secure_key,
                    'customerData' => $customerData,
                    'countries' => $countries,
                    'id_module' => $this->module->id,
                ]
            );
        }
        $this->setTemplate(
            'module:' . $this->module->name . '/views/templates/front/wabnotification.tpl'
        );
    }

    /**
     * Validate and save customer mobile number
     *
     * @return void
     */
    public function postProcess()
    {
        if (Tools::isSubmit('submitWABNotification')) {
            if (Tools::getValue('token') == $this->module->secure_key) {
                $receiveNotification = Tools::getValue('receive_notification');
                $callPrefix = Tools::getValue('call_prefix');
                $whatsappNumber = trim(Tools::getValue('whatsapp_number'));
                if ($receiveNotification) {
                    if ($callPrefix == '') {
                        $this->errors[] = $this->module->l('Please select mobile number prefix.', 'wabnotification');
                    }
                    if (Tools::strlen($whatsappNumber) == 0) {
                        $this->errors[] = $this->module->l('Please enter WhatsApp mobile number.', 'wabnotification');
                    } elseif (!Validate::isPhoneNumber($whatsappNumber) || ((int) $whatsappNumber == 0)) {
                        $this->errors[] = $this->module->l('Invalid mobile number.', 'wabnotification');
                    }
                }
                if (empty($this->errors)) {
                    $idCustomer = (int) $this->context->customer->id;
                    $customerData = WkWABCustomer::getCustomerData($idCustomer);
                    if ($customerData) {
                        $objCustomer = new WkWABCustomer((int) $customerData['id_wk_wab_customer']);
                    } else {
                        $objCustomer = new WkWABCustomer();
                    }
                    $objCustomer->active = $receiveNotification;
                    $objCustomer->call_prefix = (int) $callPrefix;
                    $objCustomer->id_customer = (int) $idCustomer;
                    $objCustomer->id_shop = (int) $this->context->shop->id;
                    $objCustomer->mobile = pSQL($whatsappNumber);
                    if ($objCustomer->save()) {
                        if ((!$customerData
                            && ($receiveNotification == 1)
                            && ($whatsappNumber != ''))
                            || (($customerData && ($whatsappNumber != '' && $customerData['mobile'] != $whatsappNumber))
                            || ($customerData && ($whatsappNumber != '' && $customerData['call_prefix'] != $callPrefix))
                            || ($customerData && !$customerData['is_verified'] && $whatsappNumber != ''))
                        ) {
                            $otpCode = rand(100000, 999999);
                            $objCustomer->is_verified = 0;
                            $objCustomer->otp = $otpCode;
                            $otpValidity = WkWABHelper::WAB_OTP_VALIDITY;
                            $objCustomer->otp_validity = strtotime(
                                date(
                                    'Y-m-d H:i:s',
                                    strtotime("+$otpValidity minutes")
                                )
                            );
                            $objCustomer->save();
                            $completeNumber = $callPrefix . $whatsappNumber;
                            $this->sendOTP($completeNumber, $otpCode);
                            if (empty($this->errors)) {
                                Tools::redirect($this->context->link->getModuleLink(
                                    'wkwhatsappbusiness',
                                    'wabnotification',
                                    [
                                        'verify_otp' => 1,
                                    ]
                                ));
                            }
                        } else {
                            if ($customerData) {
                                $this->success[] = $this->module->l('Details updated successfully.', 'wabnotification');
                            } else {
                                $this->success[] = $this->module->l('Details saved successfully.', 'wabnotification');
                            }
                            $this->redirectWithNotifications($this->context->link->getModuleLink(
                                'wkwhatsappbusiness',
                                'wabnotification'
                            ));
                        }
                    } else {
                        $this->errors[] = $this->module->l('Something went wrong.', 'wabnotification');
                    }
                }
            } else {
                $this->errors[] = $this->module->l('Invalid token.', 'wabnotification');
            }
        } elseif (Tools::isSubmit('submitVerifyWAnumber')) {
            if (Tools::getValue('token') == $this->module->secure_key) {
                $idCustomer = (int) $this->context->customer->id;
                $customerData = WkWABCustomer::getCustomerData($idCustomer);
                if ($customerData
                    && $customerData['mobile']
                    && !$customerData['is_verified']
                    && $customerData['otp']
                ) {
                    $otpCode = Tools::getValue('whatsapp_number_otp');
                    if ($otpCode && (Tools::strlen($otpCode) != 6)) {
                        $this->errors[] = $this->module->l('OTP code must be in 6-digit.', 'wabnotification');
                        $this->redirectWithNotifications($this->context->link->getModuleLink(
                            'wkwhatsappbusiness',
                            'wabnotification',
                            [
                                'verify_otp' => 1,
                            ]
                        ));
                    }
                    if (time() > $customerData['otp_validity']) {
                        $this->errors[] = $this->module->l(
                            'OTP code has been expired. Please try again.',
                            'wabnotification'
                        );
                        $this->redirectWithNotifications($this->context->link->getModuleLink(
                            'wkwhatsappbusiness',
                            'wabnotification',
                            [
                                'verify_otp' => 1,
                            ]
                        ));
                    }
                    if ($otpCode == $customerData['otp']) {
                        $objCustomer = new WkWABCustomer((int) $customerData['id_wk_wab_customer']);
                        $objCustomer->is_verified = 1;
                        $objCustomer->save();
                        $this->success[] = $this->module->l(
                            'Your WhatsApp number has been verified successfully.',
                            'wabnotification'
                        );
                        $this->redirectWithNotifications($this->context->link->getModuleLink(
                            'wkwhatsappbusiness',
                            'wabnotification'
                        ));
                    } else {
                        $this->errors[] = $this->module->l('Invalid OTP code.', 'wabnotification');
                        $this->redirectWithNotifications($this->context->link->getModuleLink(
                            'wkwhatsappbusiness',
                            'wabnotification',
                            [
                                'verify_otp' => 1,
                            ]
                        ));
                    }
                } else {
                    $this->errors[] = $this->module->l('Something went wrong.', 'wabnotification');
                    $this->redirectWithNotifications($this->context->link->getModuleLink(
                        'wkwhatsappbusiness',
                        'wabnotification',
                        [
                            'verify_otp' => 1,
                        ]
                    ));
                }
            } else {
                $this->errors[] = $this->module->l('Invalid token.', 'wabnotification');
                $this->redirectWithNotifications($this->context->link->getModuleLink(
                    'wkwhatsappbusiness',
                    'wabnotification',
                    [
                        'verify_otp' => 1,
                    ]
                ));
            }
        }
    }

    /**
     * Send OTP to customer mobile number
     *
     * @param int $whatsappNumber
     * @param int $otpCode
     *
     * @return void
     */
    private function sendOTP($whatsappNumber, $otpCode)
    {
        $objHelper = new WkWABHelper();
        $response = $objHelper->sendOtpCode(
            $whatsappNumber,
            $otpCode,
            WkWABHelper::WAB_VERIFY_OTP_TPL_NAME,
            (int) $this->context->language->id
        );
        if ($response == null) {
            $this->errors[] = $this->module->l('Application request limit reached.', 'wabnotification');
        } elseif (!$response['success'] && array_key_exists('error', $response['response'])) {
            $this->errors[] = $response['response']['error']['message'];
        }
    }

    /**
     * Resend OTP code for mobile number verification
     *
     * @return string json response
     */
    public function displayAjaxResendOtp()
    {
        if (Tools::getValue('token') == $this->module->secure_key) {
            $idCustomer = (int) $this->context->customer->id;
            $customerData = WkWABCustomer::getCustomerData($idCustomer);
            if ($customerData && $customerData['mobile'] && !$customerData['is_verified']) {
                $objCustomer = new WkWABCustomer((int) $customerData['id_wk_wab_customer']);
                $otpCode = rand(100000, 999999);
                $objCustomer->is_verified = 0;
                $objCustomer->otp = $otpCode;
                $otpValidity = WkWABHelper::WAB_OTP_VALIDITY;
                $objCustomer->otp_validity = strtotime(
                    date(
                        'Y-m-d H:i:s',
                        strtotime("+$otpValidity minutes")
                    )
                );
                $objCustomer->save();
                $completeNumber = $customerData['call_prefix'] . $customerData['mobile'];
                $this->sendOTP($completeNumber, $otpCode);
                if (empty($this->errors)) {
                    exit(json_encode([
                        'success' => true,
                        'message' => $this->module->l('OTP has been sent successfully.', 'wabnotification'),
                    ]));
                } else {
                    exit(json_encode([
                        'success' => false,
                        'message' => $this->errors,
                    ]));
                }
            }
        }
        exit(json_encode([
            'success' => false,
            'message' => $this->module->l('Invalid request!', 'wabnotification'),
        ]));
    }

    /**
     * Prepare breadcrumb for this controller view
     *
     * @return array
     */
    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = [
            'title' => $this->module->l('WhatsApp notifications', 'wabnotification'),
            'url' => '',
        ];

        return $breadcrumb;
    }

    /**
     * Load JS and CSS files
     *
     * @return void
     */
    public function setMedia()
    {
        parent::setMedia();
        Media::addJsDef([
            'wkWabAjaxLink' => $this->context->link->getModuleLink(
                'wkwhatsappbusiness',
                'wabnotification'
            ),
            'wkToken' => $this->module->secure_key,
        ]);
        $this->registerStylesheet(
            'wabnotification-css',
            'modules/' . $this->module->name . '/views/css/front/wabnotification.css'
        );
        $this->registerJavascript(
            'wabnotification-js',
            'modules/' . $this->module->name . '/views/js/front/wabnotification.js'
        );
    }
}
