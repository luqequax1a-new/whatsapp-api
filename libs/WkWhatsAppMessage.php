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
include_once dirname(__FILE__) . '/WkWhatsAppTemplate.php';
include_once dirname(__FILE__) . '/../classes/WkWABHelper.php';

class WkWhatsAppMessage extends WkWhatsAppHelper
{
    protected $whatsAppId;
    protected $orederMessagePaylaod;
    protected $otpMessagePaylaod;

    public function __construct()
    {
        $this->whatsAppId = trim(Configuration::get('WK_WAB_PHONE_NUMBER_ID'));
        parent::__construct();
    }

    public function sendSimpleTxtMessage($mobileNumber, $textMsg)
    {
        WkWABHelper::logMsg('--Sending simple txt msg--', true);
        $payload = [
            'messaging_product' => 'whatsapp',
            'preview_url' => false,
            'recipient_type' => 'individual',
            'to' => $mobileNumber,
            'type' => 'text',
            'text' => [
                'body' => $textMsg,
            ],
        ];
        WkWABHelper::logMsg(json_encode($payload));

        return $this->sendSimpleTxtMsg($payload);
    }

    private function sendSimpleTxtMsg($payload)
    {
        $this->http->resetHelper();
        $this->setDefaultHeaders();
        $this->setWhatsAppId($this->whatsAppId);
        $this->http->setUrl($this->createApiUrl('messages'));
        $this->http->setBody($payload);
        $response = $this->respond($this->http->sendRequest());
        WkWABHelper::logMsg('Response:');
        WkWABHelper::logMsg(json_encode($response));

        return $response;
    }

    public function sendOtpCode($mobileNumber, $otpcode, $template, $idLang)
    {
        if ($isoCode = $this->checkIfTemplateExists($template, $idLang)) {
            $this->prepareOtpTemplateData($mobileNumber, $otpcode, $template, $isoCode);

            return $this->sendOtpNotification();
        } else {
            WkWABHelper::logMsg('--Sending notification--', true);
            WkWABHelper::logMsg('Request:');
            WkWABHelper::logMsg(json_encode([
                'mobileNumber' => $mobileNumber,
                'template' => $template,
                'idLang' => $idLang,
            ]));
            WkWABHelper::logMsg('Response: Template not exists or approved.');
        }
    }

    public function prepareOtpTemplateData($mobileNumber, $otpcode, $template, $isoCode)
    {
        $messagePaylaod = [];
        $messagePaylaod['messaging_product'] = 'whatsapp';
        $messagePaylaod['to'] = $mobileNumber;
        $messagePaylaod['type'] = 'template';
        $messagePaylaod['template']['name'] = $template;
        $messagePaylaod['template']['language']['code'] = $isoCode;
        if ($otpcode) {
            $components = [];
            $components['type'] = 'body';
            $components['parameters'] = [
                [
                    'type' => 'text',
                    'text' => $otpcode,
                ],
            ];
            $messagePaylaod['template']['components'][] = $components;
            $messagePaylaod['template']['components'][] = [
                'type' => 'button',
                'sub_type' => 'url',
                'index' => 0,
                'parameters' => [
                    [
                        'type' => 'text',
                        'text' => $otpcode,
                    ],
                ],
            ];
        }
        $this->otpMessagePaylaod = $messagePaylaod;
        WkWABHelper::logMsg('--Sending notification--', true);
        WkWABHelper::logMsg('Payload:');
        WkWABHelper::logMsg(json_encode($messagePaylaod));
    }

    public function sendOtpNotification()
    {
        $this->http->resetHelper();
        $this->setDefaultHeaders();
        $this->setWhatsAppId($this->whatsAppId);
        $this->http->setUrl($this->createApiUrl('messages'));
        $this->http->setBody($this->otpMessagePaylaod);
        $response = $this->respond($this->http->sendRequest());
        WkWABHelper::logMsg('Response:');
        WkWABHelper::logMsg(json_encode($response));

        return $response;
    }

