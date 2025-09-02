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
 * WkWABCustomer class
 *
 * This class is used to manage customer WhatsApp details
 */
class WkWABCustomer extends ObjectModel
{
    public $id_wk_wab_customer;
    public $id_customer;
    public $id_shop;
    public $call_prefix;
    public $mobile;
    public $is_verified;
    public $otp;
    public $otp_validity;
    public $active;
    public $date_add;
    public $date_upd;

    public static $definition = [
        'table' => 'wk_wab_customer',
        'primary' => 'id_wk_wab_customer',
        'multilang' => false,
        'fields' => [
            'id_customer' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'id_shop' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'call_prefix' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'mobile' => ['type' => self::TYPE_STRING],
            'is_verified' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'otp' => ['type' => self::TYPE_STRING],
            'otp_validity' => ['type' => self::TYPE_STRING],
            'active' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat'],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat'],
        ],
    ];

    /**
     * Get customer data by customer ID
     *
     * @param int $idCustomer Customer ID
     * @param int $idShop Shop ID
     *
     * @return array Return single customer data
     */
    public static function getCustomerData($idCustomer, $idShop = null)
    {
        if ($idShop == null) {
            $idShop = (int) Context::getContext()->shop->id;
        }

        return Db::getInstance()->getRow(
            'SELECT *
            FROM `' . _DB_PREFIX_ . 'wk_wab_customer`
            WHERE `id_customer` = ' . (int) $idCustomer . '
            AND `id_shop` = ' . (int) $idShop
        );
    }

    /**
     * Get all customer data
     *
     * @param int $idShop Shop ID
     *
     * @return array Return all customer data based on the shop ID
     */
    public static function getAllCustomerId($idShop = null)
    {
        if ($idShop == null) {
            $idShop = (int) Context::getContext()->shop->id;
        }

        return Db::getInstance()->executeS(
            'SELECT `id_customer`
            FROM `' . _DB_PREFIX_ . 'wk_wab_customer`
            WHERE `mobile` != "" AND `is_verified`= "1" AND `active` = "1" AND `id_shop` = ' . (int) $idShop
        );
    }
}
