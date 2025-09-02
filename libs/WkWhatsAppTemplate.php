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
include_once dirname(__FILE__) . '/Helpers/WkWhatsAppHelper.php';

class WkWhatsAppTemplate extends WkWhatsAppHelper
{
    protected $whatsAppId;

    public function __construct()
    {
        $this->whatsAppId = trim(Configuration::get('WK_WAB_ACCOUNT_ID'));
        parent::__construct();
    }

    public function createWhatsAppTemplate($tplArr)
    {
        return $this->createTemplate($tplArr);
    }

    private function createTemplate($tplArr)
    {
        $this->http->resetHelper();
        $this->setDefaultHeaders();
        $this->setWhatsAppId($this->whatsAppId);
        $this->http->setUrl($this->createApiUrl('message_templates'));
        $this->http->setBody($tplArr);

        return $this->respond($this->http->sendRequest());
    }

    public function getWhatsAppTemplates($tplName)
    {
        return $this->getTemplates($tplName);
    }

    private function getTemplates($tplName)
    {
        $this->http->resetHelper();
        $this->setDefaultHeaders();
        $this->setWhatsAppId($this->whatsAppId);
        $this->http->setUrl($this->createApiUrl('message_templates?name=' . $tplName));

        return $this->respond($this->http->sendRequest());
    }

    public function getGraphData($dateFrom = null, $dateTo = null, $granularity = 'DAY')
    {
        return $this->getAPIGraphData($dateFrom, $dateTo, $granularity);
    }

    private function getAPIGraphData($dateFrom = null, $dateTo = null, $granularity = 'DAY')
    {
        $getParams = '.phone_numbers([]).country_codes([])';
        $dateFromTimeStamp = strtotime(date('Y-m-01'));
        $dateToTimeStamp = strtotime(date('Y-m-t'));
        if ($dateFrom !== null) {
            $dateFromTimeStamp = strtotime($dateFrom);
        }
        if ($dateTo !== null) {
            $dateToTimeStamp = strtotime($dateTo);
        }
        $getParams .= '.start(' . $dateFromTimeStamp . ').end(' . $dateToTimeStamp . ').granularity(' . $granularity . ')';
        $this->http->resetHelper();
        $this->setDefaultHeaders();
        $this->setWhatsAppId($this->whatsAppId);
        $this->http->setUrl($this->createApiUrl('?fields=analytics' . $getParams));

        return $this->respond($this->http->sendRequest());
    }
}
