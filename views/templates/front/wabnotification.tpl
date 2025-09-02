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

{extends file=$layout}
{block name='content'}
    <section id="main">
        <div class="card card-block">
            {if isset($wkpage) && ($wkpage == 'verify_otp')}
                <h2 class="subcategory-heading">{l s='WhatsApp number verification' mod='wkwhatsappbusiness'}</h2><hr>
                <div class="col-md-8 offset-md-2">
                    <div class="alert alert-success">
                        {l s='One time password has been sent to on your WhatsApp number' mod='wkwhatsappbusiness'}
                        <strong>{$customerData['call_prefix']}-{$customerData['mobile']}</strong>.
                        {l s='Please enter the code in order to verify your WhatsApp number.' mod='wkwhatsappbusiness'}
                    </div>
                    <div id="wkAlertMessages"></div>
                    <form id="verify-wa-number-form" class="form-horizontal" method="post" action="{$action}">
                        <div class="form-group row">
                            <label class="col-md-4 form-control-label" for="whatsapp_number_otp">
                                {l s='Enter OTP' mod='wkwhatsappbusiness'}
                            </label>
                            <div class="col-md-8">
                                <input class="form-control" type="number" id="whatsapp_number_otp" name="whatsapp_number_otp" placeholder="{l s='Type 6-digit code here' mod='wkwhatsappbusiness'}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-12">
                                <input type="hidden" name="submitVerifyWAnumber" value="1">
                                <input type="hidden" name="token" value="{$token}">
                                <button id="wkBtnVerify" class="btn btn-primary form-control-submit float-xs-right" type="submit">
                                    {l s='Verify' mod='wkwhatsappbusiness'}
                                </button>
                                <button class="btn btn-primary form-control-submit float-xs-right" id="wkBtnResendOtp" type="button" disabled>
                                    {l s='Resend' mod='wkwhatsappbusiness'}
                                    <span id="wkOtpCountDown"></span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            {else}
                <h2 class="subcategory-heading">{l s='WhatsApp notification' mod='wkwhatsappbusiness'}</h2><hr>
                <div class="col-md-9 offset-md-0">
                    {if isset($customerData['mobile']) && ($customerData['mobile'] != '') && isset($customerData['is_verified']) && !$customerData['is_verified']}
                        <div class="alert alert-danger">
                            {l s='Your WhatsApp number has not been verified yet. Please save these details in order to verify this number.' mod='wkwhatsappbusiness'}
                        </div>
                    {/if}
                    <form id="add-wa-number-form" class="form-horizontal" method="post" action="{$action}">
                        <div class="form-group row">
                            <label class="col-md-4 form-control-label" for="receive_notification">
                                {l s='Receive WhatsApp notifications?' mod='wkwhatsappbusiness'}
                            </label>
                            <div class="col-md-8" style="margin-top:10px;">
                                <label class="radio-inline">
                                    <input type="radio" name="receive_notification" value="1" required {if isset($smarty.post.receive_notification) && ($smarty.post.receive_notification == 1)}checked{elseif isset($customerData['active']) && $customerData['active']}checked{/if}>
                                    {l s='Yes' mod='wkwhatsappbusiness'}
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="receive_notification" value="0" {if isset($smarty.post.receive_notification) && ($smarty.post.receive_notification == 0)}checked{elseif isset($customerData['active']) && !$customerData['active'] && !isset($smarty.post.receive_notification)}checked{elseif !isset($customerData['active']) && !isset($smarty.post.receive_notification)}checked{/if}>
                                    {l s='No' mod='wkwhatsappbusiness'}
                                </label>
                            </div>
                        </div>
                        <div class="form-group row" id="wk_whatsapp_number_block" style="{if isset($smarty.post.receive_notification) && ($smarty.post.receive_notification == 1)}display:block;{elseif isset($customerData['active']) && $customerData['active']}display:block;{else}display:none;{/if}">
                            <label class="col-md-4 form-control-label" for="whatsapp_number">
                                {l s='WhatsApp number' mod='wkwhatsappbusiness'}
                            </label>
                            <div class="col-md-8">
                                <div class="row">
                                    <div class="col-md-3 call_prefix_block">
                                        <select class="form-control" name="call_prefix" id="call_prefix">
                                            {foreach from=$countries item=country}
                                                <option {if isset($smarty.post.call_prefix) && ($smarty.post.call_prefix == $country.call_prefix)}selected{elseif isset($customerData['call_prefix']) && ($customerData['call_prefix'] == $country.call_prefix)}selected{/if} value="{$country.call_prefix}">{$country.call_prefix}(
                                                {$country.iso_code})</option>
                                            {/foreach}
                                        </select>
                                    </div>
                                    <div class="col-md-5 whatsapp_number_block">
                                        <input type="number" class="form-control" name="whatsapp_number" id="whatsapp_number" value="{if isset($smarty.post.whatsapp_number)}{$smarty.post.whatsapp_number}{elseif isset($customerData['mobile']) && $customerData['mobile']}{$customerData['mobile']}{/if}">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-8 offset-md-4">
                                <input type="hidden" name="submitWABNotification" value="1">
                                <input type="hidden" name="token" value="{$token}">
                                {hook h='displayGDPRConsent' mod='psgdpr' id_module=$id_module}
                                <br>
                                <button id="saveWaNumber" class="btn btn-primary form-control-submit" type="submit">
                                    {l s='Save' mod='wkwhatsappbusiness'}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            {/if}
        </div>
    </section>
{/block}
