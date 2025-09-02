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

$(document).ready(function () {
    if (!wkWabConfigured) {
        return false;
    }
    // Graph date range picker
    $('#date-range-picker').daterangepicker({
        "opens": "left",
        "showDropdowns": true,
        "linkedCalendars": false,
        "startDate": userFriendlyDateFrom,
        "endDate": userFriendlyDateTo,
        "maxDate": currentDate,
        "locale": {
            "format": 'DD-MM-YYYY',
            "separator": " - ",
            "applyLabel": applyLabel,
            "cancelLabel": cancelLabel,
            "fromLabel": fromLabel,
            "toLabel": toLabel,
            "customRangeLabel": customRangeLabel,
            "daysOfWeek": [
                Su,
                Mo,
                Tu,
                We,
                Th,
                Fr,
                Sa
            ],
            "monthNames": [
                January,
                February,
                March,
                April,
                May,
                June,
                July,
                August,
                September,
                October,
                November,
                December
            ],
            "firstDay": 1
        },
    }, function (start, end, label) {
        $("#dashboardDateFrom").val(start.format('YYYY-MM-DD'));
        $("#dashboardDateTo").val(end.format('YYYY-MM-DD'));
        refreshDashboard($('#preselectDateRange').val());
    });

    //add focus class on daterangepicker input
    $('#date-range-picker').on('show.daterangepicker', function (ev, picker) {
        $('.wab-graph-date-content #date-range-picker').addClass('date-range-picker-focus');
    });
    //remove focus class on daterangepicker input
    $('#date-range-picker').on('hide.daterangepicker', function (ev, picker) {
        $('.wab-graph-date-content #date-range-picker').removeClass('date-range-picker-focus');
    });
    //When customer choose button half-hour, day, month
    $(".setPreselectDateRange").on('click', function () {
        $('#preselectDateRange').val($(this).attr('data-date-range'));
        $('.selected_range_text').html($(this).text());
        refreshDashboard($('#preselectDateRange').val());
    });

    //Display Graph Dashboard on page load
    refreshDashboard($('#preselectDateRange').val());
});

function refreshDashboard(preselectDateRange) {
    var dateFrom = $('#dashboardDateFrom').val();
    var dateTo = $('#dashboardDateTo').val();
    $('#wk-wab-graph-chart').addClass('wk-loading-graph');
    $.ajax({
        url: wabAjaxUrl,
        type: 'POST',
        dataType: 'json',
        data: {
            ajax: true,
            action: 'refreshDashboard',
            dateFrom: dateFrom,
            dateTo: dateTo,
            preselectDateRange: preselectDateRange,
        },
        // Ensure to get fresh data
        headers: { "cache-control": "no-cache" },
        cache: false,
        global: false,
        success: function (widgets) {
            $('#wk-wab-graph-chart').removeClass('wk-loading-graph');
            if (typeof widgets.success != 'undefined' && !widgets.success) {
                if (typeof widgets.response.error.error_user_msg != 'undefined') {
                    showErrorMessage(widgets.response.error.error_user_msg);
                } else {
                    showErrorMessage(widgets.response.error.message);
                }
            } else {
                for (var widget_name in widgets) {
                    for (var data_type in widgets[widget_name]) {
                        window[data_type](widget_name, widgets[widget_name][data_type]);
                    }
                }
            }
        }
    });
}

function data_chart(widget_name, charts) {
    for (var chart_id in charts) {
        console.log(window[charts[chart_id].chart_type]);
        window[charts[chart_id].chart_type](widget_name, charts[chart_id]);
    }
}

function data_value(widget_name, data) {
    for (var data_id in data) {
        $('#' + data_id + ' ').html(data[data_id]);
        $('#' + data_id + ', #' + widget_name).closest('section').removeClass('loading');
    }
}

var dashtrends_data;
var dashtrends_chart;
function line_chart_trends(widget_name, chart_details) {
    if (chart_details.data[0].values.length <= 0)
        $('#wk-wab-graph-chart').hide();
    else
        $('#wk-wab-graph-chart').show();
    nv.addGraph(function () {
        var chart = nv.models.lineChart()
            .useInteractiveGuideline(true)
            .x(function (d) { return (d !== undefined ? d[0] : 0); })
            .y(function (d) { return (d !== undefined ? parseInt(d[1]) : 0); })
            .margin({ left: 80 });

        chart.xAxis.tickFormat(function (d) {
            date = new Date(d * 1000);
            return (date.getMonth() + 1) + "/" + date.getDate() + "/" + date.getFullYear();
        });

        first_data = new Array();
        $('#dashtrends_toolbar div').attr('style', '').removeClass('active');
        $.each(chart_details.data, function (index, value) {
            if (value.id == 'sent' || value.id == 'sent_compare') {
                if (value.id == 'sent') {
                    setTimeout(() => {
                        $('#dashtrends_toolbar div:first').css({ 'background-color': chart_details.data[index].color, 'color': '#fff' }).addClass('active');
                    }, 100);
                }
                first_data.push(chart_details.data[index]);
            }
        });

        dashtrends_data = chart_details.data;
        dashtrends_chart = chart;

        d3.select('#wk-wab-graph-chart svg')
            .datum(first_data)
            .call(chart);
        nv.utils.windowResize(chart.update);

        return chart;
    });
}

function selectDashtrendsChart(element, type) {
    console.log(element);
    $('#dashtrends_toolbar div').removeClass('active');
    current_charts = new Array();
    $.each(dashtrends_data, function (index, value) {
        if (value.id == type || value.id == type + '_compare') {
            if (value.id == type) {
                $(element).siblings().css({ 'background-color': '#fff', 'color': '#414141' }).removeClass('active');
                $(element).css({ 'background-color': dashtrends_data[index].color, 'color': '#fff' }).addClass('active');
            }
            current_charts.push(dashtrends_data[index]);
            value.disabled = false;
        }
    });

    dashtrends_chart.yAxis.tickFormat(d3.format('.f'));

    if (type == 'conversion_rate')
        dashtrends_chart.yAxis.tickFormat(function (d) {
            return d3.round(d * 100, 2) + ' %';
        });

    d3.select('#wk-wab-graph-chart svg')
        .datum(current_charts)
        .call(dashtrends_chart);
}
