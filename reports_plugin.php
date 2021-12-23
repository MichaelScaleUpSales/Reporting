<?php
/**
 * Plugin Name: Close Reports
 * Description: This plugin to display the close.com reports.
 * Version: 1.0.0
 * Author: ITG Team - Infinite Tiers Group, Inc.
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

include_once 'constants.php';
include_once 'configuration.php';
include_once 'custom-fields.php';
include_once 'reports/all-reports.php';
include_once 'reports/lead-generation-metrics-report.php';
include_once 'reports/sales-process-metrics-report.php';
include_once 'reports/pipeline-overview-report.php';
include_once 'custom-fields-choices.php';
include_once 'include/database-manager.php';
include_once 'include/report-data/lead-generation-metrics-report-data.php';
include_once 'include/report-data/sales-process-metrics-report-data.php';
include_once 'include/report-data/pipeline-overview-report-data.php';
include_once 'include/cron-job.php';



add_action( 'wp_enqueue_scripts', 'close_reports_script_load' );

function close_reports_script_load() {
    wp_enqueue_style('bootstrap4', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css');
    wp_enqueue_script( 'boot1','https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js', array( 'jquery' ),'',true );
    wp_enqueue_script( 'popper','https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.0.4/popper.js', array( 'jquery' ),'',true );

    wp_enqueue_script( 'boot2','https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js', array( 'jquery' ),'',true );
    wp_enqueue_script( 'boot3','https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js', array( 'jquery' ),'',true );
    wp_enqueue_script( 'boot4','https://cdn.jsdelivr.net/momentjs/latest/moment.min.js', array( 'jquery' ),'',true );
    wp_enqueue_script( 'boot5','https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js', array( 'jquery' ),'',true );
    wp_enqueue_script( 'boot6','https://code.jquery.com/ui/1.12.1/jquery-ui.js', array( 'jquery' ),'',true );
    wp_enqueue_style('datepicker', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');

    wp_enqueue_style( 'bootstrap-select-css', plugin_dir_url( __FILE__ ) . 'css/bootstrap-select/css/bootstrap-select.min.css');
    wp_enqueue_script( 'bootstrap-select-js', plugin_dir_url( __FILE__ ) . 'css/bootstrap-select/js/bootstrap-select.min.js', array( 'jquery' ) ,'',true );

    wp_register_style( 'jquery-ui', 'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css' );
    wp_enqueue_style( 'jquery-ui' );
    wp_register_style( 'close-reports-fonts-style', plugin_dir_url( __FILE__ ) . 'css/fonts/fonts.css' );
    wp_enqueue_style( 'close-reports-fonts-style' );
    wp_register_style( 'close-reports-style', plugin_dir_url( __FILE__ ) . 'css/close-reports.css', '', '1.1.4' );
    wp_enqueue_style( 'close-reports-style' );
    wp_enqueue_script( 'close-reports-script', plugin_dir_url( __FILE__ ) . '/js/close-reports-js.js', array( 'jquery' ), '1.2.6' );
    wp_localize_script( 'close-reports-script', 'close_ajax_report_object',
            array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
            
}

add_action( 'show_user_profile', 'close_extra_user_profile_fields' );
add_action( 'edit_user_profile', 'close_extra_user_profile_fields' );

// Adding extra user profile fields.
function close_extra_user_profile_fields( $user ) { ?>
    <h3><?php _e("Extra profile information", "blank"); ?></h3>

    <table class="form-table report-table-container">
    <tr>
        <th><label for="# of written outreach"><?php _e("# of written outreach"); ?></label></th>
        <td>
            <input type="number" name = 'number_of_written_outreach' id = 'number_of_written_outreach' value="<?php echo esc_attr(get_user_meta($user->ID, constant('USER_META_CONTACT_OUTREACH_NUMBER'), true)) ; ?>" class="regular-text" /><br />
            <span class="description"><?php _e("The maximum # of written outreach which you want to see in the table."); ?></span>
        </td>
    </tr>
    <tr>
        <th><label for="API KEY"><?php _e("Organaization API Key"); ?></label></th>
        <td>
            <input type="string" name = 'API_KEY' id = 'API_KEY' value="<?php echo esc_attr(get_user_meta($user->ID, constant('USER_META_CONTACT_API_KEY'), true)) ; ?>" class="regular-text" /><br />
            <span class="description"><?php _e("The Close Organaization API Key"); ?></span>
        </td>
    </tr>
    </table>
<?php }

add_action( 'personal_options_update', 'save_module_user_profile_fields' );
add_action( 'edit_user_profile_update', 'save_module_user_profile_fields' );

function save_module_user_profile_fields( $user_id ) {
    if ( !current_user_can( 'edit_user', $user_id ) ) { return false; }
    $API_Key = filter_input(INPUT_POST, constant('USER_META_CONTACT_API_KEY'));
    update_user_meta( $user_id, constant('USER_META_CONTACT_OUTREACH_NUMBER'), filter_input(INPUT_POST, constant('USER_META_CONTACT_OUTREACH_NUMBER')));
    if (get_user_meta($user_id, constant('USER_META_CONTACT_API_KEY'), true) !== $API_Key) {
        update_user_meta( $user_id, constant('USER_META_CONTACT_API_KEY') , $API_Key);
        get_customer_custom_fields(true, get_user_by('id', $user_id));
    }
}

// Get the API KEY based on the current user.
function get_api_key($user_id = 0) {

    if($user_id == 0){
        $user_id = get_current_user_id();
    }
    $api_key = get_user_meta($user_id, constant('USER_META_CONTACT_API_KEY'), true);
    return $api_key;
}



/**
 * Function to call the Close.com lead API and the data depend on the selected filters.
 */
