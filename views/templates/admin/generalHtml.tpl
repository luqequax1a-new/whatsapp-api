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

{if isset($wktype)}
    {if $wktype == 'campaignname'}
        <p class="help-block">{l s='Must be less than 500 char.' mod='wkwhatsappbusiness'}</p>
        <p class="help-block">
            {l s='On WhatsApp manager template will be created with' mod='wkwhatsappbusiness'}
            "<span id="wkCreatedCampaignName">campaign_name</span>"
            {l s='name.' mod='wkwhatsappbusiness'}
        </p>
    {/if}
    {if $wktype == 'header'}
        <p class="help-block">{l s='Must be less than 60 char and define at least one variable.' mod='wkwhatsappbusiness'}</p>
        <p class="help-block">{'{{1}}'} {l s='Campaign name will be added in header. ' mod='wkwhatsappbusiness'}</p>
    {/if}
    {if $wktype == 'description'}
        <p class="help-block">{l s='Must be less than 1024 char and all four variables must be used.' mod='wkwhatsappbusiness'}</p>
        <p class="help-block">{'{{1}}'} {l s='Customer name' mod='wkwhatsappbusiness'}</p>
        <p class="help-block">{'{{2}}'} {l s='Selected category' mod='wkwhatsappbusiness'}</p>
        <p class="help-block">{'{{3}}'} {l s='Category link' mod='wkwhatsappbusiness'}</p>
        <p class="help-block">{'{{4}}'} {l s='Store link' mod='wkwhatsappbusiness'}</p>
    {/if}
    {if $wktype == 'url'}
        <p class="help-block">{l s='If dynamic type, put {{1}} after url for variable.' mod='wkwhatsappbusiness'}</p>
        <p class="help-block">{l s='Must be less than 2000 char including spaces.' mod='wkwhatsappbusiness'}</p>
    {/if}
{/if}
