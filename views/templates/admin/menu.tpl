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

<div id="wkwhatsappbusiness-modulecontent" class="clearfix">
    <div id="wkwhatsappbusiness-menu">
        <div class="col-lg-2">
            <div class="list-group" v-on:click.prevent>
                <a href="#" class="list-group-item" v-bind:class="{ 'active': isActive('wkapi') }" v-on:click="makeActive('wkapi')"><i class="icon-cogs"></i> {l s='API' mod='wkwhatsappbusiness'}</a>

                <a href="#" class="list-group-item" v-bind:class="{ 'active': isActive('wktemplate') }" v-on:click="makeActive('wktemplate')"><i class="icon-file"></i> {l s='Templates' mod='wkwhatsappbusiness'}</a>

                <a href="#" class="list-group-item" v-bind:class="{ 'active': isActive('wknotification') }" v-on:click="makeActive('wknotification')"><i class="icon-whatsapp"></i> {l s='Notification' mod='wkwhatsappbusiness'}</a>

                <a href="#" class="list-group-item" v-bind:class="{ 'active': isActive('wkwebhook') }" v-on:click="makeActive('wkwebhook')"><i class="icon-retweet"></i> {l s='Webhook' mod='wkwhatsappbusiness'}</a>
            </div>
            <div class="list-group">
                <a class="list-group-item" style="text-align:center"><i class="icon-puzzle-piece"></i> {l s='Module V' mod='wkwhatsappbusiness'} {$module_version}</a>
                <a href="https://addons.prestashop.com/en/204_webkul"  target="_blank" class="list-group-item" style="color: #00aff0; text-align:center" title="{l s='Search our more developed modules' mod='wkwhatsappbusiness'}"><i class='icon-external-link-sign'></i> {l s='More Addons' mod='wkwhatsappbusiness'}</a>
            </div>
        </div>
    </div>

    <div id="wkapi" class="wkwhatsappbusiness_menu wk-hide">
        {include file="./_partials/tabs/wkapi.tpl"}
    </div>

    <div id="wktemplate" class="wkwhatsappbusiness_menu wk-hide">
        {include file="./_partials/tabs/wktemplate.tpl"}
    </div>

    <div id="wknotification" class="wkwhatsappbusiness_menu wk-hide">
        {include file="./_partials/tabs/wknotification.tpl"}
    </div>

    <div id="wkwebhook" class="wkwhatsappbusiness_menu wk-hide">
        {include file="./_partials/tabs/wkwebhook.tpl"}
    </div>
</div>

{literal}
<script type="text/javascript">
    var base_url = "{/literal}{$ps_base_dir}{literal}";
    var moduleName = "{/literal}{$module_name}{literal}";
    var currentPage = "{/literal}{$currentPage}{literal}";
    var moduleAdminLink = "{/literal}{$moduleAdminLink}{literal}";
</script>
{/literal}