function get_lead_call_api($selectedFilters, $startIndex, $addition_query_parameter = '', $fields, $userId) {
    
    $api_key = get_api_key($userId);

    $custom_CONSTANT = constant('CUSTOM');
    //Prepare the selected filters query.
    $filters_query = '';
    if(!empty($selectedFilters)) {
        foreach ($selectedFilters as $key => $filter) {
            // $name = "Campaign";
            $name = $filter['name'];
            $value = $filter['value'];
            
            $report = $filter['report-name'];
			// $value = "Campaign 1 - Product";
			$type = array_key_exists('type', $filter) ? $filter['type'] : '';
			$year;
            $month;

            if ($name === 'date-lead') {
                $dates_list = explode('to', $value);
                
                // Get the start date (Not include the start day).
                $start_date = trim($dates_list[0]);

                $filter_string_date = trim($dates_list[1]);

                // Get the end date (Not include end day).
                $end_date = trim($dates_list[1]);
                $namenew = "qualified lead?";
                $valuenew = '("Yes")';
                
                $quali_lead_str = '"custom.qualified lead?" in ("Yes")';

                    $filter_string = " \"" . $custom_CONSTANT . "." . 'qualified lead date' . "\" > " . $start_date ." \"" . $custom_CONSTANT . "." . 'qualified lead date' . "\" < " .$end_date . " " . $quali_lead_str;
                    // $filter_string = " \"" . $custom_CONSTANT . "." . $name . "\":\"" . $value . "\" ";
                    /* print $filter_string;
                    die; */
                    //$filter_string ='"custom.First Outreach Date" > 2021-07-01 "custom.First Outreach Date" < 2021-08-30 
                    
            }

                else if ($name === 'date') {
                    $dates_list = explode('to', $value);
                    
                    // Get the start date (Not include the start day).
                    $start_date = trim($dates_list[0]);
    
                    $filter_string_date = trim($dates_list[1]);
    
                    // Get the end date (Not include end day).
                    $end_date = trim($dates_list[1]);
    
                  
                        $filter_string = " \"" . $custom_CONSTANT . "." . constant('FIRST_OUTREACH_DATE') . "\" > " . $start_date ." \"" . $custom_CONSTANT . "." . constant('FIRST_OUTREACH_DATE') . "\" < " .$end_date;
                        // $filter_string = " \"" . $custom_CONSTANT . "." . $name . "\":\"" . $value . "\" ";
                    
               
               
            
            } else {
                $filter_string = " \"" . $custom_CONSTANT . "." . $name . "\":\"" . $value . "\" ";
            }
            $filters_query = $filters_query . $filter_string;
            
        }
    }

    
    if (!empty($addition_query_parameter)) {
        $filters_query =  $addition_query_parameter . $filters_query;
    }

    // print_r($filter_string);
    // die;

	// Prepare the data API URL.
    $data_request_url = constant('CLOSE_API_PATH') . constant('CLOSE_API_LEAD'). '?_limit=200&_skip=' . $startIndex;

    if (!empty($filters_query)) {
        $data_request_url = constant('CLOSE_API_PATH') . constant('CLOSE_API_LEAD'). '?_limit=200&_skip=' . $startIndex . '&query=' . urlencode ($filters_query);
    }

    if(!empty($fields)) {
        $data_request_url =  $data_request_url . '&_fields=' .  $fields;
    }

   /*  print '<br>';
    print $data_request_url;
    print '<br>'; */

    // Get the list companies depend on the opportunity status
    $response = wp_remote_get($data_request_url,
        array('timeout' => 3600 , 'headers' => array( 'Authorization' => 'Basic ' . base64_encode($api_key ))
    ));


	$body     = wp_remote_retrieve_body( $response );
    $leads_company = json_decode($body, true);
    return $leads_company;
}

