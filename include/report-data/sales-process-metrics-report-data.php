<?php 
function retrieve_sales_process_metrics_report_data($selectedFilters, $userId, $isCronJob = false) {
    // Retrieve Report caching Data
    

    
    $report_name = "sales-process-metrics-report";
    $report_cache_data = retrieve_report_caching_data($report_name, $userId);
   
    if(!$isCronJob) {
        //then user hit the report page
        if(empty($report_cache_data) || !empty($selectedFilters)) {
            
       
            $report_data = retrieve_sales_process_metrics_report_data_from_close_api($selectedFilters, $userId);

            if(empty($selectedFilters)) {
                caching_report_data($report_name, $report_data, $userId, "");
            }
            return $report_data;
            
        } else {
            $data = get_opportunity_list('',$userId);
            return  json_encode($data);
        }

    } else {
        //cron job running
        $report_data = retrieve_sales_process_metrics_report_data_from_close_api($selectedFilters, $report_cache_data->user_id);
        clear_report_caching_data($report_name, $report_cache_data->user_id);
        caching_report_data($report_name, $report_data, $userId, "");
        return $report_data;
    }

}

function retrieve_sales_process_metrics_report_data_from_close_api($selectedFilters, $userId) {
        $opportunities = array();
        
        $opportunities = get_opportunity_list($selectedFilters, $userId);

        return json_encode($opportunities);
}

?>