    public function sendOrderMessage($mobileNumber, $template, $idLang, $templateData = [])
    {
        if ($isoCode = $this->checkIfTemplateExists($template, $idLang)) {
            $this->prepareTemplateData($mobileNumber, $template, $isoCode, $templateData);

            return $this->sendOrderWhatsAppNotification();
        } else {
            WkWABHelper::logMsg('--Sending notification--', true);
            WkWABHelper::logMsg('Request:');
            WkWABHelper::logMsg(json_encode([
                'mobileNumber' => $mobileNumber,
                'template' => $template,
                'idLang' => $idLang,
                'templateData' => $templateData,
            ]));
            WkWABHelper::logMsg('Response: Template not exists or approved.');
        }
    }

    private function sendOrderWhatsAppNotification()
    {
        $this->http->resetHelper();
        $this->setDefaultHeaders();
        $this->setWhatsAppId($this->whatsAppId);
        $this->http->setUrl($this->createApiUrl('messages'));
        $this->http->setBody($this->orederMessagePaylaod);
        $response = $this->respond($this->http->sendRequest());
        WkWABHelper::logMsg('Response:');
        WkWABHelper::logMsg(json_encode($response));

        return $response;
    }

    private function checkIfTemplateExists($template, $idReqLang)
    {
        $objWABTpl = new WkWhatsAppTemplate();
        $installedLangs = WkWABHelper::getInstalledLangIsoCodes();
        $templateInfo = $objWABTpl->getWhatsAppTemplates($template);
        $isApproved = false;
        if ($templateInfo['success'] && $templateInfo['response']['data']) {
            foreach ($installedLangs as $idLang => $lang) {
                foreach ($templateInfo['response']['data'] as $value) {
                    if (($lang == $value['language']) && ($idLang == $idReqLang)) {
                        if (strtoupper($value['status']) == 'APPROVED') {
                            $isApproved = $value['language'];
                            break 2;
                        }
                    }
                }
            }
        }

        return $isApproved;
    }

    private function prepareTemplateData($mobileNumber, $template, $isoCode, $templateData = [])
    {
        $messagePaylaod = [];
        $messagePaylaod['messaging_product'] = 'whatsapp';
        $messagePaylaod['to'] = $mobileNumber;
        $messagePaylaod['type'] = 'template';
        $messagePaylaod['template']['name'] = $template;
        $messagePaylaod['template']['language']['code'] = $isoCode;
        if ($templateData) {
            $messagePaylaod['template']['components'][] = $this->getTemplateBodyComponents(
                $templateData
            );
            $messagePaylaod['template']['components'][] = $this->getTemplateButtonComponents(
                $templateData
            );
        }
        $this->orederMessagePaylaod = $messagePaylaod;
        WkWABHelper::logMsg('--Sending notification--', true);
        WkWABHelper::logMsg('Payload:');
        WkWABHelper::logMsg(json_encode($messagePaylaod));
    }

    private function getTemplateBodyComponents($templateData)
    {
        $components = [];
        $components['type'] = 'body';
        $components['parameters'] = [
            [
                'type' => 'text',
                'text' => $templateData['var_1'],
            ],
            [
                'type' => 'text',
                'text' => $templateData['var_2'],
            ],
            [
                'type' => 'text',
                'text' => $templateData['var_3'],
            ],
        ];

        return $components;
    }

    private function getTemplateButtonComponents($templateData)
    {
        $components = [];
        $components['type'] = 'button';
        $components['sub_type'] = 'url';
        $components['index'] = 0;
        $components['parameters'] = [
            [
                'type' => 'text',
                'text' => $templateData['var_4'],
            ],
        ];

        return $components;
    }

    public function sendCampaignMessage($payload)
    {
        $this->http->resetHelper();
        $this->setDefaultHeaders();
        $this->setWhatsAppId($this->whatsAppId);
        $this->http->setUrl($this->createApiUrl('messages'));
        $this->http->setBody($payload);
        $response = $this->respond($this->http->sendRequest());
        WkWABHelper::logMsg('Response:');
        WkWABHelper::logMsg(json_encode($response));

        return $response;
    }
}
