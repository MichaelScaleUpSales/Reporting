<?php 
function retrieve_pipeline_overview_report_data($selectedFilters, $userId, $isCronJob = false) {
    // Retrieve Report caching Data
	$report_name = "pipeline-overview-report";
    $report_cache_data = retrieve_report_caching_data($report_name, $userId);
    if(!$isCronJob) {
        //then user hit the report page
        if(empty($report_cache_data) || !empty($selectedFilters)) {
            $report_data = retrieve_pipeline_overview_report_data_from_close_api($selectedFilters, $userId);
            if(empty($selectedFilters)){
                caching_report_data($report_name, $report_data, $userId, "");
            }
            return $report_data;

        } else {
            return $report_cache_data->report_data;
        }

    } else {
        //cron job running
        $report_data = retrieve_pipeline_overview_report_data_from_close_api($selectedFilters, $report_cache_data->user_id);
        clear_report_caching_data($report_name, $report_cache_data->user_id);
        caching_report_data($report_name, $report_data, $userId, "");
        return $report_data;
    }

}
function retrieve_pipeline_overview_report_data_from_close_api($selectedFilters, $userId){
    $startIndex = 0;
    $limit = 200;
    $all_data_list = array(); // all leads companies 
    $opportunities_list = array();

        do {
            $leads_companies = get_lead_call_api($selectedFilters, $startIndex, '', constant('PIPELINE_AND_SALES_DATA_FIELDS_REPORTS'), $userId);

            $has_more = $leads_companies['has_more'];

            if (!empty($leads_companies['data'])) {
                $all_data_list = array_merge($all_data_list, $leads_companies['data']);
            }

            if ($has_more) {
                $startIndex = $startIndex + $limit;
            }

        } while ($has_more);


        $no_demo_set_opportunity = 0;

        // Check if there are leads data or not
        if (!empty($all_data_list)) {

            // To get each organization record
            $opportunities_list = get_opportunity_for_leads($all_data_list, $userId);
        }

        $report_data = array(
            "all_leads_companies" => json_encode($all_data_list),
            "opportunities_list" => json_encode($opportunities_list),
        );

        return json_encode($report_data);
     
}
?>