/**
 * Function to call the Close.com opportunity API.
 */
function get_opportunity_list($selectedFilters, $userId) {

    
    $api_key = get_api_key($userId);
    
    $count_loop = 0;
    $custom_CONSTANT = constant('CUSTOM');
    $first_response_sentiment_CONSTANT = constant('FIRST_RESPONSE_SENTIMENT');
    $opportunities = array();
    $opportunities_data = array();
    $has_more = 0;
    // To get all opportunity statuses
    $response = wp_remote_get('https://api.close.com/api/v1/opportunity?status_type__in=active,won,lost',
      array('timeout' => 3600 , 'headers' => array( 'Authorization' => 'Basic ' . base64_encode($api_key ))
    ));

    $body = wp_remote_retrieve_body( $response );
    $results = json_decode($body);
    
    $opportunities_data[] = $results->data;
   
    
    
    $skip = 100;
    $limit = 100;

    if($results->has_more != ''){
        $has_more = $results->has_more;
    }else{
        $has_more = 0;
    }

    
    
    while($has_more){
        $query = "&_skip=".$skip."&_limit=".$limit;
        
        $response = wp_remote_get('https://api.close.com/api/v1/opportunity?status_type__in=active,won,lost'.$query,
            array('timeout' => 3600 , 'headers' => array( 'Authorization' => 'Basic ' . base64_encode($api_key ))
            ));
        $body = wp_remote_retrieve_body( $response );
        $results = json_decode($body);
        
        $opportunities_data[] = $results->data;

        
        if($results->has_more != ''){
            $has_more = $results->has_more;
        }else{
            $has_more = 0;
        }
    }

   
    
    $companies = $statuses = [];
    
    foreach($opportunities_data as $data_ops){
        
        foreach($data_ops as $data){
            
            array_push($statuses,$data->status_id);
            $companies[$data->status_display_name]++;
        }
    }
    
   
    $statuses = array_unique($statuses);
    
   

    $opportunity_statuses = json_decode($body, true);

    
    $default_order_opportunity = array();
    $ordered_list = array();

    $query_str = 'http://api.close.com/api/v1/lead/?_limit=200&query="opportunity_status" in ("' .implode('","',$statuses). '") ';
    
    if(!empty($selectedFilters)){
        foreach($selectedFilters as $filter){
            if($filter['name'] == 'date'){
                $dates = explode(' to ',$filter['value']);
                $query_str.= ' "custom.First Outreach Date" > "'.$dates[0].'" "custom.First Outreach Date" < "'.$dates[1].'"';
            }
            else if($filter['name'] == 'Campaign'){
                $query_str.= ' "custom.Campaign":"'.$filter['value'].'"';
            }
            else if($filter['name'] == 'Company Size'){
                $query_str.= ' "custom.Company Size":"'.$filter['value'].'"';
            }
            else if($filter['name'] == 'Lead Source'){
                $query_str.= ' "custom.Lead Source":"'.$filter['value'].'"';
            }
            else if($filter['name'] == 'Lead Owner'){
                $query_str.= ' "custom.Lead Owner":"'.$filter['value'].'"';
            }
            else if($filter['name'] == 'First Response Medium'){
                $query_str.= ' "custom.First Response Medium":"'.$filter['value'].'"';
            }
            else if($filter['name'] == 'First Response Sentiment'){
                $query_str.= ' "custom.First Response Sentiment":"'.$filter['value'].'"';
            }
        }

      
        
        $response = wp_remote_get($query_str,
            array('timeout' => 3600 , 'headers' => array( 'Authorization' => 'Basic ' . base64_encode($api_key ))
        ));

        $body = wp_remote_retrieve_body( $response );
       

        $opportunities_lists = [];
        foreach(json_decode($body)->data as $data){
            foreach($data->opportunities as $opportunity){
                array_push($opportunities_lists,(array)$opportunity);
            }
        }

        

        if (!empty($opportunities_lists)) {
            $opportunity_statuses = $opportunities_lists;

            $companies = $statuses = [];
            foreach($opportunity_statuses as $data){
                array_push($statuses,$data['status_id']);
                $companies[$data['status_display_name']]++;
            }

           
           
            foreach ($opportunity_statuses as $record) {
                $label = $record['status_label'];
                $id = $record['status_id'];
                $default_order_opportunity[$label] = $id;

               
                $opportunities[$id] = array('id'=> $id, 'label'=> $label, 'count' => $companies[$label]);
            }
        }

        
        
        $opportunity_statuses_order = constant('OPPORTUNITY_STATUSES_ORDER');
        
        foreach ($opportunity_statuses_order as $opportunity_status) {
            $id = $default_order_opportunity[$opportunity_status];
            if ($id) {
                $ordered_list[$id] = $opportunities[$id];
            }
        }
    
       
    
        foreach ($opportunities as $opportunity_status) {
            $id = $default_order_opportunity[$opportunity_status];
            if ($id && !$ordered_list[$id]) {
                $ordered_list[$id] = $opportunities[$id];
            }
        }

    }else{

       
        
        if (!empty($opportunities_data)) {
            foreach($opportunities_data as $data){
                
                //$opportunity_statuses = $opportunity_statuses['data'];
            
                foreach ($data as $record) {
                    
                    $label = $record->status_label;
                    $id = $record->status_id;
                    $default_order_opportunity[$label] = $id;
                
                   
                    $opportunities[$id] = array('id'=> $id, 'label'=> $label, 'count' => $companies[$label]);
                    
                }
            }
        }

        if(!isset($default_order_opportunity["Contract Sent"])){
            $default_order_opportunity["Contract Sent"] = "test";
            $opportunities["test"] = array('id'=> "test", 'label'=> "Contract Sent", 'count' => 0);
        }
        

        $opportunity_statuses_order = constant('OPPORTUNITY_STATUSES_ORDER');
        
        
        foreach ($opportunity_statuses_order as $opportunity_status) {
            $id = $default_order_opportunity[$opportunity_status];
            if ($id) {
                $ordered_list[$id] = $opportunities[$id];
            }
        }
    
        

       
    
        foreach ($opportunities as $opportunity_status) {
            $id = $default_order_opportunity[$opportunity_status];
            
            if ($id && !$ordered_list[$id]) {
                $ordered_list[$id] = $opportunities[$id];
            }
        }
    }

    return $ordered_list;
}

