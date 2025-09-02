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
 * AdminWABConfigController class
 *
 * This class redirect admin to the module configuration page
 */
class AdminWABConfigController extends ModuleAdminController
{
    public function initContent()
    {
        parent::initContent();
        $params = ['configure' => $this->module->name, 'page' => 'wkapi'];
        $moduleAdminLink = Context::getContext()->link->getAdminLink('AdminModules', true, false, $params);
        Tools::redirectAdmin($moduleAdminLink);
    }
}
