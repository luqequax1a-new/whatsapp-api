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

$(document).ready(function() {
    $('[name="receive_notification"]').change(function() {
        if (Number($(this).val()) == 1) {
            $('#wk_whatsapp_number_block').slideDown();
        } else {
            $('#wk_whatsapp_number_block').slideUp();
        }
    });
    var sec = 30;
    var wkCounter = setInterval(() => {
        $('#wkOtpCountDown').html('('+sec+')');
        if (sec < 0) {
            $('#wkBtnResendOtp').attr('disabled', false);
            $('#wkOtpCountDown').html('');
            clearInterval(wkCounter);
        }
        sec--;
    }, 1000);

    $('#wkBtnResendOtp').click(function() {
        var thisEle = $(this);
        $.ajax({
            url: wkWabAjaxLink,
            method: 'POST',
            dataType: 'json',
            data: {
                ajax: true,
                action: 'resendOtp',
                token: wkToken
            },
            beforeSend: function () {
                thisEle.attr('disabled', true);
            },
            complete: function () {
                thisEle.attr('disabled', false);
            },
            success: function(result) {
                if (result.success) {
                    $('#wkAlertMessages').html('<div class="alert alert-success">'+result.message+'</div>');
                    $('#wkAlertMessages').show();
                } else {
                    $('#wkAlertMessages').html('<div class="alert alert-danger">'+result.message+'</div>');
                    $('#wkAlertMessages').show();
                }
                setTimeout(() => {
                    $('#wkAlertMessages').hide();
                }, 5000);
            },
            error: function(xhr) {
                console.log(xhr.statusText);
            }
        });
    });
    $('#verify-wa-number-form').submit(function(e) {
        $('#wkBtnVerify').attr('disabled', true);
        return true;
    });
    $('#add-wa-number-form').submit(function(e) {
        $('#saveWaNumber').attr('disabled', true);
        return true;
    });
});
