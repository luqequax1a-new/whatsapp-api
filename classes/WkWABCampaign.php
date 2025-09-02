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
 * WkWABCampaign class
 *
 * This class is used for campaign management
 */
class WkWABCampaign extends ObjectModel
{
    public $id_wk_wab_campaign;
    public $campaign_name;
    public $id_shop;

    public $header_type;
    public $header_media_url;
    public $campaign_header; // 60  or media
    public $campaign_description; // 1024
    public $campaign_footer;  // 60

    public $button_status;
    public $button_type; // 0 call to action, 1 quickreply

    // for button type call to action can be 2 call and visit or one
    public $button_action_type; // 0 call, 1 visit website

    public $call_button_text; // 25
    public $coutry_code; // +91, +1
    public $phone; // for button_action_type  call

    public $visit_button_text; // 25
    public $url_type; // 0 static, 1 dynamic
    public $url; // for button_action_type  visit website

    // for button type quick reply
    public $first_quick_reply_text; // 25  can be 3  quicky reply button
    public $second_quick_reply_text; // 25  can be 3  quicky reply button
    public $third_quick_reply_text; // 25  can be 3  quicky reply button

    public $categories;
    public $customers;
    public $status;
    public $expiry_date;

    public $date_add;
    public $date_upd;

    public static $definition = [
        'table' => 'wk_wab_campaign',
        'primary' => 'id_wk_wab_campaign',
        'multilang' => true,
        'fields' => [
            'id_shop' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'campaign_name' => ['type' => self::TYPE_STRING, 'required' => true],
            'header_type' => ['type' => self::TYPE_STRING, 'required' => true],
            'header_media_url' => ['type' => self::TYPE_STRING, 'validate' => 'isUrl', 'required' => false],
            'button_status' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'button_type' => ['type' => self::TYPE_STRING, 'required' => false],
            'button_action_type' => ['type' => self::TYPE_STRING, 'required' => false],
            'coutry_code' => ['type' => self::TYPE_STRING, 'required' => false],
            'phone' => ['type' => self::TYPE_INT, 'required' => false],
            'url_type' => ['type' => self::TYPE_STRING, 'required' => false],
            'url' => ['type' => self::TYPE_STRING, 'required' => false],
            'categories' => ['type' => self::TYPE_STRING, 'required' => false],
            'customers' => ['type' => self::TYPE_STRING, 'required' => false],
            'status' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'expiry_date' => ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => true],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => true],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => true],

            /* Lang fields */
            'campaign_header' => ['type' => self::TYPE_STRING, 'required' => false, 'lang' => true],
            'campaign_description' => ['type' => self::TYPE_STRING, 'required' => true, 'lang' => true],
            'campaign_footer' => ['type' => self::TYPE_STRING, 'required' => false, 'lang' => true],
            'call_button_text' => ['type' => self::TYPE_STRING, 'required' => false, 'lang' => true],
            'visit_button_text' => ['type' => self::TYPE_STRING, 'required' => false, 'lang' => true],
            'first_quick_reply_text' => ['type' => self::TYPE_STRING, 'required' => false, 'lang' => true],
            'second_quick_reply_text' => ['type' => self::TYPE_STRING, 'required' => false, 'lang' => true],
            'third_quick_reply_text' => ['type' => self::TYPE_STRING, 'required' => false, 'lang' => true],
        ],
    ];

    /**
     * Get campaign details by campaign name
     *
     * @param string $campaignName
     *
     * @return array|void
     */
    public static function getCampaignDetailsByName($campaignName)
    {
        return Db::getInstance()->getRow(
            'SELECT *
            FROM `' . _DB_PREFIX_ . 'wk_wab_campaign`
            WHERE `campaign_name` = "' . pSQL($campaignName) . '"'
        );
    }
}
