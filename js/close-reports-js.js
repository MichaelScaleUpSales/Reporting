//Ajax to return data from BE for selected filter
var requestFilter = report_name => {
    var selectedFilters = getSelectedFilter(report_name, false);
    var channel_custom_field_id = jQuery("[name='channel_custom_field_id']").val();
    jQuery('.loader').css('display', 'block');
    jQuery('.page-loader').delay(350).css('display', 'block');
    var subFilter = jQuery(`body .${report_name} .sub-filters`).clone();
    var leadOwnerIndex = $(`#Lead_Owner_${report_name}`).prop('selectedIndex');
    var campaignIndex = $(`#Campaign_${report_name}`).prop('selectedIndex');
    var dateIndex = $(`#datepicker_${report_name}`).prop('selectedIndex');
    console.log("TULA 1");
    jQuery.ajax({
        url: close_ajax_report_object.ajax_url,
        data: {
            data: selectedFilters,
            action: report_name,
            channel_custom_field_id
        },
        type: 'GET',
        dataType: 'html',
        success: function (response) {
            console.log(report_name);
            jQuery('.' + report_name).html(response);
            jQuery(`body .${report_name} .sub-filters-container`).html('');
            jQuery(`body .${report_name} .sub-filters-container`).html(subFilter);
            jQuery(`body #Lead_Owner_${report_name}`).prop('selectedIndex', leadOwnerIndex);
            jQuery(`body #Campaign_${report_name}`).prop('selectedIndex', campaignIndex);
            jQuery(`body #datepicker_${report_name}`).prop('selectedIndex', dateIndex);
            updateFiltersApplied(report_name);
            if (report_name === "lead_generation_metrics_report" || report_name === "lead_generation_metrics_report_lead" || report_name === "pipeline_overview_report") {
                
                var selectedFilters = getSelectedFilter(report_name);
                renderChipFilter(report_name, selectedFilters);
                init_date_picker(report_name);
                jQuery('.select-channel').selectpicker('render');
                jQuery(".datepicker_" + report_name + "_input").removeClass("hasDatepicker");
                jQuery(".datepicker_" + report_name + "_input").datepicker("destroy");
                jQuery(".datepicker_" + report_name + "_input").removeAttr("id");
                jQuery(".datepicker_" + report_name + "_input").datepicker("refresh");
            }

            hideLoader();
        },
        error: (error) => {
            console.log(error);
        }
    })
}


function init_date_picker (report_name, column_number = 1) {
    console.log(report_name);
    var filters_name_selector = '';
    if (report_name === 'sales_process_metrics_report') {
        filters_name_selector = `#filters_${column_number}`; 
    }
    jQuery(`.${report_name} ${filters_name_selector} #datepicker_${report_name} input`).daterangepicker({
        autoUpdateInput: false,
        locale: {
            cancelLabel: 'Clear'
        }
    });

    jQuery(`.${report_name} ${filters_name_selector} #datepicker_${report_name} input`).on('apply.daterangepicker', function(ev, picker) {
        let value = picker.startDate.format('YYYY-MM-DD') + ' to ' + picker.endDate.format('YYYY-MM-DD');

        jQuery(`.${report_name} ${filters_name_selector} #datepicker_${report_name} input`).val(value);
        if(report_name == "sales_process_metrics_report") {
            var column_number = picker.element.parents('.filters')[0].id.split('_')[1];
            var report_name_parent = jQuery('#filters_' + column_number + ' #report_filter_sales_process_metrics_report').parent().attr('id');
            callDataFromBakEndForSalesProcess('', report_name, report_name_parent);
        }
        else {
            callDataFromBakEnd('', report_name);
            //getOutreachMetrics();
            
        }
    });

    jQuery(`.${report_name} ${filters_name_selector} #datepicker_${report_name}`).on('cancel.daterangepicker', function(ev, picker) {
        jQuery(`.${report_name} #datepicker_${report_name} input`).val('');
    });
}




