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
include_once dirname(__FILE__) . '/HttpHelper.php';

class WkWhatsAppHelper
{
    protected $http;
    protected $apiUrl;
    protected $token;
    protected $apiVersion;
    protected $whatsAppId;

    public const API_URL = 'https://graph.facebook.com';
    public const API_VERSION = 'v17.0';

    public function __construct()
    {
        $this->http = new HttpHelper();
        $this->apiUrl = self::API_URL;
        $this->setAPIVersion(self::API_VERSION);
        $this->token = trim(Configuration::get('WK_WAB_TOKEN'));
    }

    protected function setDefaultHeaders()
    {
        $this->http->addHeader('Content-Type: application/json');
        $this->http->addHeader('Authorization: Bearer ' . $this->token);
    }

    protected function setAPIVersion($version)
    {
        $this->apiVersion = $version;
    }

    protected function createApiUrl($resource)
    {
        return $this->apiUrl . '/' . $this->apiVersion . '/' . $this->whatsAppId . '/' . $resource;
    }

    protected function setWhatsAppId($whatsAppId)
    {
        $this->whatsAppId = trim($whatsAppId);
    }

    protected function respond($data)
    {
        $response = [];
        if ($data) {
            if (array_key_exists('error', $data)) {
                $response = [
                    'success' => false,
                    'response' => $data,
                ];
            } else {
                $response = [
                    'success' => true,
                    'response' => $data,
                ];
            }
        }

        return $response;
    }
}
