<?php 

function fill_caching_data(){
    // lead generation metrics report
    $report_name = "lead-generation-metrics-report";
    $leadGenerationMetricsReportSelectedFilters = array (
        0 => 
        array (
          'name' => 'Lead Source',
          'value' => 'ScaleUpSales lead',
        ),
    );

    $user_query = retrieve_report_caching_users($report_name);
    foreach ( $user_query as $userId) {
        retrieve_generation_metrics_report_data($leadGenerationMetricsReportSelectedFilters, $userId->user_id, null, true);

    }

    // pipeline overview report
    $report_name = "pipeline-overview-report";
    $pipelineOverviewReportSelectedFilters = null;
    $user_query = retrieve_report_caching_users($report_name);
    foreach ( $user_query as $userId) {
        retrieve_pipeline_overview_report_data($pipelineOverviewReportSelectedFilters, $userId->user_id, true);

    }

    // sales process metrics report
    $salesProcessMetricsSelectedFilters = null;
    $report_name = "sales-process-metrics-report";
    $user_query = retrieve_report_caching_users($report_name);
    foreach ( $user_query as $userId) {
        retrieve_sales_process_metrics_report_data($salesProcessMetricsSelectedFilters, $userId->user_id, true);

    }
}