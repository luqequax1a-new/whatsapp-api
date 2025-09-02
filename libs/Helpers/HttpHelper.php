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
class HttpHelper
{
    public $curl;
    public $headers = [];

    public function __construct()
    {
        $this->initCurl();
    }

    public function __destruct()
    {
        curl_close($this->curl);
    }

    private function initCurl()
    {
        if (!function_exists('curl_version')) {
            trigger_error('Curl not available', E_USER_ERROR);
        } else {
            $this->curl = curl_init();
            $this->setDefaults();
        }
    }

    private function setDefaults()
    {
        curl_setopt($this->curl, CURLOPT_VERBOSE, 1);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($this->curl, CURLOPT_SSLVERSION, 'CURL_SSLVERSION_TLSv1_2');
        curl_setopt($this->curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($this->curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($this->curl, CURLOPT_HEADER, 1);
        curl_setopt($this->curl, CURLINFO_HEADER_OUT, 1);
    }

    private function setHeaders()
    {
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $this->headers);
    }

    private function sendCurlRequest()
    {
        $this->setHeaders();
        $result = curl_exec($this->curl);
        // dump($result); // here for checking api response
        // die;
        if (curl_errno($this->curl)) {
            trigger_error('Request Error:' . curl_error($this->curl), E_USER_WARNING);
        }
        $headerSize = curl_getinfo($this->curl, CURLINFO_HEADER_SIZE);
        $body = substr($result, $headerSize);

        return json_decode($body, true);
    }

    public function resetHelper()
    {
        $this->curl = null;
        $this->initCurl();
        $this->headers = [];
    }

    public function setUrl($url)
    {
        curl_setopt($this->curl, CURLOPT_URL, $url);
    }

    public function setBody($postData)
    {
        if (is_array($postData)) {
            $postData = json_encode($postData);
        }
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($this->curl, CURLOPT_POST, true);
        $this->setRequestType('POST');
    }

    public function setRequestType($type)
    {
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $type);
    }

    public function setAuthentication($authData)
    {
        curl_setopt($this->curl, CURLOPT_USERPWD, $authData);
    }

    public function addHeader($header)
    {
        $this->headers[] = $header;
    }

    public function sendRequest()
    {
        return $this->sendCurlRequest();
    }
}
