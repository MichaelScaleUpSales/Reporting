<?php 
function retrieve_generation_metrics_report_data($selectedFilters, $userId, $channel_custom_field_id = null, $isCronJob = false) {
    // Retrieve Report caching Data
    $report_name = "lead-generation-metrics-report";
    $report_cache_data = retrieve_report_caching_data($report_name, $userId);

    if(!$isCronJob) {
        //then user hit the report page
        if(empty($report_cache_data) || !empty($report_cache_data)) {
            
            $report_data = retrieve_generation_metrics_report_data_from_close_api($selectedFilters, $userId, $channel_custom_field_id);
           
            if(empty($report_cache_data)){
                caching_report_data($report_name, $report_data, $userId, $channel_custom_field_id);
            }
            return $report_data;

        } else {
            return $report_cache_data->report_data;
        }

    } else {
        //cron job running
        $report_data = retrieve_generation_metrics_report_data_from_close_api($selectedFilters, $report_cache_data->user_id, $report_cache_data->channel_custom_field_id);
        clear_report_caching_data($report_name, $report_cache_data->user_id);
        caching_report_data($report_name, $report_data, $userId, $report_cache_data->channel_custom_field_id);
        return $report_data;
    }

}

function retrieve_generation_metrics_report_data_from_close_api($selectedFilters, $user_Id, $channel_custom_field_id){
    
    $total_positive_response = 0;
    $total_response = 0;
    $total_leads = 0;
    $positive_response_rate_out_of_total_response = 0;
    $positive_response_rate_out_of_total_companies = 0;
    $response_rate = 0;
    $is_filters_contain_number_outreach = false;

    // The total companies that have a channel (the total of lead companies.).
    $global_total_companies = 0;

    $channels_data = array();

    if(!empty($selectedFilters)) {
        foreach ($selectedFilters as $filters) {
            // Check if the filtering is done based on the number of written outreach.
            if($filters['name'] === constant('NUMBER_OF_WRITTEN_OUTREACH')){
                $is_filters_contain_number_outreach = true;
                break;
            }
        }
    }

    $api_key = get_api_key($user_Id);
    $custom_CONSTANT = constant('CUSTOM');

    if ($channel_custom_field_id) {
        // To get all choices for "the First Response Medium" custom field that have channel choices
        $response = wp_remote_get( constant('CLOSE_API_PATH') . constant('CLOSE_API_LEAD_CUSTOM_FIELDS') . $channel_custom_field_id . '/' ,
            array('timeout' => 3600 , 'headers' => array( 'Authorization' => 'Basic ' . base64_encode( $api_key ))
        ));

        
        $body = wp_remote_retrieve_body( $response );
        $data_api = json_decode($body, true);
        
        //	Get all organization leads for each channel
        if (!empty($data_api['choices'])) {
            $total_companies_list = get_lead_call_api($selectedFilters, 0, '', 'id', $user_Id);
            $global_total_companies = $total_companies_list['total_results'];
            foreach ($data_api['choices'] as $channel) {
                $channel_data = array();
                $startIndex = 0;
                $limit = 200;
                $all_data_list = array();
                $tries = 0;
                do {
                    
                    $channel_query = "\"" . $custom_CONSTANT . "." . constant('FIRST_RESPONSE_MEDIUM') . "\":\"" . $channel . "\" ";
                    
                    
                    $data_api = get_lead_call_api($selectedFilters, $startIndex, $channel_query, constant('LEAD_DATA_FIELDS_REPORT'), $user_Id);
                    $total_companies = $data_api['total_results'];


                    $has_more = $data_api['has_more'];

                    
                    
                    if (!empty($data_api['data'])) {
                        $all_data_list = array_merge($all_data_list, $data_api['data']);
                    }

                    if ($has_more) {
                        // Check if there's more leads to get it.
                        $startIndex = $startIndex + $limit;
                    }
                } while ($has_more);
                
                // Check if the channel has data or not
                if(!empty($all_data_list)) {
                    // To get each organization record
                    for ($record = 0; $record < count($all_data_list); $record++) {

                        // Check if the organization has custom fields or not
                        if (!empty($all_data_list[$record][$custom_CONSTANT])) {
                            //To get all organization custom fields
                            $organization_custom_fields = $all_data_list[$record][$custom_CONSTANT];
                            $number_of_written_outreach_CONSTANT = constant('NUMBER_OF_WRITTEN_OUTREACH');
                            $first_response_sentiment_CONSTANT = constant('FIRST_RESPONSE_SENTIMENT');
                            $qualified_lead_CONSTANT = constant('QUALIFIED_LEAD');

                            // Check if the organization has "# of Written Outreach" and "First Response Sentiment" custom fields.
                            if (array_key_exists($number_of_written_outreach_CONSTANT, $organization_custom_fields)) {
                                $outreach_number = $organization_custom_fields[$number_of_written_outreach_CONSTANT];

                                // Calculate the total number of leads (which have "Qualified Lead?" custom field).
                                if (array_key_exists($qualified_lead_CONSTANT, $organization_custom_fields)) {
                                    if (in_array(constant('QUALIFIED_LEAD_MARKED_VALUE'),$organization_custom_fields[$qualified_lead_CONSTANT])) {
                                        $total_leads++;
                                    }
                                }
                                // To check if "the number of written outreach" key found for the first time or not
                                if (!array_key_exists($outreach_number, $channel_data)) {
                                    $channel_data[$outreach_number]= array('total_response'=> 0,'number_of_positive_responses'=> 0
                                    ,'number_of_negative_responses'=> 0, 'positive_response_percent'=> 0 , 'negative_response_percent'=> 0
                                    , 'percent_of_responses'=> 0, 'number_of_leads'=> 0);
                                }

                                // Increase the number of leads (Calculate the total number of leads (which have "Qualified Lead?" custom field)).
                                if (array_key_exists($qualified_lead_CONSTANT, $organization_custom_fields)) {
                                    if (in_array(constant('QUALIFIED_LEAD_MARKED_VALUE'),$organization_custom_fields[$qualified_lead_CONSTANT])) {
                                        $channel_data[$outreach_number]['number_of_leads']++;
                                    }
                                }

                                if(array_key_exists($first_response_sentiment_CONSTANT, $organization_custom_fields) ) {

                                    // Increase the number of response
                                    $channel_data[$outreach_number]['total_response']++;

                                    $total_response++;

                                    $positive_CONSTANT = constant('POSITIVE');
                                    $negative_CONSTANT = constant('NEGATIVE');
                                    if ($organization_custom_fields[$first_response_sentiment_CONSTANT] === $positive_CONSTANT) {
                                        // Check if the "first response sentiment" value was a Positive value
                                        $channel_data[$outreach_number]['number_of_positive_responses']++;
                                        $total_positive_response++;
                                    } else if ($organization_custom_fields[$first_response_sentiment_CONSTANT] === $negative_CONSTANT) {
                                        // Check if the "first response sentiment" value was a Negative value
                                        $channel_data[$outreach_number]['number_of_negative_responses']++;
                                    }
                                }
                            }
                        }
                    }
                    // Sorting the channel by key (# of Written Outreach).
                    ksort($channel_data);

                    // To calculate the percent for Positive Response, Negative Response, and all responses for each channel.
                    foreach ($channel_data as $key => $value) {
                        if ($channel_data[$key]['total_response']) {
                            $channel_data[$key]['positive_response_percent'] = ($channel_data[$key]['number_of_positive_responses'] / $channel_data[$key]['total_response'])*100;
                            $channel_data[$key]['negative_response_percent'] = ($channel_data[$key]['number_of_negative_responses'] / $channel_data[$key]['total_response'])*100;
                        }
                    }

                }

                // To gather all channels data in one table
                $channels_data[$channel] = $channel_data;
            }

        }

        // Calculate 'Positive response rate out of total responses' and 'Positive response rate out of total companies' for all channels.
        $positive_response_rate_out_of_total_response = $total_response ? ($total_positive_response/$total_response) * 100 : 0;
        $positive_response_rate_out_of_total_companies = $total_positive_response ? ($total_positive_response/$global_total_companies) * 100 : 0;
        $response_rate = $total_response ? ($total_response/$global_total_companies) * 100 : 0;
        

        //Caching Report Data in database 
        $report_data = array(
            "global_total_companies" => $global_total_companies,
            "total_response" => $total_response,
            "total_positive_response" => $total_positive_response,
            "response_rate" => $response_rate,
            "positive_response_rate_out_of_total_response" => $positive_response_rate_out_of_total_response,
            "positive_response_rate_out_of_total_companies" => $positive_response_rate_out_of_total_companies,
            "total_leads" => $total_leads,
            "is_filters_contain_number_outreach" => $is_filters_contain_number_outreach,
            "channels_data" => json_encode($channels_data),
        );

        return json_encode($report_data);

    }

}