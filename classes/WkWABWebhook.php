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
 * WkWABWebhook class
 *
 * This class is used to handle webhook response
 */
class WkWABWebhook
{
    /**
     * Handle customer reply
     *
     * @param object $decodedCustomerReply
     *
     * @return void
     */
    public static function customerReplied($decodedCustomerReply)
    {
        if (isset($decodedCustomerReply->object)
        && $decodedCustomerReply->object === 'whatsapp_business_account'
        ) {
            if (isset($decodedCustomerReply->entry, $decodedCustomerReply->entry[0]->changes, $decodedCustomerReply->entry[0]->changes[0]->value->messages, $decodedCustomerReply->entry[0]->changes[0]->value->messages[0])
            ) {
                WkWABHelper::logWebhookResponse(
                    json_encode($decodedCustomerReply->entry[0]->changes[0]->value->messages[0]->from)
                );
                WkWABHelper::logWebhookResponse(
                    json_encode($decodedCustomerReply->entry[0]->changes[0]->value->messages[0]->text->body)
                );
                WkWABHelper::logWebhookResponse(
                    json_encode($decodedCustomerReply->entry[0]->changes[0]->value->messages[0]->type)
                );

                $message = [];
                $message = $decodedCustomerReply->entry[0]->changes[0]->value->messages[0];

                $mobileNumberToReply = $message->from;
                $customerMsgType = $message->type;

                if ($customerMsgType == 'text') {
                    $customerMsg = $message->text->body;
                }
                if ($customerMsgType == 'interactive') {
                    $customerMsg = $message->interactive->list_reply->id;
                    WkWABHelper::logWebhookResponse(
                        $customerMsg
                    );
                }
                if ($customerMsgType == 'button') {
                    $customerMsg = $message->button->payload;
                }

                self::sendReply($mobileNumberToReply, $customerMsg, $customerMsgType);
            }
        }
    }

    /**
     * Send reponse to customer query through webhook
     *
     * @param int $mobileNumberToReply
     * @param string $customerMsg
     * @param sting $customerMsgType
     *
     * @return void
     */
    public static function sendReply($mobileNumberToReply, $customerMsg, $customerMsgType)
    {
        if (is_string($customerMsg) && $customerMsgType == 'text') {
            self::prepareTemplateDataForCategoryList($mobileNumberToReply);
        }
        if (isset($customerMsg) && $customerMsgType == 'interactive') {
            $selectedCategoryId = $customerMsg;
            self::prepareTemplateDataForCategoryPage($mobileNumberToReply, $selectedCategoryId);
        }
        if (isset($customerMsg) && $customerMsgType == 'button') {
            $payload = $customerMsg;
            self::prepareTemplateDataButtonPayload($mobileNumberToReply, $payload);
        }
    }

    /**
     * Prepare interactive message for category list
     *
     * @param int $mobileNumber
     *
     * @return void
     */
    public static function prepareTemplateDataForCategoryList($mobileNumber)
    {
        $messagePaylaod = [];
        $messagePaylaod['messaging_product'] = 'whatsapp';
        $messagePaylaod['recipient_type'] = 'individual';
        $messagePaylaod['to'] = $mobileNumber;
        $messagePaylaod['type'] = 'interactive';
        $messagePaylaod['interactive']['type'] = 'list';
        $messagePaylaod['interactive']['header'] = self::getTemplateHeaderComponents();
        $messagePaylaod['interactive']['body'] = self::getTemplateBodyComponents();
        $messagePaylaod['interactive']['footer'] = self::getTemplateFooterComponents();
        $messagePaylaod['interactive']['action'] = self::getTemplateActionComponents();

        $objSms = new WkWhatsAppMessage();
        $objSms->sendCampaignMessage($messagePaylaod);

        WkWABHelper::logWebhookResponse('--Webhook Sending Reply--', true);
        WkWABHelper::logWebhookResponse('Payload : ');
        WkWABHelper::logWebhookResponse(json_encode($messagePaylaod));
        exit;
    }

    /**
     * Prepare simple text message for specific category
     *
     * @param int $mobileNumber Customer mobile number
     * @param int $selectedCategoryId Category ID
     *
     * @return void
     */
    public static function prepareTemplateDataForCategoryPage($mobileNumber, $selectedCategoryId)
    {
        $idLang = Context::getContext()->language->id;
        $categoryInformation = new Category($selectedCategoryId);
        $categoryName = $categoryInformation->name[$idLang];
        $categoryLinkRewrite = $categoryInformation->link_rewrite[$idLang];

        $messagePaylaod = [];
        $messagePaylaod['messaging_product'] = 'whatsapp';
        $messagePaylaod['recipient_type'] = 'individual';
        $messagePaylaod['to'] = $mobileNumber;
        $messagePaylaod['type'] = 'text';
        $messagePaylaod['text']['preview_url'] = true;

        $wkModule = Module::getInstanceByName('wkwhatsappbusiness');
        $catLink = Context::getContext()->link->getCategoryLink($selectedCategoryId, $categoryLinkRewrite);

        $messagePaylaod['text']['body'] = '*' . $categoryName . '*' . "\n\n" .
        $wkModule->l('Buy the trendiest collection from the category', 'webhook') . "\n\n" . $catLink;

        $objSms = new WkWhatsAppMessage();
        $objSms->sendCampaignMessage($messagePaylaod);

        WkWABHelper::logWebhookResponse('--Webhook Sending Reply--', true);
        WkWABHelper::logWebhookResponse('Payload : ');
        WkWABHelper::logWebhookResponse(json_encode($messagePaylaod));
        exit;
    }

