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
 * AdminWABAnalyticsController class
 *
 * This class displayed the analytics about messages (sent & delivered) on graph
 */
class AdminWABAnalyticsController extends ModuleAdminController
{
    /**
     * Constructer
     */
    public function __construct()
    {
        $this->context = Context::getContext();
        $this->bootstrap = true;
        $this->display = 'view';

        parent::__construct();

        $this->toolbar_title = $this->l('Message analytics');

        $this->defineJSVars();
    }

    public function initContent()
    {
        if (!WkWABHelper::isModuleConfigured()) {
            $this->warnings[] = $this->l('Please configure this module first.');

            return false;
        }
        parent::initContent();
    }

    public function renderView()
    {
        $preselectDateRange = 2;
        $dateFrom = date('Y-m-01');
        $dateTo = date('Y-m-t');

        $totalBakers = 5;
        $totalFundedProject = 100;
        $totalFundedProjectLive = 90;
        $adminCurrency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));

        $this->tpl_view_vars = [
            'signDefaultCurr' => Currency::getDefaultCurrency()->sign,
            'totalBakers' => $totalBakers,
            'totalFundedProject' => Tools::displayPrice($totalFundedProject, $adminCurrency),
            'totalFundedProjectLive' => Tools::displayPrice($totalFundedProjectLive, $adminCurrency),
            // Graph Keys
            'preselectDateRange' => $preselectDateRange,
            'userFriendlyDateFrom' => date('d-m-Y', strtotime($dateFrom)),
            'userFriendlyDateTo' => date('d-m-Y', strtotime($dateTo)),
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'salesData' => [],
        ];

        return parent::renderView();
    }

    public function defineJSVars()
    {
        $dateFrom = date('Y-m-01');
        $dateTo = date('Y-m-t');
        $jsVars = [
            'userFriendlyDateTo' => date('d-m-Y', strtotime($dateTo)),
            'userFriendlyDateFrom' => date('d-m-Y', strtotime($dateFrom)),
            'currentDate' => date('d/m/Y'),
            'wabAjaxUrl' => $this->context->link->getAdminLink('AdminWABAnalytics'),
            'applyLabel' => $this->l('Apply'),
            'cancelLabel' => $this->l('Cancel'),
            'fromLabel' => $this->l('From'),
            'toLabel' => $this->l('To'),
            'customRangeLabel' => $this->l('Custom'),
            'January' => $this->l('January'),
            'February' => $this->l('February'),
            'March' => $this->l('March'),
            'April' => $this->l('April'),
            'May' => $this->l('May'),
            'June' => $this->l('June'),
            'July' => $this->l('July'),
            'August' => $this->l('August'),
            'September' => $this->l('September'),
            'October' => $this->l('October'),
            'November' => $this->l('November'),
            'December' => $this->l('December'),
            'Su' => $this->l('Su'),
            'Mo' => $this->l('Mo'),
            'Tu' => $this->l('Tu'),
            'We' => $this->l('We'),
            'Th' => $this->l('Th'),
            'Fr' => $this->l('Fr'),
            'Sa' => $this->l('Sa'),
            'wkWabConfigured' => WkWABHelper::isModuleConfigured(),
        ];

        if (Configuration::get('PS_REWRITING_SETTINGS')) {
            $jsVars['friendly_url'] = 1;
        } else {
            $jsVars['friendly_url'] = 0;
        }
        Media::addJsDef($jsVars);
    }

    public function displayAjaxRefreshDashboard()
    {
        $dateFrom = date('Y-m-01');
        $dateTo = date('Y-m-t');
        $preselectDateRange = Tools::getValue('preselectDateRange');
        if (Tools::getValue('dateFrom')) {
            $dateFrom = Tools::getValue('dateFrom');
        }
        if (Tools::getValue('dateTo')) {
            $dateTo = Tools::getValue('dateTo');
        }
        $granularity = 'DAY';
        if ($preselectDateRange == 1) {
            $granularity = 'HALF_HOUR';
        } elseif ($preselectDateRange == 2) {
            $granularity = 'DAY';
        } elseif ($preselectDateRange == 3) {
            $granularity = 'MONTH';
        }

        $params = [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'granularity' => $granularity,
        ];
        exit(json_encode(['dashtrends' => $this->getWABAnalytics($params)]));
    }

    // Functions used for graph implementation
    private function getWABAnalytics($params)
    {
        // Retrieve data from API
        $tmpData = $this->getGraghData($params);
        $graphData = [];
        if ($tmpData) {
            foreach ($tmpData as $tmp) {
                $graphData['sent'][$tmp['start']] = $tmp['sent'];
                $graphData['delivered'][$tmp['start']] = $tmp['delivered'];
            }
        }
        $this->wabGraphData = $graphData;
        $this->wabGraphDataSum = $this->addupData($this->wabGraphData);

        return [
            'data_value' => [
                'sent_score' => $this->wabGraphDataSum['sent'],
                'delivered_score' => $this->wabGraphDataSum['delivered'],
            ],
            'data_chart' => ['dash_trends_chart1' => $this->getChartTrends()],
        ];
    }

    public function getChartTrends()
    {
        $chartData = [];
        $chartDataCompare = [];
        foreach (array_keys($this->wabGraphData) as $chartKey) {
            $chartData[$chartKey] = $chartDataCompare[$chartKey] = [];

            if (!count($this->wabGraphData[$chartKey])) {
                continue;
            }

            foreach ($this->wabGraphData[$chartKey] as $key => $value) {
                $chartData[$chartKey][] = [$key, $value];
            }
        }

        $charts = [
            'sent' => $this->l('Sent messages'),
            'delivered' => $this->l('Delivered messages'),
        ];

        $gfxColor = ['#1777b6', '#72c279', '#6b399c', '#00A347', '#887E4E'];

        $i = 0;
        $data = [
            'chart_type' => 'line_chart_trends',
            'date_format' => $this->context->language->date_format_lite,
        ];

        foreach ($charts as $key => $title) {
            $data['data'][] = [
                'id' => $key,
                'key' => $title,
                'color' => $gfxColor[$i],
                'values' => $chartData[$key],
                'disabled' => false,
            ];
            ++$i;
        }

        return $data;
    }

    protected function addupData($data)
    {
        $summing = [
            'sent' => array_sum($data['sent']),
            'delivered' => array_sum($data['delivered']),
        ];

        return $summing;
    }

    // Function used for graph implementation
    protected function getGraghData($params)
    {
        $objTpl = new WkWhatsAppTemplate();
        $response = $objTpl->getGraphData($params['dateFrom'], $params['dateTo'], $params['granularity']);
        if ($response && $response['success']) {
            if (isset($response['response']['analytics']['data_points'])) {
                return $response['response']['analytics']['data_points'];
            }
            exit(json_encode([
                'success' => false,
                'response' => [
                    'error' => [
                        'message' => $this->l('No data found for the selected date range.'),
                    ],
                ],
            ]));
        }
        exit(json_encode($response));
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        // CSS files
        $this->context->controller->addCSS(
            _MODULE_DIR_ . $this->module->name . '/views/css/admin/graph/analytics.css'
        );
        $this->context->controller->addCSS(
            _MODULE_DIR_ . $this->module->name . '/views/css/admin/graph/nv.d3.css'
        );

        // JS files
        $this->context->controller->addJS(
            _MODULE_DIR_ . $this->module->name . '/views/js/admin/graph/analytics.js'
        );
        $this->context->controller->addJS(
            _MODULE_DIR_ . $this->module->name . '/views/js/admin/graph/d3.v3.min.js'
        );
        $this->context->controller->addJS(
            _MODULE_DIR_ . $this->module->name . '/views/js/admin/graph/nv.d3.min.js'
        );
        // Include Date Range Picker
        $this->addJS('//cdn.jsdelivr.net/momentjs/latest/moment.min.js');
        $this->addJS('//cdn.jsdelivr.net/npm/bootstrap-daterangepicker@3.0.3/daterangepicker.min.js');
        $this->addCSS('//cdn.jsdelivr.net/npm/bootstrap-daterangepicker@3.0.3/daterangepicker.css');
    }
}
