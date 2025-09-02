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

<p>
{l s='Create message templates as per your wish. Click on the "Draft" button to save it locally or click on "Send To WhatsApp" button to upload drafted campaign template on WhatsApp Buisness, you will not be able to modify these template messages until it is rejected.' mod='wkwhatsappbusiness'}
</p>
<p>
    <strong>{l s='Note : ' mod='wkwhatsappbusiness'}</strong>{l s='All shown variable respected to field must be defined.' mod='wkwhatsappbusiness'}
</p>
<p>
    <strong>{l s='You can change the style of the template body message text as per given format:' mod='wkwhatsappbusiness'}</strong>
</p>
<ul>
    <li>{l s='*Bold text*' mod='wkwhatsappbusiness'} = <strong>{l s='Bold text' mod='wkwhatsappbusiness'}</strong></li>
    <li>{l s='_Italic text_' mod='wkwhatsappbusiness'} = <em>{l s='Italic text' mod='wkwhatsappbusiness'}</em></li>
    <li>{l s='~Strike through~' mod='wkwhatsappbusiness'} = <del>{l s='Strike through' mod='wkwhatsappbusiness'}</del></li>
    <li>{l s='```Code```' mod='wkwhatsappbusiness'} = <code>{l s='Code' mod='wkwhatsappbusiness'}</code></li>
</ul>
<br>
<div class="clearfix"></div>
{if isset($templateInfo)}
    {if isset($templateInfo['success'])}
        {if $templateInfo['success'] == true}
            {if isset($templateInfo['response']) && isset($templateInfo['response']['data'])}
                {foreach from=$templateInfo['response']['data'] item=status}
                    <div class="col-lg-3">
                        {assign var="alertClassName" value='alert-danger'}
                        {if $status['status'] == 'APPROVED'}
                            {$alertClassName = 'alert-success'}
                        {elseif $status['status'] == 'PENDING'}
                            {$alertClassName = 'alert-warning'}
                        {elseif $status['status'] == 'REJECTED'}
                            {$alertClassName = 'alert-danger'}
                        {/if}
                        <div class="alert {$alertClassName}">
                            <p><strong>{l s='ID' mod='wkwhatsappbusiness'}</strong>: {$status['id']}</p>
                            <p><strong>{l s='Status' mod='wkwhatsappbusiness'}</strong>: {$status['status']}</p>
                            <p><strong>{l s='Language' mod='wkwhatsappbusiness'}</strong>: {$status['language']}</p>
                        </div>
                    </div>
                {/foreach}
            {/if}
            {if isset($templateInfo['response'])
            && isset($templateInfo['response']['data'])
            && empty($templateInfo['response']['data'])}
                <div class="col-lg-12">
                    <div class="alert alert-danger">
                        <p>{l s='Template is still not uploaded to WhatsApp Business, please upload first.' mod='wkwhatsappbusiness'}</p>
                    </div>
                </div>
            {/if}
        {/if}
    {/if}
{/if}
<div class="clearfix"></div>
