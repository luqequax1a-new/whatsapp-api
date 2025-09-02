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
 * WkWABInstall class
 *
 * This class is used to create and delete the module database tables
 */
class WkWABInstall
{
    /**
     * Create module tables
     *
     * @return bool
     */
    public function createTables()
    {
        $success = true;
        $queries = $this->getDbTableQueries();
        $db = Db::getInstance(_PS_USE_SQL_SLAVE_);
        foreach ($queries as $query) {
            $success &= $db->execute($query);
        }

        return $success;
    }

    /**
     * Get module table queries
     *
     * @return array
     */
    private function getDbTableQueries()
    {
        return [
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . "wk_wab_customer` (
                `id_wk_wab_customer` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `id_shop` int(10) UNSIGNED NOT NULL,
                `id_customer` int(10) UNSIGNED NOT NULL,
                `call_prefix` int(10) NOT NULL,
                `mobile` varchar(20) NOT NULL,
                `is_verified` tinyint(4) NOT NULL DEFAULT '0',
                `otp` int DEFAULT NULL,
                `otp_validity` varchar(15) NULL DEFAULT NULL,
                `active` tinyint(4) NOT NULL,
                `date_add` datetime NOT NULL,
                `date_upd` datetime NOT NULL,
                PRIMARY KEY (`id_wk_wab_customer`)
            ) ENGINE=" . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;',
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . "wk_wab_campaign` (
                `id_wk_wab_campaign` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `id_shop` INT(11) UNSIGNED NOT NULL,
                `campaign_name` VARCHAR(255) NOT NULL,
                `header_type` ENUM('text','media') NULL DEFAULT NULL,
                `header_media_url` VARCHAR(255) NULL,
                `button_status` INT(11) NOT NULL,
                `button_type` ENUM('call_to_action','quick_reply') NULL DEFAULT NULL,
                `button_action_type` ENUM('call','visit_website') NULL DEFAULT NULL,
                `coutry_code` VARCHAR(30) NULL,
                `phone` VARCHAR(50) NULL,
                `url_type` ENUM('static','dynamic') NULL,
                `url` VARCHAR(255) NULL,
                `categories` TEXT NULL,
                `customers` TEXT NULL,
                `status` INT(10) NOT NULL DEFAULT 0,
                `expiry_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `date_add` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `date_upd` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id_wk_wab_campaign`)
            ) ENGINE=" . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;',
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'wk_wab_campaign_lang` (
                `id_wk_wab_campaign` int(10) unsigned NOT NULL,
                `id_lang` int(10) unsigned NOT NULL,
                `campaign_header` VARCHAR(60) NULL,
                `campaign_description` TEXT NOT NULL,
                `campaign_footer` VARCHAR(60) NULL,
                `call_button_text` VARCHAR(25) NULL,
                `visit_button_text` VARCHAR(25) NULL,
                `first_quick_reply_text` VARCHAR(25) NULL,
                `second_quick_reply_text` VARCHAR(25) NULL,
                `third_quick_reply_text` VARCHAR(25) NULL,
                PRIMARY KEY (`id_wk_wab_campaign`,`id_lang`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8',
        ];
    }

    /**
     * Delete module tables when uninstalled
     *
     * @return bool
     */
    public function deleteTables()
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->execute(
            'DROP TABLE IF EXISTS
            `' . _DB_PREFIX_ . 'wk_wab_customer`,
            `' . _DB_PREFIX_ . 'wk_wab_campaign`,
            `' . _DB_PREFIX_ . 'wk_wab_campaign_lang`'
        );
    }
}
