<?php
function get_custom_fields ($report_name) {

    $user_id = get_current_user_id();
    $lead_custom_fields = get_user_meta($user_id, constant("USER_META_LEAD_CUSTOM_FIELD"))[0];
    $contact_custom_fields = get_user_meta($user_id, constant("USER_META_CONTACT_CUSTOM_FIELD"))[0];
   
    
    if (empty($lead_custom_fields) && empty($contact_custom_fields)) {
        // Get the custom fields if the not exist at database for the current user.
        get_customer_custom_fields(true, wp_get_current_user());
        $lead_custom_fields = get_user_meta($user_id, constant("USER_META_LEAD_CUSTOM_FIELD"))[0];
        $contact_custom_fields = get_user_meta($user_id, constant("USER_META_CONTACT_CUSTOM_FIELD"))[0];
    }
    if ($report_name === 'lead_generation_metrics_report') {
        // Show the lead custom fields.
        show_custom_fields($lead_custom_fields,$report_name, true);
    } else {

        $lead_custom_fields["Campaign"] = array(
            "choices" =>get_campaigns(),
            "accepts_multiple_values" => '',
            "name" => 'Campaign',
            "updated_by" => 'user_Ws3C2c8wXEtUDNIBlAZdHEtSqkoOSqVOuJcT9foRdb2',
            "created_by" => 'user_JR3Uxgw5VCnRIAW7cxnWBc3L8thdALWs8Xe2PwMuiuA',
            "organization_id" => 'orga_K85ZQzsf04SyetPIITJEM5yPU9INENTZXP2Dpv9t9bT',
            "id" => "cf_Guj3PMQrOjVjROphpIHqJWp6s50KJXLjSFYWcm3oRgY",
        );

    ?>
    <form id="report_filter_<?php echo $report_name;?>" data-report-name="<?php echo $report_name;?>" class="report__filter">
        <!-- <h5 class="font-weight-bold mb-3">Filters</h5> -->
        <div class="d-flex justify-content-between">
        <!-- <h6 class="filters-applied_<?php echo $report_name;?> text-muted" data-report-name="<?php echo $report_name;?>">0 filters applied</h6> -->
        </div>
        <?php // Add hidden input to save the report name in it and add the submit button to the filters form. ?>
        <input type="hidden" id="report-name" name="report-name" value="<?php echo $report_name; ?>">
        <?php
            // Show the lead custom fields.
            show_custom_fields($lead_custom_fields,$report_name);
            // Show the contact custom fields.
            show_custom_fields($contact_custom_fields,$report_name);
        ?>
        <div class="report_submit_buttons_container">
            <a class="reset<?php if($report_name === 'sales_process_metrics_report') {?>-scale<?php } ?> h-hand" data-report-name="<?php echo $report_name;?>">Reset</a>
            <input class="btn-apply btn-apply_report_filter_<?php echo $report_name; ?>" type="submit" name="filter" value="Apply" />
        </div>
        </form><?php } ?>
<?php
}


// Sort the company size options.
function sort_array($first_value, $second_value) {
    $split_first_value = explode('-', $first_value);
    $split_second_value = explode('-', $second_value);
    $first_value = (int)($split_first_value[0]);
    $second_value = (int)($split_second_value[0]);

    if ($first_value == $second_value) {
        return 0;
    }
    return ($first_value < $second_value) ? -1 : 1;
}

/**
 * Show the custom fields filters.
 */
