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

<div class="wk-form-wrapper">
    {if isset($messages['verify_otp']['status']) && $messages['verify_otp']['status']}
        <div class="form-group">
            {foreach from=$messages['verify_otp']['status'] item=status}
            <div class="col-lg-4">
                    {assign var="alertClassName" value='alert-danger'}
                    {if $status['status'] == 'APPROVED'}
                        {$alertClassName = 'alert-success'}
                    {elseif $status['status'] == 'PENDING'}
                        {$alertClassName = 'alert-warning'}
                    {/if}
                    <div class="alert {$alertClassName}">
                        <p><strong>{l s='ID' mod='wkwhatsappbusiness'}</strong>: {$status['id']}</p>
                        <p><strong>{l s='Status' mod='wkwhatsappbusiness'}</strong>: {$status['status']}</p>
                        <p><strong>{l s='Language' mod='wkwhatsappbusiness'}</strong>: {$status['lang']}</p>
                    </div>
            </div>
            {/foreach}
        </div>
    {/if}
    <div class="form-group">
        <label class="control-label col-lg-3 required">
            <span title="" data-html="true" data-toggle="tooltip" class="label-tooltip" data-original-title="{l s='Enter the copy OTP button text.' mod='wkwhatsappbusiness'}">{l s='Copy button text' mod='wkwhatsappbusiness'}</span>
        </label>
        <div class="col-lg-8">
            <div class="form-group">
                {foreach from=$languages item=lang}
                    <div class="translatable-field lang-{$lang.id_lang}" style="{if !$lang.is_default}display:none;{/if}">
                        <div class="{if count($languages) > 1}col-lg-9{else}col-md-11{/if}">
                            <input type="text" id="verify_otp_btn_txt_{$lang.id_lang}" name="verify_otp_btn_txt_{$lang.id_lang}" class="" value="{if isset($messages['verify_otp']['btn_text'][{$lang.id_lang}])}{$messages['verify_otp']['btn_text'][{$lang.id_lang}]}{/if}" onkeyup="if (isArrowKey(event)) return ;updateFriendlyURL();" maxlength="25" {if isset($messages['verify_otp']['status'][{$lang.id_lang}]['id']) && ($messages['verify_otp']['status'][{$lang.id_lang}]['id'])}disabled{/if}>
                        </div>
                        {if count($languages) > 1}
                            <div class="col-lg-2">
                                <button type="button" class="btn btn-default dropdown-toggle" tabindex="-1" data-toggle="dropdown">
                                {$lang.iso_code}
                                <i class="icon-caret-down"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    {foreach from=$languages item=langDrop}
                                        <li>
                                            <a href="javascript:hideOtherLanguage({$langDrop.id_lang});" tabindex="-1">{$langDrop.name}</a>
                                        </li>
                                    {/foreach}
                                </ul>
                            </div>
                        {/if}
                    </div>
                {/foreach}
            </div>
            <p class="help-block">
                {l s='Maximum 25 characters allowed.' mod='wkwhatsappbusiness'}
            </p>
        </div>
    </div>
</div>
