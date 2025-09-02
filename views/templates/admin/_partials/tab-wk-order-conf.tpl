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
    {if isset($messages['order_conf']['status']) && $messages['order_conf']['status']}
        <div class="form-group">
            {foreach from=$messages['order_conf']['status'] item=status}
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
            <span title="" data-html="true" data-toggle="tooltip" class="label-tooltip" data-original-title="{l s='Enter the header message for order confirmation. (ie: Order confirmed!)' mod='wkwhatsappbusiness'}">{l s='Header' mod='wkwhatsappbusiness'}</span>
        </label>
        <div class="col-lg-8">
            <div class="form-group">
                {foreach from=$languages item=lang}
                    <div class="translatable-field lang-{$lang.id_lang}" style="{if !$lang.is_default}display:none;{/if}">
                        <div class="{if count($languages) > 1}col-lg-9{else}col-md-11{/if}">
                            <input type="text" id="create_order_conf_header_{$lang.id_lang}" name="create_order_conf_header_{$lang.id_lang}" class="" value="{if isset($messages['order_conf']['header'][{$lang.id_lang}])}{$messages['order_conf']['header'][{$lang.id_lang}]}{/if}" onkeyup="if (isArrowKey(event)) return ;updateFriendlyURL();" maxlength="60" {if isset($messages['order_conf']['status'][{$lang.id_lang}]['id']) && ($messages['order_conf']['status'][{$lang.id_lang}]['id'])}disabled{/if}>
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
                {l s='Maximum 60 characters allowed.' mod='wkwhatsappbusiness'}
            </p>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-lg-3 required">
            <span title="" data-html="true" data-toggle="tooltip" class="label-tooltip" data-original-title="{l s='Enter the body message for order confirmation.' mod='wkwhatsappbusiness'}">{l s='Body' mod='wkwhatsappbusiness'}</span>
        </label>
        <div class="col-lg-8">
            <div class="form-group">
                {foreach from=$languages item=lang}
                    <div class="translatable-field lang-{$lang.id_lang}" style="{if !$lang.is_default}display:none;{/if}">
                        <div class="{if count($languages) > 1}col-lg-9{else}col-md-11{/if}">
                            <textarea id="create_order_conf_body_{$lang.id_lang}" name="create_order_conf_body_{$lang.id_lang}" class="form-control" rows="5" onkeyup="if (isArrowKey(event)) return ;updateFriendlyURL();" maxlength="1024" {if isset($messages['order_conf']['status'][{$lang.id_lang}]['id']) && ($messages['order_conf']['status'][{$lang.id_lang}]['id'])}disabled{/if}>{if isset($messages['order_conf']['body'][{$lang.id_lang}])}{$messages['order_conf']['body'][{$lang.id_lang}]}{/if}</textarea>
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
                {l s='Maximum 1024 characters allowed.' mod='wkwhatsappbusiness'} <br>
                {l s='{{1}} = Name of the customer' mod='wkwhatsappbusiness'} <br>
                {l s='{{2}} = Order reference' mod='wkwhatsappbusiness'} <br>
                {l s='{{3}} = Order total amount' mod='wkwhatsappbusiness'}
            </p>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-lg-3 required">
            <span title="" data-html="true" data-toggle="tooltip" class="label-tooltip" data-original-title="{l s='Enter the footer message for order confirmation. (ie: Thanks for shopping with us!)' mod='wkwhatsappbusiness'}">{l s='Footer' mod='wkwhatsappbusiness'}</span>
        </label>
        <div class="col-lg-8">
            <div class="form-group">
                {foreach from=$languages item=lang}
                    <div class="translatable-field lang-{$lang.id_lang}" style="{if !$lang.is_default}display:none;{/if}">
                        <div class="{if count($languages) > 1}col-lg-9{else}col-md-11{/if}">
                            <input type="text" id="create_order_conf_footer_{$lang.id_lang}" name="create_order_conf_footer_{$lang.id_lang}" class="" value="{if isset($messages['order_conf']['footer'][{$lang.id_lang}])}{$messages['order_conf']['footer'][{$lang.id_lang}]}{/if}" onkeyup="if (isArrowKey(event)) return ;updateFriendlyURL();" maxlength="60" {if isset($messages['order_conf']['status'][{$lang.id_lang}]['id']) && ($messages['order_conf']['status'][{$lang.id_lang}]['id'])}disabled{/if}>
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
                {l s='Maximum 60 characters allowed.' mod='wkwhatsappbusiness'}
            </p>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-lg-3 required">
            <span title="" data-html="true" data-toggle="tooltip" class="label-tooltip" data-original-title="{l s='Enter the button text to view order. (ie: View order!)' mod='wkwhatsappbusiness'}">{l s='Button text' mod='wkwhatsappbusiness'}</span>
        </label>
        <div class="col-lg-8">
            <div class="form-group">
                {foreach from=$languages item=lang}
                    <div class="translatable-field lang-{$lang.id_lang}" style="{if !$lang.is_default}display:none;{/if}">
                        <div class="{if count($languages) > 1}col-lg-9{else}col-md-11{/if}">
                            <input type="text" id="create_order_conf_btn_txt_{$lang.id_lang}" name="create_order_conf_btn_txt_{$lang.id_lang}" class="" value="{if isset($messages['order_conf']['btn_text'][{$lang.id_lang}])}{$messages['order_conf']['btn_text'][{$lang.id_lang}]}{/if}" onkeyup="if (isArrowKey(event)) return ;updateFriendlyURL();" maxlength="25" {if isset($messages['order_conf']['status'][{$lang.id_lang}]['id']) && ($messages['order_conf']['status'][{$lang.id_lang}]['id'])}disabled{/if}>
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
