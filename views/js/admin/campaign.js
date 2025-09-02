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

$(document).ready(function(){
    $('.wkchosen').chosen({
        width: '100%',
        no_results_text: chosen_no_results_text,
        placeholder_text_single: chosen_placeholder_text_single,
        placeholder_text_multiple: chosen_placeholder_text_multiple
    });
    if ($('#header_type').val() == 'text') {
        $('#header_media_url').parents('.form-group').hide(300);
        $('.wk_campaign_header').parents('.form-group').show(300);
    }
    if ($('#header_type').val() == 'media') {
        $('.wk_campaign_header').parents('.form-group').hide(300);
        $('#header_media_url').parents('.form-group').show(300);
    }
    if ($('#header_type').val() == '0') {
        $('.wk_campaign_header').parents('.form-group').hide(300);
        $('#header_media_url').parents('.form-group').hide(300);
    }

    if ($('#button_status_on').is(':checked')){
        // $('.wk-show-button').parents('.form-group').show();

        if ($('#button_type').val() == 'quick_reply') {
            $('#button_action_type').parents('.form-group').hide(300);
            $('.wk-call').parents('.form-group').hide(300);
            $('.wk-website').parents('.form-group').hide(300);
            $('#button_action_type').val(0);

            $('.first_quick_reply_text').parents('.form-group').show(300);
            $('.second_quick_reply_text').parents('.form-group').show(300);
            $('.third_quick_reply_text').parents('.form-group').show(300);
        }
        if ($('#button_type').val() == 'call_to_action') {
            $('.first_quick_reply_text').parents('.form-group').hide(300);
            $('.second_quick_reply_text').parents('.form-group').hide(300);
            $('.third_quick_reply_text').parents('.form-group').hide(300);

            $('#button_action_type').parents('.form-group').show(300);

            if ($('#button_action_type').val() == 'call') {
                $('.wk-website').parents('.form-group').hide(300);
                $('.wk-call').parents('.form-group').show(300);
            }

            if ($('#button_action_type').val() == 'visit_website') {
                $('.wk-call').parents('.form-group').hide(300);
                $('.wk-website').parents('.form-group').show(300);
            }
            if ($('#button_action_type').val() == 0) {
                $('.wk-website').parents('.form-group').hide(300);
                $('.wk-call').parents('.form-group').hide(300);
            }
        }
        if ($('#button_type').val() == 0) {
            $('#button_action_type').parents('.form-group').hide(300);

            $('.first_quick_reply_text').parents('.form-group').hide(300);
            $('.second_quick_reply_text').parents('.form-group').hide(300);
            $('.third_quick_reply_text').parents('.form-group').hide(300);
            $('.wk-call').parents('.form-group').hide(300);
            $('.wk-website').parents('.form-group').hide(300);
            $('#button_action_type').val(0);
        }

    } else {
        $('.wk-show-button').parents('.form-group').hide();
    }

    $(document).on('change', '#header_type', function () {
        if ($(this).val() == 'text') {
            $('#header_media_url').parents('.form-group').hide(300);
            $('.wk_campaign_header').parents('.form-group').show(300);
        }
        if ($(this).val() == 'media') {
            $('.wk_campaign_header').parents('.form-group').hide(300);
            $('#header_media_url').parents('.form-group').show(300);
        }
        if ($(this).val() == '0') {
            $('.wk_campaign_header').parents('.form-group').hide(300);
            $('#header_media_url').parents('.form-group').hide(300);
        }
    });

    $(document).on("change", 'input[name="button_status"]', function () {
        if ($(this).val() == 0) {
            $('.wk-show-button').parents('.form-group').hide(300);
        } else {
            $('#button_type').parents('.form-group').show(300);
        }
    });

    $(document).on("change", '#button_type', function () {
        $('.wk-call').parents('.form-group').hide(300);
        $('.wk-website').parents('.form-group').hide(300);
        $('#button_action_type').val(0);

        if ($(this).val() == 'call_to_action') {
            $('.first_quick_reply_text').parents('.form-group').hide(300);
            $('.second_quick_reply_text').parents('.form-group').hide(300);
            $('.third_quick_reply_text').parents('.form-group').hide(300);

            $('#button_action_type').parents('.form-group').show(300);
        }
        if ($(this).val() == 'quick_reply') {
            $('#button_action_type').parents('.form-group').hide(300);

            $('.first_quick_reply_text').parents('.form-group').show(300);
            $('.second_quick_reply_text').parents('.form-group').show(300);
            $('.third_quick_reply_text').parents('.form-group').show(300);
        }

        if ($(this).val() == '0') {
            $('.first_quick_reply_text').parents('.form-group').hide(300);
            $('.second_quick_reply_text').parents('.form-group').hide(300);
            $('.third_quick_reply_text').parents('.form-group').hide(300);
            $('#button_action_type').parents('.form-group').hide(300);
            $('.wk-call').parents('.form-group').hide(300);
            $('.wk-website').parents('.form-group').hide(300);
        }
    });

    $(document).on("change", '#button_action_type', function () {
        if ($(this).val() == 'call') {
            $('.wk-website').parents('.form-group').hide(300);
            $('.wk-call').parents('.form-group').show(300);
        }
        if ($(this).val() == 'visit_website') {
            $('.wk-call').parents('.form-group').hide(300);
            $('.wk-website').parents('.form-group').show(300);
        }
        if ($(this).val() == '0') {
            $('.wk-call').parents('.form-group').hide(300);
            $('.wk-website').parents('.form-group').hide(300);
        }

    });

    if ($('#campaign_name').val() != undefined) {
        if ($('#campaign_name').val() != '') {
            var inputedName = $('#campaign_name').val();
            var lowerCaseInputedName = inputedName.toLowerCase();
            var underScoredInputedName = lowerCaseInputedName.replaceAll(" ", "_");
            $('#wkCreatedCampaignName').html(underScoredInputedName);
        }
    }
    $(document).on("keyup", "#campaign_name", function() {
        var inputedName = $(this).val();
        var lowerCaseInputedName = inputedName.toLowerCase();
        var underScoredInputedName = lowerCaseInputedName.replaceAll(" ", "_");
        $('#wkCreatedCampaignName').html(underScoredInputedName);
    });

    $(document).on('click', '#sendCampaignButton', function(){
        var touchedIdCampaign = $(this).attr('data-id');
        $('#whatsapp_confirmation_modal').modal('show');
        $('#whatsapp_confirmation_modal button[value="confirm"]').off('click');
        $('#whatsapp_confirmation_modal button[value="confirm"]').on('click', () => {
            var data = {
                action: 'getSendCampaignToCustomer',
                ajax: true,
                idCampaign: touchedIdCampaign,
                wabToken: wabToken
            };
            $.ajax({
                url: wabCampaignUrl,
                dataType: 'json',
                type: 'post',
                data: data,
                beforeSend: function () {
                    $('#wabloader').show(300);
                    $('#whatsapp_confirmation_modal button[value="confirm"]').attr('disabled', true);
                    $('#whatsapp_confirmation_modal .cancel').attr('disabled', true);
                },
                complete: function () {
                    $('#whatsapp_confirmation_modal button[value="confirm"]').attr('disabled', false);
                    $('#whatsapp_confirmation_modal .cancel').attr('disabled', false);
                    $('#wabloader').hide(300);
                },
                success: function (response) {
                    if (response.hasError) {
                        $.growl.error({title: "", message: response.message});
                    } else {
                        $.growl.notice({title: "", message: response.message});
                    }
                    $('#wabloader').hide(300);
                    $('#whatsapp_confirmation_modal').modal('hide');
                },
                error: function (jqXHR, exception) {
                    $('#wabloader').hide(300);
                    $('#whatsapp_confirmation_modal').modal('hide');
                    $('#whatsapp_confirmation_modal button[value="confirm"]').attr('disabled', false);
                    $('#whatsapp_confirmation_modal .cancel').attr('disabled', false);
                }
            });
        });
    });


    // if (templateInfo != undefined && templateInfo.success != undefined && templateInfo.success == true) {
    //     if (templateInfo.response != undefined && templateInfo.response.data != undefined) {
    //         var languageWiseTemplateStatus = [];
    //         $.each(templateInfo.response.data, function(key, value) {
    //             languageWiseTemplateStatus[value.language] = value.status
    //         });
    //     }
    // }
    // $.each($('#wk_wab_campaign_form').serializeArray(), function(key, fieldName) {
    //     $.each(langsForJs, function(langId, langName) {
    //         if (languageWiseTemplateStatus[langName] == 'APPROVED') {
    //             if (fieldName.name.includes(langId)) {
    //                 $('#'+fieldName.name).attr('readonly', true);
    //             } else {
    //                 // $('#'+fieldName.name).attr('readonly', true);
    //             }
    //         }
    //     });
    // });
});
