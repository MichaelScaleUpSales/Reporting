<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

// Get the extension selectors.
add_action( 'wp_ajax_pipeline_overview_report', 'pipeline_overview_report_ajax_call_back' );

function pipeline_overview_report_view($no_demo_set_opportunity, $opportunities, $no_demo_set_companies) {
    $all_companies = array();
    //$all_companies_ordered['Interested - No Demo Set'] = $no_demo_set_companies;
    // Get the order of the opportunity statuses will be shown in the table.
    $opportunity_statuses_order = constant('OPPORTUNITY_STATUSES_ORDER');
    $opportunity_in_configured_arrangement = array();
    $opportunity_not_in_configured_arrangement = array();
    foreach ($opportunities as $opportunity => $opportunity_data) {
        $all_companies[$opportunity_data['label']] = $opportunity_data['companies_names'];
        // Collect the opportunity statuses which are not in the configured arrangement.
        if(!in_array($opportunity_data['label'], $opportunity_statuses_order)) {
            $opportunity_not_in_configured_arrangement[$opportunity_data['label']] = $opportunity_data['companies_names'];
        }
    }
    // Rearrange the opportunity statuses based on the configured arrangement.
    foreach ($opportunity_statuses_order as $opportunity_status) {
        if (array_key_exists($opportunity_status, $all_companies)) {
            $opportunity_in_configured_arrangement[$opportunity_status] = $all_companies[$opportunity_status];
        }
    }

    // Merge the opportunities [Which are in the configured arrangement and which are not in the configured arrangement] in one array.
    //$all_companies_ordered= $all_companies_ordered + array_merge($opportunity_in_configured_arrangement, $opportunity_not_in_configured_arrangement);
    $all_companies_ordered = array_merge($opportunity_in_configured_arrangement, $opportunity_not_in_configured_arrangement);
    
	?>
	<div class="pipeline_overview_report d-flex w-100">
        <ul class="nav d-flex pipeline-nav m-0 col-4 col-xl-3 flex-column" id="pipelineTabs" role="tablist">
            <?php
            foreach($all_companies_ordered as $opportunity => $companies) {
                $opportunityWithoutSpace = str_replace(' ', '_',$opportunity);
                $LABELS_CONSTANTS = constant('CHANGE_OPPORTUNITY_STATUSES_LABEL');

                ?>
                <li class="nav-item pipeline-nav-item">
                    <a class="nav-link pipeline-nav-link<?php if($opportunity == 'Interested - No Demo Set' || $opportunity == 'Demo Set') echo ' active';?>" data-toggle="tab" id="<?php echo $opportunityWithoutSpace;?>-tab" href="#<?php echo $opportunityWithoutSpace; ?>" role="tab" aria-selected="true">
                        <div class="d-flex justify-content-between">
                            <span class="company-label-tab"><?php if($LABELS_CONSTANTS[$opportunity]) { echo $LABELS_CONSTANTS[$opportunity]; } else { echo $opportunity; } ; ?></span>
                            <span class="company-count d-flex align-items-center"><?php echo count($companies) ?></span>
                        </div>
                    </a>
                </li>
                <?php } ?>
        </ul>
        <div class="tab-content col-7 col-xl-9">
        <div class="sub-filters-container"><div class="sub-filters d-flex my-2 justify-content-center flex-wrap"></div></div>
                <div class="filter-chips-container"></div>
            <?php
        foreach($all_companies_ordered as $opportunity => $companies) {
            $opportunityWithoutSpace = str_replace(' ', '_',$opportunity);
            ?> <div class="search-organization tab-pane<?php if($opportunity == 'Interested - No Demo Set' || $opportunity == 'Demo Set') echo ' active';?>" id="<?php echo $opportunityWithoutSpace;?>" role="tabpanel" aria-labelledby="<?php echo $opportunityWithoutSpace;?>-tab">
                    <?php if( count($companies) ){?><input class="search-filter" type="text" placeholder="Search by company name ..."> <?php }?>
                    <?php if( !count($companies) ){?><h2 class="text-center mt-4">No data found</h2><?php }?>
                    <br/>
                    <div class="company-list d-flex pl-4">
            <?php
                    foreach($companies as $key => $company) {?>
                        <span class="company-label"> <?php echo $company; ?> </span>
                <?php }?>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
	<?php
}

function pipeline_overview_report_data($selectedFilters) {

    $report_json_data = retrieve_pipeline_overview_report_data($selectedFilters, get_current_user_id());

    $report_data = json_decode($report_json_data);
    $all_data_list = json_decode($report_data->all_leads_companies, true);


    // Check if there are leads data or not
    if (!empty($all_data_list)) {
        $opportunities_list = json_decode($report_data->opportunities_list, true);

        $no_demo_set_opportunity = $opportunities_list['no_demo_set_opportunity'];
        $opportunity = $opportunities_list['opportunity'];
        $no_demo_set_companies = $opportunities_list['no_demo_set_companies'];

        // To display the data in the storefront
        pipeline_overview_report_view($no_demo_set_opportunity, $opportunity, $no_demo_set_companies);
    }  
    else {
        $no_demo_set_opportunity = array();
        $opportunity = array();
        $no_demo_set_companies = array();

        // To display the data in the storefront
        pipeline_overview_report_view($no_demo_set_opportunity, $opportunity, $no_demo_set_companies);
    } 
}

/**
 * The call back ajax function to update the report data depend on selected filters.
 */
function pipeline_overview_report_ajax_call_back() {
    $filters = array();
	if (filter_input(INPUT_GET, 'data', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY)) {
		$filters = filter_input(INPUT_GET, 'data', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
	}
    
	pipeline_overview_report_data($filters);
    wp_die();
}