    /**
     * Prepare button action selected by customer
     *
     * @param int $mobileNumber
     * @param string $payload
     *
     * @return void
     */
    public static function prepareTemplateDataButtonPayload($mobileNumber, $payload)
    {
        if ($payload == 'chat') {
            self::prepareTemplateDataForCategoryList($mobileNumber);
        } elseif ($payload == 'site') {
            self::prepareTemplateDataForGotoSite($mobileNumber);
        } else {
            $categoryId = (int) str_replace('view_', '', $payload);
            self::prepareTemplateDataForCategoryPage($mobileNumber, $categoryId);
        }
        exit;
    }

    /**
     * Prepare and send notification for visit site
     *
     * @param int $mobileNumber
     *
     * @return void
     */
    public static function prepareTemplateDataForGotoSite($mobileNumber)
    {
        $shop = Context::getContext()->shop;
        $shopName = $shop->name;
        $shopUrl = Context::getContext()->link->getBaseLink($shop->id);
        $messagePaylaod = [];
        $messagePaylaod['messaging_product'] = 'whatsapp';
        $messagePaylaod['recipient_type'] = 'individual';
        $messagePaylaod['to'] = $mobileNumber;
        $messagePaylaod['type'] = 'text';
        $messagePaylaod['text']['preview_url'] = true;
        $wkModule = Module::getInstanceByName('wkwhatsappbusiness');
        $messagePaylaod['text']['body'] = '*' . $shopName . '*' . "\n\n" .
        $wkModule->l('Cick here to visit store and get exclusive offer for you.', 'webhook') . "\n" . $shopUrl;
        $objSms = new WkWhatsAppMessage();
        $objSms->sendCampaignMessage($messagePaylaod);
        WkWABHelper::logWebhookResponse('--Webhook Sending Reply--', true);
        WkWABHelper::logWebhookResponse('Payload : ');
        WkWABHelper::logWebhookResponse(json_encode($messagePaylaod));
        exit;
    }

    /**
     * Get message header component
     *
     * @return array
     */
    public static function getTemplateHeaderComponents()
    {
        $components = [];
        $components['type'] = 'text';
        $components['text'] = Context::getContext()->shop->name;

        return $components;
    }

    /**
     * Get message body component
     *
     * @return array
     */
    public static function getTemplateBodyComponents()
    {
        $wkModule = Module::getInstanceByName('wkwhatsappbusiness');
        $components = [];
        $components['text'] = $wkModule->l('Click on Explore More to check our our catolog', 'webhook');

        return $components;
    }

    /**
     * Get message footer component
     *
     * @return array
     */
    public static function getTemplateFooterComponents()
    {
        $shopName = Context::getContext()->shop->name;
        $wkModule = Module::getInstanceByName('wkwhatsappbusiness');
        $components = [];
        $components['text'] = $wkModule->l('From', 'webhook') . ' ' . $shopName . ' ' . $wkModule->l('Admin', 'webhook');

        return $components;
    }

    /**
     * Get template button actions component
     *
     * @return array
     */
    public static function getTemplateActionComponents()
    {
        $idLang = Context::getContext()->language->id;
        $components = [];
        $components['button'] = 'Explore More';
        $shop = new Shop(Context::getContext()->shop->id);
        $root_category = Category::getRootCategory(null, $shop);
        $rootCategoryArr = Category::getNestedCategories($root_category->id, $idLang, true, null);
        $mainCategories = $rootCategoryArr[$root_category->id]['children'];
        $sections = [];
        foreach ($mainCategories as $categories) {
            $category = [];
            $category['title'] = self::trimMessageTitle($categories['name']);
            if (isset($categories['children'])) {
                $category['rows'] = [];
                foreach ($categories['children'] as $child) {
                    $row = [];
                    $row['id'] = $child['id_category'];
                    $row['title'] = self::trimMessageTitle($child['name']);
                    $row['description'] = self::trimMessageTitle(
                        Category::getDescriptionClean($child['description']),
                        72
                    );
                    $category['rows'][] = $row;
                }
            } else {
                $category['rows'] = [];
                $row = [];
                $row['id'] = $categories['id_category'];
                $row['title'] = self::trimMessageTitle($categories['name']);
                $row['description'] = self::trimMessageTitle(
                    Category::getDescriptionClean($categories['description']),
                    72
                );
                $category['rows'][] = $row;
            }
            $sections[] = $category;
        }
        $components['sections'] = $sections;

        return $components;
    }

    private static function trimMessageTitle($message, $limit = 24)
    {
        return Tools::strlen($message) <= $limit ? $message : Tools::substr($message, 0, $limit - 3) . '...';
    }
}
