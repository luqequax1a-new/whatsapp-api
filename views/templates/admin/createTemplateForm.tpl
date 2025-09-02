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

{* <pre>
{$isTplExists}
{$messages|print_r}
</pre> *}
<div id="wk-template-block">
    <ul class="nav nav-tabs" id="wk-checkout-config">
        <li class="nav-item active">
            <a class="nav-link" href="#tab-wk-order-conf" data-toggle="tab">
                {l s='Order confirmation' mod='wkwhatsappbusiness'}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#tab-order-status-update" data-toggle="tab">
                {l s='Order status update' mod='wkwhatsappbusiness'}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#tab-order-tracking" data-toggle="tab">
                {l s='Tracking number' mod='wkwhatsappbusiness'}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#tab-wkotp" data-toggle="tab">
                {l s='OTP' mod='wkwhatsappbusiness'}
            </a>
        </li>
    </ul>
    <div id="wk_config_details" class="tab-content panel collapse in">
        <div class="tab-pane active" id="tab-wk-order-conf">
            {include file="$self/views/templates/admin/_partials/tab-wk-order-conf.tpl"}
        </div>
        <div class="tab-pane" id="tab-order-status-update">
            {include file="$self/views/templates/admin/_partials/tab-order-status-update.tpl"}
        </div>
        <div class="tab-pane" id="tab-order-tracking">
            {include file="$self/views/templates/admin/_partials/tab-order-tracking.tpl"}
        </div>
        <div class="tab-pane" id="tab-wkotp">
            {include file="$self/views/templates/admin/_partials/tab-verify-otp.tpl"}
        </div>
    </div>
</div>
