<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

// Get the extension selectors.
add_action( 'wp_ajax_sales_process_metrics_report', 'sales_process_metrics_report_ajax_call_back' );

function sales_process_metrics_report_view_html ($opportunities) {
    
    ?>
	<div  class="sales_process_metrics_report">
        <div class="d-flex">
            <div>
                <table class="report-table report-table-container" id="report-table">
                <tr class="report-table-filters-row">
                        <td><?php
                            foreach ($GLOBALS['filters_names'] as $name) {
                                $nameWithoutSpace = str_replace(' ', '_',$name);
                                if($name === constant('FIRST_OUTREACH_DATE')) {
                                    ?>
                                    <div id=month> Month </div>
                                    <div id=year> Year </div>
                                    <?php
                                } else if ($name !== constant('FIRST_RESPONSE_MEDIUM')){
                                    ?><div id="<?php echo $nameWithoutSpace; ?>"><?php echo $name; ?> </div><?php
                                }
                            } 
                        ?></td>
                        <td class="filters" id="filters_1"><?php get_custom_fields('sales_process_metrics_report', true);?></td>
                    </tr>
                    <?php
                    $LABELS_CONSTANTS = constant('CHANGE_OPPORTUNITY_STATUSES_LABEL');
                    
                    foreach($opportunities as $opportunity) {
                        $label_text = $opportunity['label'];
                        if ($opportunity['id']) {
                        ?>
                        <tr id="<?php echo $opportunity['id']; ?>">
                            <td class="sales_process_item_label">Total # of <?php if($LABELS_CONSTANTS[$label_text]) { echo $LABELS_CONSTANTS[$label_text]; } else { echo $label_text; } ; ?></td>
                            <td class="sales_process_item_value" data-opp="<?= $label_text ?>"><?php echo number_format($opportunity['count']);?></td>
                        </tr>
                        <?php
                        }
                    } ?>
                </table>
            </div>
            <div class="btn_new_column_container">
                <label for="add-new-cloumn" class="btn_new_column_label">+</label>
                <input class="btn-new-column" type="button" id="add-new-cloumn" name="add-new-cloumn" value="Add New Column" />
            </div>
        </div>
    </div>
    <script>
        // var leads_converted_to_opportunity_status = <?php echo json_encode($leads_converted_to_opportunity_status); ?>;
        var initial_data = <?php echo json_encode($opportunities);?>;
    </script>

    <?php
}