function init_date_picker_lead (report_name, column_number = 2) {
    
    var filters_name_selector = '';
    if (report_name === 'sales_process_metrics_report') {
        filters_name_selector = `#filters_${column_number}`; 
    }

    jQuery(`#datepicker_${report_name}_lead input`).daterangepicker({
        autoUpdateInput: false,
        locale: {
            cancelLabel: 'Clear'
        }
    });

    //datepicker for qualified leads
    jQuery(`#datepicker_${report_name}_lead input`).on('apply.daterangepicker', function(ev, picker) {
        
        let value = picker.startDate.format('YYYY-MM-DD') + ' to ' + picker.endDate.format('YYYY-MM-DD');
        

        jQuery(`#datepicker_${report_name}_lead input`).val(value);
        //callDataFromBakEnd('', report_name+"_lead");
        callDataFromBakEnd('', report_name+"_lead");
        
    });


 
    jQuery(`#datepicker_${report_name}_lead`).on('cancel.daterangepicker', function(ev, picker) {
        
        jQuery(`#datepicker_${report_name}_lead input`).val('');
    });
}


function init_date_picker_leadtab (report_name, column_number = 1) {
    
    var filters_name_selector = '';
    jQuery(`#datepicker_lead_generation_metrics_report_leadtab input`).daterangepicker({
        autoUpdateInput: false,
        locale: {
            cancelLabel: 'Clear'
        }
    });

    //datepicker for qualified leads
    jQuery(`#datepicker_lead_generation_metrics_report_leadtab input`).on('apply.daterangepicker', function(ev, picker) {
        let value = picker.startDate.format('YYYY-MM-DD') + ' to ' + picker.endDate.format('YYYY-MM-DD');
        jQuery(`#datepicker_lead_generation_metrics_report_leadtab input`).val(value);
        retriveQualifiedLeads(value);
    });


 
    jQuery(`#datepicker_lead_generation_metrics_report_leadtab`).on('cancel.daterangepicker', function(ev, picker) {
        jQuery(`#datepicker_${report_name}_leadtab input`).val('');
    });
}

