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

$(window).ready(function() {
    $('#wk-template-block')
        .parents('.form-wrapper')
        .removeClass('form-wrapper');
    $('#wk-template-block')
        .parents('.form-group > div')
        .removeClass('col-lg-8 col-lg-offset-3 col-lg-9')
        .addClass('col-md-12');
    if (typeof isWabTplExists != 'undefined') {
        if (isWabTplExists) {
            $('[name="submitTemplateSettings"').attr('disabled', true);
        }
    }
    $('[name="WK_WAB_SEND_ORDER_UPDATE"]').change(function() {
        if (Number($(this).val()) == 1) {
            $('#WK_WAB_SEND_ORDER_STATUS___chosen').parents('.form-group').slideDown();
            $('[name="WK_WAB_SEND_ORDER_STATUS[]"]').parents('.form-group').slideDown();
        } else {
            $('#WK_WAB_SEND_ORDER_STATUS___chosen').parents('.form-group').slideUp();
            $('[name="WK_WAB_SEND_ORDER_STATUS[]"]').parents('.form-group').slideUp();
        }
    });
    if (typeof wkWabOrderStaus != 'undefined') {
        if (wkWabOrderStaus) {
            $('#WK_WAB_SEND_ORDER_STATUS___chosen').parents('.form-group').slideDown();
            $('[name="WK_WAB_SEND_ORDER_STATUS[]"]').parents('.form-group').slideDown();
        } else {
            $('#WK_WAB_SEND_ORDER_STATUS___chosen').parents('.form-group').slideUp();
            $('[name="WK_WAB_SEND_ORDER_STATUS[]"]').parents('.form-group').slideUp();
        }
    }
    $('#module_form_1').submit(function() {
        $('[name="submitTemplateSettings"]').attr('disabled', true);
        return true;
    });
});