/**
 * Function to call the Close.com opportunity API.
 */
function get_opportunity_for_leads ($all_data_list, $userId) {

    $api_key = get_api_key($userId);
    $count_loop = 0;
    $custom_CONSTANT = constant('CUSTOM');
    $positive_CONSTANT = constant('POSITIVE');
    $first_response_sentiment_CONSTANT = constant('FIRST_RESPONSE_SENTIMENT');
    $opportunity = array();

    // To get all opportunity statuses
    $response = wp_remote_get(constant('CLOSE_API_PATH') . constant('CLOSE_API_OPPORTUNITY_STATUS'),
      array('timeout' => 3600 , 'headers' => array( 'Authorization' => 'Basic ' . base64_encode($api_key ))
    ));
    $body = wp_remote_retrieve_body( $response );
    $opportunity_statuses = json_decode($body, true);

    if (!empty($opportunity_statuses['data'])) {
        $opportunity_statuses = $opportunity_statuses['data'];
        $opportunities = array(); // To save the opportunity status id and label in it
        foreach ($opportunity_statuses as $record) {
            $label = $record['label'];
            $id = $record['id'];
            $opportunity[$id] = array('id'=> $id, 'label'=> $label, 'count' => 0, 'companies_names' => array());
            array_push($opportunities, $opportunity);
        }
    }

    $no_demo_set_opportunity = 0;
    $no_demo_set_companies = array();

    // Check if there are leads data or not
    if (!empty($all_data_list)) {
        // To get each organization record
        foreach ($all_data_list as $company_item) {
            $count_loop = $count_loop + 1;
            $company_name = $company_item[constant("DISPLAY_NAME")];

            // Check if the organization has opportunities or not
            if(!empty($company_item[constant('OPPORTUNITIES')])) {
                // Get all opportunities choices
                $opportunities_choices_list = $company_item[constant('OPPORTUNITIES')];
                $current_company_opportunities = array();

                foreach ($opportunities_choices_list as $opportunities_choices) {
                    // Check if the organization has opportunity status or not yet
                    if(array_key_exists(constant('STATUS_ID'), $opportunities_choices)) {
                        // Get the if of the status
                        $status_id = $opportunities_choices[constant('STATUS_ID')];
                        // Check if the opportunity is add for current company.
                        if (!in_array($status_id, $current_company_opportunities)) {
                            array_push($current_company_opportunities, $status_id);
                        }
                    }
                }

                // Add the current company opportunity for the total number of opportunities.
                foreach ($current_company_opportunities as $company_opportunities_status_id) {
                    $opportunity[$company_opportunities_status_id]['count'] = $opportunity[$company_opportunities_status_id]['count'] + 1;
                    array_push( $opportunity[$company_opportunities_status_id]['companies_names'], $company_name);
                }

            } else {

                if(!empty($company_item[$custom_CONSTANT])) {
                    $organization_custom_fields = $company_item[$custom_CONSTANT];
                    if(array_key_exists($first_response_sentiment_CONSTANT, $organization_custom_fields) && $organization_custom_fields[$first_response_sentiment_CONSTANT] === $positive_CONSTANT) {
                        array_push($no_demo_set_companies, $company_name);
                        // This mean that the Company don't  have opportunity status yet (No Demo set)
                        $no_demo_set_opportunity++;
                    }
                }
            }
        }
    }
    return array ('no_demo_set_opportunity' => $no_demo_set_opportunity, 'no_demo_set_companies' => $no_demo_set_companies, 'opportunity' => $opportunity);
}

