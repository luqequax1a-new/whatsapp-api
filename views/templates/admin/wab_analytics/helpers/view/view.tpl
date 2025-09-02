{*
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
*}

<div class="wab-graph-date-content">
    <div class="row">
        <div class="col-md-7">
            <div class="dropdown">
                <label>{l s='Granularity' mod='wkwhatsappbusiness'}</label>
                <button class="form-control selected_date_range_btn" type="button" data-toggle="dropdown">
                    {if $preselectDateRange == '1'}
                        <span class="selected_range_text">{l s='Half hour' mod='wkwhatsappbusiness'}</span>
                    {elseif $preselectDateRange == '2'}
                        <span class="selected_range_text">{l s='Day' mod='wkwhatsappbusiness'}</span>
                    {elseif $preselectDateRange == '3'}
                        <span class="selected_range_text">{l s='Month' mod='wkwhatsappbusiness'}</span>
                    {/if}
                    <span class="arrow_span">
                        <i class="material-icons">&#xE313;</i>
                    </span>
                </button>
                <ul class="dropdown-menu selectDateRange_ul">
                    <li class="setPreselectDateRange" data-trigger-function="HALF_HOUR" data-date-range="1">
                        {l s='Half hour' mod='wkwhatsappbusiness'}
                    </li>
                    <li class="setPreselectDateRange" data-trigger-function="Day" data-date-range="2">
                        {l s='Day' mod='wkwhatsappbusiness'}
                    </li>
                    <li class="setPreselectDateRange" data-trigger-function="Month" data-date-range="3">
                        {l s='Month' mod='wkwhatsappbusiness'}
                    </li>
                </ul>
            </div>
        </div>
        <div class="col-md-5">
            <label>{l s='Select date range' mod='wkwhatsappbusiness'}</label>
            <input type="hidden" id="dashboardDateFrom" name="dashboardDateFrom" value="{$dateFrom|date_format:"%Y-%m-%d"}">
            <input type="hidden" id="dashboardDateTo" name="dashboardDateTo" value="{$dateTo|date_format:"%Y-%m-%d"}">
            <input type="hidden" name="preselectDateRange" id="preselectDateRange" value="{$preselectDateRange}">
            <div class="input-group">
                <input type="text" class="form-control" id="date-range-picker">
                <span class="input-group-addon"><i class="material-icons">&#xE8A3;</i></span>
            </div>
        </div>
    </div>
</div>
<div class="panel">
    <div class="panel-content">
        <section class="panel wk-graph">
            <input type="hidden" id="dashboardDateFrom" name="dashboardDateFrom" value="{$dateFrom|date_format:"%Y-%m-%d"}">
            <input type="hidden" id="dashboardDateTo" name="dashboardDateTo" value="{$dateTo|date_format:"%Y-%m-%d"}">
            <input type="hidden" name="preselectDateRange" id="preselectDateRange" value="{$preselectDateRange}">
			<div id="dashtrends_toolbar">
                <div class="col-md-6 wk-wab-graph-options wk-wab-graph-options-active" onclick="selectDashtrendsChart(this, 'sent');" style="border-left: none;background-color: #1777b6;color: #fff;">
                    <div class="dash-item">{l s='Sent messages' mod='wkwhatsappbusiness'}</div>
                    <div class="data_value"><span id="sent_score"></span></div>
                    <div class="dash_trend"><span id="sent_score_trends"></span></div>
                </div>
                <div class="wk-wab-graph-options col-md-6" onclick="selectDashtrendsChart(this, 'delivered');">
                    <div class="dash-item">{l s='Delivered messages' mod='wkwhatsappbusiness'}</div>
                    <div class="data_value"><span id="delivered_score"></span></div>
                    <div class="dash_trend"><span id="delivered_score_trends"></span></div>
                </div>
            </div>
            <div class="clearfix"></div>
            <div id="wk-wab-graph-chart">
                <svg></svg>
            </div>
        </section>
    </div>
</div>