function show_custom_fields($custom_fields,$report_name) {
    
    
    global $channel_custom_field_id;
    $default_filters = constant('DEFAULT_FILTERS_LIST');

    
    $ordered_custom_fields = array();
    // Order filter list
    $custom_fields_order = constant('CUSTOM_FIELDS_ORDER');
    array_unshift($custom_fields_order, "Campaign");
    array_unshift($default_filters, "Campaign");
    // $custom_fields_order[] = "Campaign";
    // $default_filters[] = "Campaign";

    
    
    
    
    foreach ($custom_fields_order as $custom_field_item) {
        $ordered_custom_fields[$custom_field_item] = $custom_fields[$custom_field_item];
    }

    
    foreach ($ordered_custom_fields as $custom_field) {
        
        // Check if the custom field is among the filters which will display
       if (in_array($custom_field['name'], $default_filters)) {
            
        // Get the custom field attributes.
            $type = $custom_field['type'];
            $id = $custom_field['id'];
            $name = $custom_field['name'];
            $report = $filter['report-name'];
            // To collect the filters names
            array_push($GLOBALS['filters_names'], $name);
            $last_collapse;

            
            // Save "First Response Medium" id
            if ($name === constant('FIRST_RESPONSE_MEDIUM')) {
                $channel_custom_field_id = $id;
                ?>
                    <input type="hidden" value="lcf_5UhysDn33434jT7sfMxRmnVZAljTNig9Z8Iw5fOJJOV" name="channel_custom_field_id" />
                <?php
            }
            
            if (($name != constant('FIRST_OUTREACH_DATE') && $report_name === 'lead_generation_metrics_report')) {
               // Silent
               
            } else if ($type === 'choices' or (($type === 'text' or $type === 'user') and  $name != constant('FIRST_OUTREACH_DATE'))) {
                
                // Check if the custom field is a select drop down list or if the custom field type is different from the 'choices' type to get all values for it.
                if(array_key_exists('choices', $custom_field)) {
                    $choices = $custom_field['choices'];
                    
                } else {
                    $choices = array();
                }

                
                $nameWithoutSpace = str_replace(' ', '_',$name);
                if(!empty($choices)){
                    if ($name === constant('COMPANY_SIZE')) {
                        usort($choices, "sort_array");
                    }
                }
                ?>
                    <div class="form-group m-0 mt-2">
                            <select name="<?php echo $name; ?>" class="form-control h-hand select-input-scale selectpicker" id="<?php echo $nameWithoutSpace.'_'.$report_name;?>">
                            <option value=""><?php echo $name; ?></option>
                            <?php foreach ($choices as $choice) {
                                if (!($name === constant('EXCLUDE_ITEM_FROM_SELECT_LIST')['key'] && $choice === constant('EXCLUDE_ITEM_FROM_SELECT_LIST')['value'])) {
                                    ?>
                                    <option value="<?php echo $choice; ?>"><?php echo $choice; ?></option>
                                    <?php
                                }
                            } ?>
                            </select>
                    </div>
                <?php
            } else if ($name === constant('FIRST_OUTREACH_DATE') && $report_name === 'lead_generation_metrics_report') { // Check if the custom field is date for lead_generation_metrics_report
                
                ?>
                <script>
                jQuery(document).ready(function () {
                        jQuery('body .<?php echo $report_name ;?> .sub-filters').append(`
                            <div class="lead_generation_metrics_report_filters">
                            <div id="datepicker_lead_generation_metrics_report" class="datepicker_lead_generation_metrics_report">
                            <input type="text" name="date" report-name="lead_generation_metrics_report" class="datepicker datepicker_lead_generation_metrics_report_input" value="First Outreach Date"/><i class="datepicker_icon"></i>
                            </div>
                        </div>`);

                        /* jQuery('body .<?php echo $report_name ;?> .sub-filters ').append(`
                            <div class="lead_generation_metrics_report_filters_lead">
                                <div id="datepicker_lead_generation_metrics_report_lead" class="datepicker_lead_generation_metrics_report_lead">
                            <input type="text" name="date-lead" report-name="lead_generation_metrics_report_lead" class="datepicker datepicker_lead_generation_metrics_report_lead_input" value="Leads"/><i class="datepicker_icon"></i>
                            </div>
                        </div>`); */
                        

                }); 
                var lead_report_start_date;
                var lead_report_end_date;
                    jQuery(function() {
                        init_date_picker("<?php echo $report_name ;?>");
                        //init_date_picker_lead("lead_generation_metrics_report");
                    });

                    
                </script>
                <?php
            }
            
            
            
            
            else if ($name === constant('FIRST_OUTREACH_DATE') && $report_name == 'sales_process_metrics_report') { // Check if the custom field is date for other reports.
                
                ?>
                <div class="form-group m-0 mt-2"><div id="datepicker_<?php echo $report_name;?>" name="daterange" class="datepicker_lead_generation_metrics_report">
                <input type="text" name="date" report-name="lead_generation_metrics_report" class="datepicker datepicker_<?php echo $report_name;?>_input datepicker_<?php echo $report_name;?>_input_1" value="First Outreach Date"/><i class="datepicker_icon"></i></div></div>
                <script>
                var reportName = "<?php echo $report_name?>";
                var startDate;
                var endDate;
                init_date_picker(reportName, 1);
                </script>
                <?php
            }  else if ($name === constant('FIRST_OUTREACH_DATE') && $report_name != 'sales_process_metrics_report' && $report_name != 'lead_generation_metrics_report') { // Check if the custom field is date for other reports.
                
                ?>
                <div class="form-group m-0 mt-2"><div id="datepicker_<?php echo $report_name;?>" name="daterange" class="datepicker_lead_generation_metrics_report">
                <input type="text" name="date" report-name="lead_generation_metrics_report" class="datepicker datepicker_<?php echo $report_name;?>_input" value="First Outreach Date"/><i class="datepicker_icon"></i></div></div>
                <script>
                var reportName = "<?php echo $report_name?>";
                var startDate;
                var endDate;
                jQuery(function() {
                        jQuery(`#datepicker_${reportName} input`).daterangepicker({
                            autoUpdateInput: false,
                            locale: {
                                cancelLabel: 'Clear'
                            }
                        });


                        jQuery(`#datepicker_${reportName} input`).on('apply.daterangepicker', function(ev, picker) {
                            let value = picker.startDate.format('YYYY-MM-DD') + ' to ' + picker.endDate.format('YYYY-MM-DD');

                            jQuery(`#datepicker_${reportName} input`).val(value);
                            requestFilter(reportName, '');
                        });

                        jQuery(`#datepicker_${reportName} input`).on('cancel.daterangepicker', function(ev, picker) {
                            jQuery(`#datepicker_${reportName} input`).val('');
                        });
                    });
                </script>
                <?php
            }else{
                $choices = $custom_field['choices'];
                ?>
                <div class="form-group m-0 mt-2">
                        <select name="<?php echo $name; ?>" class="form-control h-hand select-input-scale selectpicker" id="<?php echo $nameWithoutSpace.'_'.$report_name;?>">
                        <option value=""><?php echo $name; ?></option>
                        <?php foreach ($choices as $choice) {
                            if (!($name === constant('EXCLUDE_ITEM_FROM_SELECT_LIST')['key'] && $choice === constant('EXCLUDE_ITEM_FROM_SELECT_LIST')['value'])) {
                                ?>
                                <option value="<?php echo $choice; ?>"><?php echo $choice; ?></option>
                                <?php
                            }
                        } ?>
                        </select>
                </div>
                <?php
            }
        }
    }
}