function sales_process_metrics_report_view (
    $total_responses,
    $total_reponse_rate,
    $total_positive_responses,
    $total_positive_responses_rate,
    $total_negative_responses,
    $total_negative_responses_rate,
    $leads_converted_to_opportunity_status,
    $opportunities,
    $sales_cycle_length_in_months
) {

    $initial_values = array(
        "number_of_opportunities_values" => $opportunities,
        "cycle_company_in_months_values" => $sales_cycle_length_in_months
    );

    // Collect opportunity options.
    $opportunity_options = '';
    $initial_value = '';
    $initial_opportunity_label = '';
    $companies_options = '';

    foreach($opportunities as $opportunity) {
        $label = $opportunity['label'];
        $count = $opportunity['count'];
        if (empty($initial_value) && empty($initial_opportunity_label)) {
            $initial_value = $count;
            $initial_opportunity_label = $label;
        }
        $opportunity_options = $opportunity_options . '
            <option name="' . strtolower(str_replace(' ', '_',$label)) .'" value="' .
                $count .'" >' . $label . '</option>';
    }


    ?>
    <script>
        var leads_converted_to_opportunity_status = <?php echo json_encode($leads_converted_to_opportunity_status); ?>;
        var initial_data = <?php echo json_encode($initial_values);?>;
    </script>

	<div  class="sales_process_metrics_report">
        <div class="d-flex">
            <div>

                <table class="report-table report-table-container" id="report-table">

                    <tr class="report-table-filters-row">
                        <td><?php
                            foreach ($GLOBALS['filters_names'] as $name) {
                                $nameWithoutSpace = str_replace(' ', '_',$name);
                                if($name === constant('FIRST_OUTREACH_DATE')) {
                                    ?>
                                    <div id=month> Month </div>
                                    <div id=year> Year </div>
                                    <?php
                                } else if ($name !== constant('FIRST_RESPONSE_MEDIUM')){
                                    ?><div id="<?php echo $nameWithoutSpace; ?>"><?php echo $name; ?> </div><?php
                                }
                            } 
                        ?></td>
                        <td class="filters" id="filters_1"><?php get_custom_fields('sales_process_metrics_report', true);?></td>
                    </tr>
                    <tr class="number_of_opportunities_values">
                        <td class="sales_process_item_label">
                            <select class="opportunity_list_labels select-input-scale">
                                <?php echo $opportunity_options; ?>
                            </select>
                        </td>
                        <td class="sales_process_item_value number_of_opportunities_container">
                            <div class="number_of_opportunities_display"><?php echo $initial_value; ?></div>
                            <select class="opportunity_list" style="display: none;">
                                <?php echo $opportunity_options; ?>
                            </select>
                            <div class="opportunity_status_items" style="display: none;"><?php echo json_encode($leads_converted_to_opportunity_status); ?></div>
                        </td>
                    </tr>

                    <tr id="number_of_demos_divided_by_total_companies_contacted">
                        <td class="sales_process_item_label"><span id="opportunity_choice_companies_contacted"><?php echo $initial_opportunity_label; ?></span> / Total companies contacted</td>
                        <td class="sales_process_item_value number_of_demos_divided_by_total_companies_contacted"><?php echo number_format($leads_converted_to_opportunity_status[$initial_opportunity_label]['number_of_demos_divided_by_total_companies_contacted'], 2);?> %</td>
                    </tr>
                    <tr id="number_of_demos_divided_by_total_companies_replied">
                        <td class="sales_process_item_label"><span id="opportunity_choice_companies_replied"><?php echo $initial_opportunity_label; ?></span> / Total companies replied</td>
                        <td class="sales_process_item_value number_of_demos_divided_by_total_companies_replied"><?php echo number_format($leads_converted_to_opportunity_status[$initial_opportunity_label]['number_of_demos_divided_by_total_companies_replied'], 2);?> %</td>
                    </tr>
                    <tr id="number_of_demos_divided_by_total_companies_replied_positively">
                        <td class="sales_process_item_label"><span id="opportunity_choice_replied_positively"><?php echo $initial_opportunity_label; ?></span> / Total companies that replied positively</td>
                        <td class="sales_process_item_value number_of_demos_divided_by_total_companies_replied_positively"><?php echo number_format($leads_converted_to_opportunity_status[$initial_opportunity_label]['number_of_demos_divided_by_total_companies_replied_positively'], 2);?> %</td>
                    </tr>
                    <tr class="cycle_company_in_months_values">
                        <td class="sales_process_item_label">Sales Cycle Length (in months)</td>
                        <td class="sales_process_item_value"><?php echo $sales_cycle_length_in_months; ?></td>
                    </tr>
                </table>
            </div>
            <div class="btn_new_column_container">
                <label for="add-new-cloumn" class="btn_new_column_label">+</label>
                <input class="btn-new-column" type="button" id="add-new-cloumn" name="add-new-cloumn" value="Add New Column" />
            </div>
        </div>
    </div>
	<?php
}

function sales_process_metrics_report($selectedFilters, $isAjaxRequest = false) {
    

    $report_json_data = retrieve_sales_process_metrics_report_data($selectedFilters, get_current_user_id());
    $opportunities = json_decode($report_json_data, true);
    
    
    if ($isAjaxRequest) {
        
        $return_data = array(
            "opportunity_list" => $opportunities,
        );
        echo json_encode($return_data);
        
    } else {
        
        sales_process_metrics_report_view_html($opportunities);
    }
}

/**
 * The call back ajax function to update the report data depend on selected filters.
 */
function sales_process_metrics_report_ajax_call_back() {
    
	if (filter_input(INPUT_GET, 'data', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY)) {
        $filters = filter_input(INPUT_GET, 'data', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
    }
	sales_process_metrics_report($filters, true);
    wp_die();
}