// Add the loader action.
function add_loader() {
    // Add the loader for all the pages except the home page.
	if (!is_front_page()) {
		?>
			<div class="page-loader overlay-container" style="display: block;">
				<div class="max-loader">Loading ...</div>
			</div>
		<?php
	}
}
add_action('wp_body_open', 'add_loader');



// Get the custom fields when the user login.
function get_customer_custom_fields($user_login, $user) {

    global $users_data;
    $users_data = get_leads_users();

    
	$lead_custom_fields = lead_custom_fields();

	update_user_meta( $user->ID, constant("USER_META_LEAD_CUSTOM_FIELD"), $lead_custom_fields );

	$contact_custom_fields = contact_custom_fields();
	update_user_meta( $user->ID, constant("USER_META_CONTACT_CUSTOM_FIELD"), $contact_custom_fields );

}
add_action('wp_login', 'get_customer_custom_fields', 10, 2);

/**
 * Filter hidden Toolbar menus on the front-end.
 *
 * @param array $ids Toolbar menu IDs.
 * @return array (maybe) filtered front-end Toolbar menu IDs.
 */
function wpdocs_hide_some_toolbar_menu( $ids ) {
    return $ids;
}
add_filter( 'rda_frontend_toolbar_nodes', 'wpdocs_hide_some_toolbar_menu' );

// Hide the update nag in dashboard.
function hide_wp_update_nag() {
    remove_action( 'admin_notices', 'update_nag', 3 );
}
add_action('admin_menu','hide_wp_update_nag');

// Create Caching Database
register_activation_hook( __FILE__, 'plugin_activation');

// Create Cron Job to fetch data from Close.com API to Caching Table every 24hours (12:00 AM)
add_action( 'close_reports_job', 'schedule_for_cron_job_to_fill_caching_table' );

function plugin_activation() {
    create_close_reports_caching_table();
    if (! wp_next_scheduled ( 'close_reports_job' )) {
        wp_schedule_event( strtotime('23:59:00'), 'daily', 'close_reports_job', array(),  true );
    }
}

function schedule_for_cron_job_to_fill_caching_table() {
    fill_caching_data();
}

register_deactivation_hook( __FILE__, 'plugin_deactivation' );
  
function plugin_deactivation() {
    wp_clear_scheduled_hook( 'close_reports_job' );
}
?>