/**
 * Get the text custom fields
 */
function filter_custom_fields($custom_fields) {
    $default_custom_field_details = array();
    global $text_custom_Fields;
    $text_custom_Fields = array();
    global $user_custom_Fields;
    $user_custom_Fields = array();
    $default_filters = constant('DEFAULT_FILTERS_LIST');
    if (!empty($custom_fields)) {
        foreach ($custom_fields as $custom_field) {
            $name = $custom_field['name'];
            // Check if the custom field is among the filters which will display
            if (in_array($name, $default_filters)) {
                $type = $custom_field['type'];
                if ($type === 'text') {
                    array_push($text_custom_Fields, $name);
                } else if($type === 'user') {
                    array_push($user_custom_Fields, $name);
                }
                $default_custom_field_details[$name] = $custom_field;
            }
        }
    }
    return $default_custom_field_details;
}

/**
 * Display the lead custom fields.
 */
function lead_custom_fields() {
    $lead_custom_fields = get_leads_cutsom_fields();
    $default_custom_field_details = filter_custom_fields($lead_custom_fields);
    $default_custom_field_list = get_text_Custom_Fields_choices($default_custom_field_details);
    return $default_custom_field_list;
}

/**
 * Get the lead custom fields.
 */
function get_leads_cutsom_fields () {
    // Get lead custom fields.
    $response = wp_remote_get( constant('CLOSE_API_PATH') . constant('CLOSE_API_LEAD_CUSTOM_FIELDS') ,
        array('timeout' => 10 , 'headers' => array( 'Authorization' => 'Basic ' . base64_encode( get_api_key() ))
    ));

	$body     = wp_remote_retrieve_body( $response );
	$data_api = json_decode($body, true);

    if (array_key_exists('data', $data_api)) {
        $data = $data_api['data'];
    } else {
        $data = array();
    }

	return $data;
}

/**
 * Display the contact custom fields.
 */
function contact_custom_fields() {
    $contact_custom_fields = get_contacts_custom_fields();
    $default_custom_field_details = filter_custom_fields($contact_custom_fields);
    $default_custom_field_list = get_text_Custom_Fields_choices($default_custom_field_details);
    return $default_custom_field_list;
}

/**
 * Get the contact custom fields.
 */
function get_contacts_custom_fields () {
    // Get lead custom fields.
    $response = wp_remote_get( constant('CLOSE_API_PATH') . constant('CLOSE_API_CONTACT_CUSTOM_FIELDS') ,
        array('timeout' => 10 , 'headers' => array( 'Authorization' => 'Basic ' . base64_encode( get_api_key() ))
    ));

	$body     = wp_remote_retrieve_body( $response );
	$data_api = json_decode($body, true);

    if (array_key_exists('data', $data_api)) {
        $data = $data_api['data'];
    } else {
        $data = array();
    }

	return $data;
}