jQuery(document).ready(function () {

    $(document).on('change','#qualified-leads-campaign',function(){
        if($(this).val() == "all"){
            $('.qualified_leads_wrapper table tbody tr').show();
            $('.qualified-count span').text($('.qualified_leads_wrapper table tbody tr').length);
        }else{
            var campaign_show = $("#qualified-leads-campaign").val();
            $('.qualified_leads_wrapper table tbody tr').hide();
            var found_leads = $('.qualified_leads_wrapper table tbody').find('tr[data-campaign="'+campaign_show+'"]');
            found_leads.show();
            $('.qualified-count span').text(found_leads.length);
        }
        
    });

    jQuery('.quick-filter-box').click(function(){
        var data = jQuery(this).find('a').data('quickfilter');
        $('.page-loader').show();
        $('.quick-filter-box').removeClass('active');
        $(this).addClass('active');
        
        $.ajax({
            url: close_ajax_report_object.ajax_url,
            data: {
                data: data,
                action: "qualified_lead_quickfilters"
            },
            type: 'POST',
            dataType: 'json',
            success: function (response) {
                $('#qualified-leads-campaign').empty().append(response.campaigns).show();
                $('.page-loader').css("display","none");
                $('.qualified_leads_wrapper').empty().append(response.html);
            }
        });
        
    });
     
    init_date_picker_leadtab("lead_generation_metrics_report");
    //Flags to avoid duplicate request
    var lead = false;
    var sales = false;
    var pipeline = false;
    var qualified = false;

    //Call ajax function according report tab name
    var getReportTabDOM = report_name => {
        switch (report_name) {
            case "#qualified-leads":
                requestReportTab("qualified-leads");
                sales = true;
                break;
            case "#sales-process-metrics":
                requestReportTab("sales_process_metrics");
                sales = true;
                break;
            case "#pipeline-overview":
                requestReportTab("pipeline_overview");
                pipeline = true;
                break;
            case "#lead_generation_metrics":
                requestReportTab("lead_generation_metrics");
                lead = true;
                break;
            default:
                requestReportTab("lead_generation_metrics");
                lead = true;
                break;
        }
    }

   
    //Ajax to return data from BE for selected filter on sales report
    var requestFilterSales = (report_name, report_name_parent) => {
        var selectedFilters = getSelectedFilter(report_name, report_name_parent);
        var channel_custom_field_id = jQuery("[name='channel_custom_field_id']").val();
        var columnNumber = parseInt(report_name_parent.split('_')[1]);
        
        jQuery('.loader').css('display', 'block');
        jQuery('.page-loader').delay(350).css('display', 'block');
        
        jQuery.ajax({
            url: close_ajax_report_object.ajax_url,
            data: {
                data: selectedFilters,
                action: report_name,
                channel_custom_field_id
            },
            type: 'GET',
            dataType: 'html',
            success: function (response) {
                updateFilterColumn(JSON.parse(response), columnNumber);

               

                if(report_name == 'sales_process_metrics_report'){
                   $('table#report-table .sales_process_item_value:nth-child('+ parseInt(getNum(columnNumber)+1) +')').text('0');
                    var response = JSON.parse(response);
                    var opportunity_values = response['opportunity_list'];
                    
                    for (current_element in opportunity_values) {
                        

                        if (current_element) {
                            var opportunity = opportunity_values[current_element];

                            var opportunity_value = jQuery('#' + current_element).children()[getNum(columnNumber)];
                            
                            opportunity_value.innerText = opportunity['count'];
                            jQuery('#' + current_element).show();
                        }
                    }

                }
                hideLoader();
            },
            error: (error) => {
                console.log(error);
            }
        })
    }
    //Reset scale filter
    jQuery('body').on("click", ".reset-scale", function () {
        var report_name = jQuery(this).attr("data-report-name");
        var report_name_parent = jQuery(this).parent().parent().parent().attr('id');
        var columnNumber = parseInt(report_name_parent.split('_')[1]);
        jQuery(`#${report_name_parent} #report_filter_${report_name} #datepicker_${report_name} span`).html('First Outreach Date');
        jQuery(`#${report_name_parent} #report_filter_${report_name} select`).prop('selectedIndex', 0);
        jQuery('.selectpicker').selectpicker('render');
        jQuery('.datepicker_' + report_name + '_input_' + columnNumber).datepicker('setDate', 'null');
        jQuery('.datepicker_' + report_name + '_input_' + columnNumber).val('First Outreach Date');

        var response = {"opportunity_list":initial_data};
        updateFilterColumn(response, columnNumber);
    });
    //Ajax to return report tab DOM
    var requestReportTab = report_name => {
        jQuery('.loader').css('display', 'block');
        jQuery('.page-loader').delay(350).css('display', 'block');
        // Call the filter API to update the report table.
        console.log("DA2");
        jQuery.ajax({
            url: close_ajax_report_object.ajax_url,
            data: {
                action: 'get_report_tab',
                report_name
            },
            type: 'GET',
            dataType: 'html',
            success: function (response) {
                jQuery('.' + report_name + '_container').html(response);
                hideLoader();
                // To style only selects with the select-channel class
                jQuery('.select-channel').selectpicker();
                jQuery('.selectpicker').selectpicker();

            },
            error: (error) => {
                console.log(error);
            }
        })
    }
    //Get report tab at the first load
    var url = document.URL;
    var hash = url.substring(url.indexOf('#'));

    // Check if the user have access to reports to get reports data or not.
    if (jQuery('.login-error-container') && jQuery('.login-error-container').length > 0) {
        hideLoader();
    } else {
        getReportTabDOM(hash);
    }

    //Tab event handler
    $("#headerTabs").find("li a").each(function (key, val) {
        if (hash == $(val).attr('href'))
            $(val).click();
        $(val).click(function (ky, vl) {
            location.hash = $(this).attr('href');
            if (($(this).attr('href') === "#lead-generation-metrics" || $(this).attr('href') === "") && !lead)
                getReportTabDOM("#lead-generation-metrics");
            if ($(this).attr('href') === "#sales-process-metrics" && !sales)
                getReportTabDOM("#sales-process-metrics");
            if ($(this).attr('href') === "#pipeline-overview" && !pipeline)
                
                getReportTabDOM("#pipeline-overview");
                if ($(this).attr('href') === "#qualified-leads" && !qualified)
                    getReportTabDOM("#qualified-leads");
        });
    });

    //Search by company name on pipeline report
    jQuery('body').on('keyup', ".search-filter", function () {
        var value = jQuery(this).val().toLowerCase();
        jQuery(".company-label").filter(function () {
            jQuery(this).toggle(jQuery(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    //Filter table by channel name for lead generation report
    jQuery('body').on('changed.bs.select', '.select-channel', function (e, clickedIndex, newValue, previousValue) {
        var filter, table, row, channelCell, channelValue;
        $('#noDataFound').remove();
        filter = jQuery('.select-channel').selectpicker('val').toLowerCase();;

        table = document.getElementById("reportTable");
        row = table.getElementsByTagName("tr");
        for (var i = 0; i < row.length; i++) {
            channelCell = row[i].getElementsByTagName("td")[0];
            if (channelCell) {
                channelValue = channelCell.textContent || channelCell.innerText;
                if (filter === 'all channels')
                    row[i].style.display = "";
                else if (channelValue.toLowerCase().indexOf(filter) > -1) {
                    row[i].style.display = "";
                } else {
                    row[i].style.display = "none";
                }
            }
        }
        if (jQuery('#reportTable > tbody > tr:not([style*="display: none"])').length - 1 === 0)
            $('#reportTable tr:last').after('<tr id="noDataFound"> <td>No data found</td></tr>');
    });

    
    //Apply Filter when dropdown change
    jQuery('body').on('changed.bs.select', '#report_filter_sales_process_metrics_report select', function (e, clickedIndex, newValue, previousValue) {
        e.preventDefault();
        var report_name = jQuery('#report_filter_sales_process_metrics_report').attr("data-report-name");
        var report_name_parent = jQuery(this).parent().parent().parent().parent().attr('id');
        requestFilterSales(report_name, report_name_parent);
    });

    jQuery('body').on('changed.bs.select', '#report_filter_lead_generation_metrics_report select', function (e, clickedIndex, newValue, previousValue) {
        e.preventDefault();
        var report_name = jQuery('#report_filter_lead_generation_metrics_report').attr("data-report-name");
        requestFilter(report_name, '');
    });

    jQuery('body').on('changed.bs.select', '#report_filter_pipeline_overview_report select', function (e, clickedIndex, newValue, previousValue) {
        e.preventDefault();
        var report_name = jQuery('#report_filter_pipeline_overview_report').attr("data-report-name");
        requestFilter(report_name, '');
    });
    jQuery(".datepicker_lead_generation_metrics_report_input").on("change paste keyup cut select", function() {
        alert(jQuery(this).val());  
    }); 



    //Remove selected filter when user close it
    jQuery('body').on('click', '.closebtn', function () {
        var report_name = jQuery(this).attr("data-report-name");
        var filterName = jQuery(this).attr("data-filter-name");
        jQuery(`#report_filter_${report_name} input[type="radio"][name="` + filterName + '"]').prop('checked', false);
        jQuery(`#report_filter_${report_name} input[type="number"][name="` + filterName + '"]').val("");
        
        if(report_name == "pipeline_overview_report") {
            if (filterName === "date") {
                jQuery(`#report_filter_${report_name} .datepicker_`+ report_name + '_input').datepicker('setDate', 'null');
                jQuery(`#report_filter_${report_name} .datepicker_` + report_name + '_input').val('First Outreach Date');
            } else {
                jQuery('#' + filterName.replaceAll(" ", "_") +"_"+ report_name).selectpicker('val', '');
            }
            
        }
        else if(report_name == "lead_generation_metrics_report") {

            jQuery('.datepicker_' + report_name + '_input').datepicker('setDate', 'null');
            jQuery('.datepicker_' + report_name + '_input').val('First Outreach Date');

        }
        //jQuery('#' + filterName.replaceAll(" ", "-")).remove();
        requestFilter(report_name);

    });
    //Reset filter
    jQuery('body').on("click", ".reset", function () {
        var report_name = jQuery(this).attr("data-report-name");
        jQuery(`body #datepicker_${report_name} span`).html('First Outreach Date');
        jQuery(`body #Lead_Owner_${report_name}`).prop('selectedIndex', 0);
        jQuery(`body #Company_Size_${report_name}`).prop('selectedIndex', 0);
        jQuery(`body #First_Response_Medium_${report_name}`).prop('selectedIndex', 0);
        jQuery(`body #First_Response_Sentiment_${report_name}`).prop('selectedIndex', 0);
        jQuery(`body #Lead_Source_${report_name}`).prop('selectedIndex', 0);
        jQuery(`.filters-applied_${report_name}`).html('0 filters applied');
        jQuery('.datepicker_' + report_name + '_input').datepicker('setDate', 'null');
        jQuery('.datepicker_' + report_name + '_input').val('First Outreach Date');
        jQuery(`.btn-apply_report_filter_${report_name}`).click();
        jQuery('.selectpicker').selectpicker('render');

    });
    //Filter Submit
    jQuery('body').on("submit", "#report_filter_lead_generation_metrics_report, #report_filter_pipeline_overview_report", function (event) {
        event.preventDefault();
        var report_name = jQuery(this).attr("data-report-name");
        requestFilter(report_name, '');
    });
    jQuery('body').on("submit", "#report_filter_sales_process_metrics_report", function (event) {
        event.preventDefault();
        var report_name = jQuery(this).attr("data-report-name");
        var report_name_parent = jQuery(this).parent().attr('id');
        requestFilterSales(report_name, report_name_parent);
    });

    jQuery('body').on('change', '.opportunity_list_labels', opportunityListChanged);

    function opportunityListChanged() {
        var name = jQuery('.opportunity_list_labels').children('option:selected').attr('name');
        var display_name = jQuery('.opportunity_list_labels').children('option:selected').html();
        jQuery('.opportunity_list').each((index, item) => {
            var $item = jQuery(item);
            var value = $item.children('[name="' + name + '"]').val();
            var parent_container = $item.parent('.number_of_opportunities_container');
            parent_container.children('.number_of_opportunities_display').html(value);
            updateLeadsConverted(parent_container, display_name, index);
        });
    }

    function updateLeadsConverted(parent_container, name, index) {
        var opportunity_status_items = JSON.parse(parent_container.children('.opportunity_status_items').html())

        jQuery("#opportunity_choice_companies_contacted").html(name);
        jQuery("#opportunity_choice_companies_replied").html(name);
        jQuery("#opportunity_choice_replied_positively").html(name);

        var opportunity_status = opportunity_status_items[name];
        for (const key in opportunity_status) {
            jQuery(jQuery("." + key)[index]).html(Number(opportunity_status[key]).toFixed(2) + ' %');
        }
    }

    function addOpportunityStatusRows() {
        var random_key = Object.keys(leads_converted_to_opportunity_status)[0];
        var random_object = leads_converted_to_opportunity_status[random_key];
        for (key in random_object) {
            value = random_object[key];
            jQuery('#' + key).append('<td class="sales_process_item_value ' + key + '"> ' + Number(value).toFixed(2) + ' </td>');
        }
    }

    // Remove column button
    jQuery('body').on("click", ".remove-column", function () {
        var column_id = jQuery(this).parent().parent().attr('id');
        // Split the column id to get the column index
        var split_column_id= column_id.split('_');
        jQuery(".sales_process_metrics_report tr").each(function() {
            jQuery(this).children("td:eq("+split_column_id[1]+")").remove();
        });
        var column = 1;
        jQuery(".sales_process_metrics_report .filters ").each(function() {
            jQuery(this).attr('id', 'filters_'+column);
                column++;
        });
      });

    // Add new column button
    jQuery('body').on("click", "#add-new-cloumn", function () {

        // Get the number of columns in the table
        var number_of_columns = jQuery('#sales-process-metrics #report-table').find("tr:first td").length;

        // Add the filters to the new column
        var filtersElement = jQuery('#sales-process-metrics .report-table-filters-row');
        var new_filter_id = 'id="report_filter' + number_of_columns + '"';
        var filters = filtersElement.find(".filters").html();
        filters = filters.replace('datepicker_sales_process_metrics_report_input_1', 'datepicker_sales_process_metrics_report_input_' +number_of_columns);

        var form_with_new_filter_id = filters.replace('id="report_filter"', new_filter_id);

        filtersElement.append("<td class='filters' id='filters_" + number_of_columns + "'>" + form_with_new_filter_id +
        "<span><span class='remove-icon remove-column'></span><input class='btn-remove-column btn-apply_report_filter_sales_process_metrics_report remove-column' type='button' id='remove' name='remove' value='Remove this comparison'></span>"+ "</td>");


        
        // Add column header
        var filtersElement = jQuery('#sales-process-metrics .report-table-header');
        filtersElement.append("<th class='report-table-header-item'> Value </th>");

        for (current_element in initial_data) {
            var initial_value = initial_data[current_element];
            if (typeof initial_value === 'object') {
                    var initial_opportunity_label = initial_value['label'];
					var initial_opportunity_value = initial_value['count'];
                    var opportunities_element = '<td class="sales_process_item_value">' + initial_opportunity_value + '</td>';
                    jQuery('#' + current_element).append(opportunities_element);
            } else {
                jQuery('#' + current_element).append('<td class="sales_process_item_value"> ' + initial_value + (current_element.includes("rate") ? " %" : "") + " </td>");
            }
        }
        var firstColumnDate = jQuery('.datepicker_sales_process_metrics_report_input').val();
        jQuery('#filters_' + number_of_columns).find('.bootstrap-select').replaceWith(function() { return $('select', this); });
        jQuery('#filters_' + number_of_columns +' select.select-input-scale').selectpicker('render');
        init_date_picker('sales_process_metrics_report', number_of_columns);
        jQuery('.datepicker_sales_process_metrics_report_input_' +number_of_columns).removeClass("hasDatepicker");
        jQuery('.datepicker_sales_process_metrics_report_input_' +number_of_columns).datepicker("destroy");
        jQuery('.datepicker_sales_process_metrics_report_input_' +number_of_columns).removeAttr('id');
        opportunityListChanged();
    });
});

//Display selected filter
function renderChipFilter (report_name, selectedFilters) {
    
    jQuery(`.${report_name} .filter-chips-container`).html('');
    console.log(selectedFilters);
    selectedFilters.forEach(chip => {
       // if (!(chip.name === "date" || chip.name === "Lead Owner" || chip.name === "Campaign")) {
            jQuery(`.${report_name} .filter-chips-container`).append(`<div id="${chip.name.replace(" ", "-")}" class="filter-chip">
                    <span class="filter-chip-name">${chip.value}</span>
                    <span data-filter-name="${chip.name}" data-report-name="${report_name}" class="closebtn">&times;</span>
                </div>`);
        //}
    });
}

function leadGeneraationMetricDefaultSelectedFilters() {
    var report_name = 'lead_generation_metrics_report';
    if (leadGeneratioDefaultSelectedFilters && leadGeneratioDefaultSelectedFilters.length) {
        var selectedFiltersCount = 0;
        leadGeneratioDefaultSelectedFilters.forEach(
            (selectedFilter) => {
                jQuery(`#report_filter_${report_name} [name="${selectedFilter['name']}"][value="${selectedFilter['value']}"]`).click();
                selectedFiltersCount++;
            }
        )
        jQuery(`.filters-applied_${report_name}`).html(selectedFiltersCount + ' filters applied');
        renderChipFilter(report_name, leadGeneratioDefaultSelectedFilters);
    }
}

function hideLoader() {
    jQuery('.loader').css('display', 'none');
    jQuery('.page-loader').delay(350).css('display', 'none');
}

function handleDatePickerValue(date, report_name, column_number) {
    
    if(report_name == "sales_process_metrics_report") {
        var report_name_parent = jQuery('#filters_' + column_number + ' #report_filter_sales_process_metrics_report').parent().attr('id');
        callDataFromBakEndForSalesProcess(date, report_name, report_name_parent);
    }
    else {
        callDataFromBakEnd(date, report_name);
    }
 };




function retriveQualifiedLeads(value){
    $('.page-loader').css("display","block");
    $('.quick-filter-box').removeClass('active');
    console.log("4412");
    jQuery.ajax({
        url: close_ajax_report_object.ajax_url,
        data: {
            action: "qualified_lead_get",
            data:{
                date : value
            }
        },
        type: 'POST',
        dataType: 'json',
        success: function (response) {
            $('#qualified-leads-campaign').empty().append(response.campaigns).show();
            $('.page-loader').css("display","none");
            $('.qualified_leads_wrapper').empty().append(response.html);
        },
        error: (error) => {
            console.log(error);
        }
    })
}

jQuery(document).on('change','#campaigns-filter',function(){
    getOutreachMetrics();
})

function getOutreachMetrics(){
    var filters = [];
    $('.page-loader.overlay-container').show();
    console.log("22fasdad");
    jQuery.ajax({
        url: close_ajax_report_object.ajax_url,
        data: {
            action: "outreach_metrics_callback",
            "campaign" : $('#campaigns-filter').val(),
            "date" : $('.datepicker_lead_generation_metrics_report_input').val()
        },
        type: 'POST',
        dataType: 'json',
        success: function (response) { 
            console.log(response);
            $('.page-loader.overlay-container').hide();
            $('#reportTable tr.lead-generation-table-row').remove();
            $('.report-general-information_item:first-child .report-general-information_item_value').text(response.report_data.global_total_companies);
            $('.report-general-information_item:nth-child(2) .report-general-information_item_value').text(response.report_data.total_response);
            $('.report-general-information_item:nth-child(3) .report-general-information_item_value').text(response.report_data.total_positive_response);
            $('.report-general-information_item:nth-child(4) .report-general-information_item_value').text(response.report_data.response_rate + '%');
            $('.report-general-information_item:nth-child(5) .report-general-information_item_value').text(response.report_data.positive_response_rate_out_of_total_response + '%');
            $('.report-general-information_item:nth-child(6) .report-general-information_item_value').text(response.report_data.total_leads);
            /* var html = '';
            for (var report in response.reports) {
                for (var media in response.reports[report]) {
                    

                    html+='<tr class="lead-generation-table-row">';
                    html+='<td><span>'+report+'</span></td>';
                    html+='<td>'+media+'</td>';
                    html+='<td>'+response.reports[report][media].total_response+'</td>';
                    html+='<td>'+response.reports[report][media].positive+'</td>';
                    html+='<td>'+response.reports[report][media].replies_to_message_out_total+'%</td>';
                    html+='<td>'+response.reports[report][media].positive_reply+'%</td>';
                    html+='<td>'+response.reports[report][media].lead+'</td>';
                    html+='</tr>';
                }
             } */
             $('#reportTable tbody .lead-generation-table-row').remove();
             $('#reportTable tbody').append(response.report_data.responses);
        },
        error: function(xhr, status, error) {
            console.log(xhr.responseText);
            console.log(status);
            console.log(error);
          }
    })
}
 
//Ajax to return data from BE for selected filter
function callDataFromBakEnd(date, report_name) {
    var report_name_sec = report_name;
    var report_name_helper = report_name;
    if(report_name == "lead_generation_metrics_report_lead"){
        report_name = "lead_generation_metrics_report";
    }

    var selectedFilters = getSelectedFilter(report_name_helper, false);
    
    var channel_custom_field_id = jQuery("[name='channel_custom_field_id']").val();
    jQuery('.loader').css('display', 'block');
    jQuery('.page-loader').delay(350).css('display', 'block');
    var subFilter = jQuery(`body .${report_name} .sub-filters`).clone();
    var leadOwnerIndex = $(`#Lead_Owner_${report_name}`).prop('selectedIndex');
    var campaignIndex = $(`#Campaign_${report_name}`).prop('selectedIndex');
    var dateIndex = $(`#datepicker_${report_name}`).prop('selectedIndex');
    console.log("DA3");
    jQuery.ajax({
        url: close_ajax_report_object.ajax_url,
        data: {
            data: selectedFilters,
            action: report_name,
            channel_custom_field_id
        },
        type: 'GET',
        dataType: 'html',
        success: function (response) {
            var report_name_helper = report_name;
            if(report_name == "lead_generation_metrics_report_lead"){
                report_name = "lead_generation_metrics_report";
            }
            jQuery('.' + report_name).html(response);
            jQuery(`body .${report_name} .sub-filters-container`).html('');
            jQuery(`body .${report_name} .sub-filters-container`).html(subFilter); 
            jQuery(`body #Lead_Owner_${report_name}`).prop('selectedIndex', leadOwnerIndex);
            jQuery(`body #Campaign_${report_name}`).prop('selectedIndex', campaignIndex);
            jQuery(`body #datepicker_${report_name}`).prop('selectedIndex', dateIndex);
            updateFiltersApplied(report_name);
            if (report_name === "lead_generation_metrics_report" || report_name === "lead_generation_metrics_report_lead" || report_name === "pipeline_overview_report") {
                var selectedFilters = getSelectedFilter(report_name_helper);
                renderChipFilter(report_name, selectedFilters);
                jQuery('.select-channel').selectpicker('render');
                jQuery(".datepicker_" + report_name + "_input").removeClass("hasDatepicker");
                jQuery(".datepicker_" + report_name + "_input").datepicker("destroy");
                jQuery(".datepicker_" + report_name + "_input").removeAttr("id");
                init_date_picker(report_name);
                init_date_picker_lead(report_name_helper);
                init_date_picker_leadtab(report_name_helper);
                jQuery(".datepicker_" + report_name + "_input").datepicker("refresh");

            }

            hideLoader();
        },
        error: (error) => {
            console.log(error);
        }
    })
}
//Ajax to return data from BE for selected filter on sales report
function callDataFromBakEndForSalesProcess (date, report_name, report_name_parent) {
    var selectedFilters = getSelectedFilter(report_name, report_name_parent);
    var channel_custom_field_id = jQuery("[name='channel_custom_field_id']").val();
    var columnNumber = parseInt(report_name_parent.split('_')[1]);

    jQuery('.loader').css('display', 'block');
    jQuery('.page-loader').delay(350).css('display', 'block');
    console.log("44123");
    jQuery.ajax({
        url: close_ajax_report_object.ajax_url,
        data: {
            data: selectedFilters,
            action: report_name,
            channel_custom_field_id
        },
        type: 'GET',
        dataType: 'html',
        success: function (response) {
            updateFilterColumn(JSON.parse(response), columnNumber);
            init_date_picker(report_name, columnNumber);
            jQuery('.datepicker_' + report_name + '_input_' + columnNumber).removeClass("hasDatepicker");
            jQuery('.datepicker_' + report_name + '_input_' + columnNumber).datepicker("destroy");
            jQuery('.datepicker_' + report_name + '_input_' + columnNumber).removeAttr("id");
             jQuery('.datepicker_' + report_name + '_input_' + columnNumber).datepicker("refresh");
            hideLoader();
        },
        error: (error) => {
            console.log(error);
        }
    })
}

//Update number of filter applied
function updateFiltersApplied (report_name) {
    var numberOfFilterChecked = 0;
    if (jQuery(`#ofWrittenOutreach_${report_name} input[type="number"]`).length && jQuery(`#ofWrittenOutreach_${report_name} input[type="number"]`).val() !== "")
        numberOfFilterChecked++;
    if (jQuery(`body #date_${report_name}`).length && jQuery(`body #date_${report_name}`).val() !== "")
        numberOfFilterChecked++;
    if (jQuery(`body #Lead_Owner_${report_name}`).length && jQuery(`body #Lead_Owner_${report_name}`).val() !== "")
        numberOfFilterChecked++;
    if (jQuery(`body #Campaign_${report_name}`).length && jQuery(`body #Campaign_${report_name}`).val() !== "")
        numberOfFilterChecked++;
    jQuery(`#report_filter_${report_name} input[type='radio']`).each(function () {
        if (jQuery(this).is(':checked'))
            numberOfFilterChecked++;
    });
    jQuery(`.filters-applied_${report_name}`).html(numberOfFilterChecked + ' filters applied');
}

//Get selected filter from DOM
function getSelectedFilter (report_name, report_name_parent) {
    if (report_name === "lead_generation_metrics_report_leadtab"){
        var helper_class = ".datepicker_lead_generation_metrics_report_leadtab_input";
    }
    if(report_name === "lead_generation_metrics_report_lead"){
        var helper_class = ".datepicker_lead_generation_metrics_report_lead_input";
    } 
    else{
        var helper_class = ".datepicker_lead_generation_metrics_report_input";
    }

    
    var selectedFilters = new Array();
    if (!report_name_parent) {
        
        jQuery(`
        `+helper_class+`,
        #report_filter_${report_name} input:not([type=hidden]),
        #report_filter_${report_name} [type='radio']:checked,
        #report_filter_${report_name} select,
        .${report_name} .sub-filters select`).map((__, item) => {
            
            var $item = jQuery(item);
            var type = $item.attr('type');
            var name = $item.attr('name');
            
            if (type !== 'submit') {
                var value = item.value;
                
                if (value) {
                    var selectedFilter = { name, value };
                    if(value !="First Outreach Date") {
                        selectedFilters.push(selectedFilter);
                    }
                }
            }
        });
    } else {
        var columnNumber = parseInt(report_name_parent.split('_')[1]);
        jQuery(`.datepicker_${report_name}_input_${columnNumber}, #${report_name_parent} #report_filter_${report_name} input:not([type=hidden]),#${report_name_parent} #report_filter_${report_name} select`).map((__, item) => {
            var $item = jQuery(item);
            var type = $item.attr('type');
            var name = $item.attr('name');
            if (type !== 'submit') {
                var value = item.value;
                if (value) {
                    var selectedFilter = { name, value };
                    if(value !="First Outreach Date") {
                        selectedFilters.push(selectedFilter);
                    }
                }
            }
        });
    }

    return selectedFilters;
}
function getNum(val) {
    if (isNaN(val)) {
        return 0;
    }
return val;
}
 //Update filter column
 function updateFilterColumn (response, columnNumber) {
    $('#report-table tbody tr').each((_, row) => {
        var current_element_label_DOM = $(row).attr('class');
        var current_element_value_response;

        var opportunity_values = response['opportunity_list'];
        if(opportunity_values != null) {
            for (current_element in opportunity_values) {
                if (current_element) {
                    var opportunity = opportunity_values[current_element];
                    var opportunity_value = jQuery('#' + current_element).children()[getNum(columnNumber)];
                    opportunity_value.innerText = opportunity['count'];
                }
            }
        }else {
            jQuery('#report-table > tbody  > tr').each(function(index, tr) { 
                if(index != 0) {
                    var opportunity_value = jQuery(this).children()[getNum(columnNumber)];
                    opportunity_value.innerText = '0';
                }
             });
        }
    });
}
