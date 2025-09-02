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
include_once '../../config/config.inc.php';
include_once 'wkwhatsappbusiness.php';

class WkWhatsAppListener
{
    public function verifyToken()
    {
        if (Configuration::get('WAB_WEBHOOK_TOKEN_VERIFIED') == 0
            && Tools::getIsset('hub_mode')
            && Tools::getIsset('hub_challenge')
            && Tools::getIsset('hub_verify_token')
        ) {
            WkWABHelper::logWebhookResponse('--Webhook Receiving Token Verification -- Start', true);
            WkWABHelper::logWebhookResponse('--Webhook Receiving -- ' . __LINE__, true);
            WkWABHelper::logWebhookResponse('--Webhook Receiving -- ' .
            Configuration::get('WAB_WEBHOOK_TOKEN_VERIFIED'), true);
            WkWABHelper::logWebhookResponse('--Webhook Receiving -- ' . Configuration::get('WAB_WEBHOOK_TOKEN'), true);
            WkWABHelper::logWebhookResponse(json_encode($_GET));
            $hubMode = Tools::getValue('hub_mode');
            $hubChallenge = Tools::getValue('hub_challenge');
            $hubVerifyToken = Tools::getValue('hub_verify_token');

            if ($hubMode && $hubVerifyToken) {
                WkWABHelper::logWebhookResponse('--Webhook Receiving -- ' . __LINE__, true);
                if ($hubMode === 'subscribe' && $hubVerifyToken === Configuration::get('WAB_WEBHOOK_TOKEN')) {
                    WkWABHelper::logWebhookResponse('--Webhook Receiving -- ' . __LINE__, true);
                    Configuration::updateValue('WAB_WEBHOOK_TOKEN_VERIFIED', 1);
                    WkWABHelper::logWebhookResponse('--Webhook Receiving -- WAB_WEBHOOK_TOKEN_VERIFIED', true);
                    header('HTTP/1.1 200 OK');
                    exit($hubChallenge);
                } else {
                    header('HTTP/1.1 403 Forbidden');
                    exit;
                }
            }
        }

        if (Configuration::get('WAB_WEBHOOK_TOKEN_VERIFIED') == 1) {
            $customerReply = Tools::file_get_contents('php://input');
            $decodedCustomerReply = json_decode($customerReply);
            if (isset($decodedCustomerReply->object)
            && $decodedCustomerReply->object === 'whatsapp_business_account'
            ) {
                if (isset($decodedCustomerReply->entry, $decodedCustomerReply->entry[0]->changes, $decodedCustomerReply->entry[0]->changes[0]->value->messages, $decodedCustomerReply->entry[0]->changes[0]->value->messages[0])
                ) {
                    if (is_object($decodedCustomerReply)) {
                        WkWABHelper::logWebhookResponse('--Webhook Receiving Customer Reply--', true);
                        WkWABHelper::logWebhookResponse('Customer Reply:');
                        WkWABHelper::logWebhookResponse(json_encode($decodedCustomerReply));
                        WkWABWebhook::customerReplied($decodedCustomerReply);
                        header('HTTP/1.1 200 OK');
                        exit;
                    }
                }
                exit;
            }
            exit;
        }
        exit;
    }
}
$runListener = new WkWhatsAppListener();
$runListener->verifyToken();
