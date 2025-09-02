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

<div class="modal fade " id="whatsapp_confirmation_modal" tabindex="-1" style="display: none;"  aria-hidden="true">
    <div class="modal-dialog ">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{l s='Send campaign to customer' mod='wkwhatsappbusiness'}</h4>
                <button type="button" class="close" data-dismiss="modal">×</button>
            </div>
            <div class="modal-body">
                {l s='Please confirm to send campign to customer' mod='wkwhatsappbusiness'}
                <div class="modal-loader" id="wabloader">
                    <img src="{$loaderPopUpImg|escape:'htmlall':'UTF-8'}">
                    <div class="" style="color: rgb(0, 128, 0);">
                        {l s='Please wait sending message to campaign customers...' mod='wkwhatsappbusiness'}
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="cancel btn btn-outline-secondary btn-lg" data-dismiss="modal">
                    {l s='Cancel' mod='wkwhatsappbusiness'}
                </button>
                <button type="button" value="confirm" class="btn btn-primary btn-lg" style="text-transform:none;">
                    {l s='Confirm' mod='wkwhatsappbusiness'}
                </button>
            </div>
        </div>
    </div>
</div>
