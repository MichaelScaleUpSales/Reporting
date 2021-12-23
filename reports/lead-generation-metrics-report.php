<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

global $channel_custom_field_id;
$GLOBALS['maximum_outreach_number'] = 0;

// Get the extension selectors.
add_action( 'wp_ajax_lead_generation_metrics_report', 'lead_generation_metrics_report_ajax_call_back' );
add_action( 'wp_ajax_lead_generation_metrics_report_lead', 'lead_generation_metrics_lead_report_ajax_call_back' );


function render_channel_outreach_record($channel_name, $number_of_written_outreach, $is_number_written_of_outreach_exit, $number_of_written_outreach_object, $total_response) {
	
	$percent_of_responses = 0;
	if ($number_of_written_outreach_object['total_response'] && $total_response) {
		$percent_of_responses = ($number_of_written_outreach_object['total_response'] / $total_response)*100;
	}
	?>
	<tr class="lead-generation-table-row">
		<td><span><?php echo $channel_name; ?></span></td>
		<td><?php echo $number_of_written_outreach; ?></td>
		<td><?php echo $is_number_written_of_outreach_exit ? $number_of_written_outreach_object['total_response'] : 0; ?></td>
		<td><?php echo $is_number_written_of_outreach_exit ? $number_of_written_outreach_object['number_of_positive_responses']:0; ?></td>
		<td><?php echo $is_number_written_of_outreach_exit ? number_format($percent_of_responses, 2) :0; ?> %</td>
		<td><?php echo $is_number_written_of_outreach_exit ? number_format($number_of_written_outreach_object['positive_response_percent'], 2):0; ?> %</td>
		<td><?php echo $is_number_written_of_outreach_exit ? $number_of_written_outreach_object['number_of_leads'] : 0; ?></td>
	</tr>
	<?php
}

function lead_generation_metrics_report_view($total_companies, $total_response, $total_positive_response, $response_rate, $positive_total_response_rate, $positive_companies_response_rate, $total_leads, $channels_data, $is_filters_contain_number_outreach,$campaigns = "",$responses = '') {
	
	$empty_data = true;
	
	?>
	<div  class="report__table lead_generation_metrics_report container">
	<div class="report-general-information">
		<div class="report-general-information_item report-general-information_first_item">
			<span class="report-general-information_item_header">Total Companies Approached </span>
			<span class="report-general-information_item_value text-blue"><?php echo $total_companies; ?></span>
		</div>
		<div class="report-general-information_item">
			<span class="report-general-information_item_header">Total Companies that Replied </span>
			<span class="report-general-information_item_value text-blue"><?php echo $total_response; ?></span>
		</div>
		<div class="report-general-information_item">
			<span class="report-general-information_item_header">Total Companies that Replied Positively</span>
			<span class="report-general-information_item_value text-blue"><?php echo $total_positive_response; ?></span>
		</div>
		<div class="report-general-information_item">
			<span class="report-general-information_item_header">Total Company Response Rate </span>
			<span class="report-general-information_item_value text-blue"><?php echo number_format($response_rate, 2); ?>%</span>
		</div>
		<div class="report-general-information_item">
			<span class="report-general-information_item_header">Total Company Positive Response Rate </span>
			<span class="report-general-information_item_value text-blue"><?php echo number_format($positive_total_response_rate, 2); ?>%</span>
		</div>
		<div class="report-general-information_item report-general-information_last_item">
			<span class="report-general-information_item_header">Total Leads</span>
			<span class="report-general-information_item_value text-blue"><?php echo $total_leads; ?></span>
		</div>
	</div>
	<div class="sub-filters-container">
		<?php
			
		?>
		
		<div class="sub-filters d-flex">
			
			<div class="campaigns-filter">
				<select id="campaigns-filter" name="Campaign">
					<option value="">Campaign</option>
					<?php foreach($campaigns as $campaign): ?>
						<option value="<?= $campaign ?>" <?php ($filter_camp == $campaign) ? print "selected" : print ""; ?>><?= $campaign ?></option>
					<?php endforeach; ?>
					
				</select>
			</div>
		</div>
		
	</div>
	<div class="filter-chips-container"></div>
	<table id="reportTable" class="report-table-lead report-table-container">
		<tr class="report-table-header">
			<th class="report-table-header-item report-table-header-first-item pb-0 pl-4 width-7">
				<div class="form-group h-hand m-0">
					<select class="form-control select-channel selectpicker">
					<option>All Channels</option>
					<?php
						foreach ($channels_data as $channel_name => $channel_data_list) {
							?><option><?php echo $channel_name ?></option><?php
						}
					?>
					</select>
				</div>
			</th>
			<th class="report-table-header-item align-bottom width-7 pl-3"><span>Message #</span></th>
			<th class="report-table-header-item align-bottom width-7 pl-3"><span>Total # of Replies</span></th>
			<th class="report-table-header-item align-bottom width-7 pl-3"><span>Total # of Positive Replies</span></th>
			<th class="report-table-header-item align-bottom width-7 pl-3"><span>% Replies To Message Out of Total Replies</span></th>
			<th class="report-table-header-item align-bottom width-7 pl-3"><span>Positive Reply %</span></th>
			<th class="report-table-header-item report-table-header-last-item align-bottom width-7 pl-3"># of Leads</th>
		</tr>

		<?php
		$total_responses = 0;
		foreach($responses as $response){
			$total_responses+=$response['total'];
		}

		
		foreach($responses as $key => $response):
			foreach($response['responses'] as $outreach => $data):
			
			?>
				<tr class="lead-generation-table-row">
					<td><span><?php echo $key; ?></span></td>
					<td><?php echo $outreach; ?></td>
					<td><?php echo $data['total']; ?></td>
					<td><?php echo $data['positive']; ?></td>
					<td><?php echo round(($data['total'] / $total_responses) * 100 , 2); ?>%</td>
					<td><?php echo round(($data['positive'] / $data['total']) * 100 , 2); ?>%</td>
					<td><?php echo $data['leads']; ?></td>
				</tr>
			<?php endforeach; 
		endforeach; 
		
		if(empty($responses)) {
			?>
			<tr> <td>No data found</td></tr>
			<?php
		}
	?>
	</table>
	</div>
	<?php
}

function get_campaigns(){
	$query = 'https://api.close.com/api/v1/lead/?_limit=200&query=has:custom.Campaign&_fields=custom';
	/* print $query;
	die; */

	
	$api_key = get_api_key(get_current_user_id());
	

	$response = wp_remote_get($query,
        array('timeout' => 3600 , 'headers' => array( 'Authorization' => 'Basic ' . base64_encode($api_key ))
    ));
	$body = wp_remote_retrieve_body( $response );
	
	$data_api = json_decode($body, true);
	
	$campaigns = [];
	foreach($data_api['data'] as $campaign){
		array_push($campaigns,$campaign['custom']['Campaign']);
	}

	$campaigns = array_unique($campaigns);
	sort($campaigns);

	$has_more_data = $data_api['has_more'];
	if($has_more_data == 1){
		while($has_more_data > 0){

			$query = 'https://api.close.com/api/v1/lead/?_limit=200&query=has:custom.Campaign AND NOT custom.Campaign in ('.implode(',',$campaigns).')&_fields=custom';
			$api_key = get_api_key(get_current_user_id());
			

			$response = wp_remote_get($query,
				array('timeout' => 3600 , 'headers' => array( 'Authorization' => 'Basic ' . base64_encode($api_key ))
			));
			$body = wp_remote_retrieve_body( $response );

			$data_api = json_decode($body, true);
			
			foreach($data_api['data'] as $campaign){
				array_push($campaigns,$campaign['custom']['Campaign']);
			}

			$campaigns = array_unique($campaigns);
			sort($campaigns);
			

			$has_more_data = $data_api['has_more'];
		}
	}
	
	
	return $campaigns;

}

function lead_generation_metrics_report_data($selectedFilters) {
	
	$user_Id = get_current_user_id();
	global $channel_custom_field_id;
	$api_key = get_api_key($user_Id);

	$mediums = [
		'http://api.close.com/api/v1/lead/?query="custom.First Response Medium":"LinkedIn"&"custom.First Response Medium":"LinkedIn"&_fields=custom&_limit=200',
		'http://api.close.com/api/v1/lead/?query="custom.First Response Medium":"Email"&"custom.First Response Medium":"LinkedIn"&_fields=custom&_limit=200'
	];

	$responses = [];

	foreach($mediums as $url){
		$response = wp_remote_get( $url ,
			array('timeout' => 3600 , 'headers' => array( 'Authorization' => 'Basic ' . base64_encode( $api_key ))
		));
		
		$body = wp_remote_retrieve_body( $response );
		$data_api = json_decode($body, true);
		
		foreach($data_api['data'] as $api){
			
			$response_medium = $api['custom']['First Response Medium'];
			$response_outreach = $api['custom']['# of Written Outreach'];
			
			$responses[$response_medium]['responses'][$response_outreach]['total']++;
			$responses[$response_medium]['total']++;

			if($api['custom']['First Response Sentiment'] == "Positive"){
				$responses[$response_medium]['responses'][$response_outreach]['positive']++;
				$responses[$response_medium]['total_positive']++;
			}

			if(isset($api['custom']['Qualified Lead?'])){
				$responses[$response_medium]['responses'][$response_outreach]['leads']++;
			}
			
		}
		$has_more = 0;
		$skip = 200;
		
		if($data_api['has_more'] == 1){
			$has_more = 1;
		}

		while($has_more){
			$response = wp_remote_get( $url."&_skip=".$skip ,
				array('timeout' => 3600 , 'headers' => array( 'Authorization' => 'Basic ' . base64_encode( $api_key ))
			));
			
			
			
			$body = wp_remote_retrieve_body( $response );
			$data_api = json_decode($body, true);

			foreach($data_api['data'] as $api){
				
				$response_medium = $api['custom']['First Response Medium'];
				$response_outreach = $api['custom']['# of Written Outreach'];

				$responses[$response_medium]['responses'][$response_outreach]['total']++;
				$responses[$response_medium]['total']++;

				if($api['custom']['First Response Sentiment'] == "Positive"){
					$responses[$response_medium]['responses'][$response_outreach]['positive']++;
					$responses[$response_medium]['total_positive']++;
				}

				if(isset($api['custom']['Qualified Lead?'])){
					$responses[$response_medium]['responses'][$response_outreach]['leads']++;
				}
				
			}
			$has_more = $data_api['has_more'];
			$skip+=200;
		}

		
	}
	foreach($responses as $metric => $values){
		ksort($responses[$metric]['responses']);
	}

	
	$needed_urls  = [
		'global_total_companies' => 'http://api.close.com/api/v1/lead/?_fields=total_results',
		'total_positive_response' => 'http://api.close.com/api/v1/lead/?_fields=total_results&_limit=200&query="custom.first response sentiment" in ("Positive") ',
		'total_response' => 'http://api.close.com/api/v1/lead/?_fields=total_results&_limit=200&query="custom.first response sentiment" in ("Positive","Negative") ',
		'total_leads' => 'http://api.close.com/api/v1/lead/?_fields=total_results&_limit=200&query="custom.qualified lead?" in ("Yes")',
	];

	$results = [];
	
	foreach($needed_urls as $key => $url){
		$response = wp_remote_get( $url ,
			array('timeout' => 3600 , 'headers' => array( 'Authorization' => 'Basic ' . base64_encode( $api_key ))
		));
		
		$body = wp_remote_retrieve_body( $response );
		$data_api = json_decode($body, true);
		$results[$key] = $data_api['total_results'];
	}

	/* print "<pre>";
	print_R($results);
	die; */

	//$report_json_data = retrieve_generation_metrics_report_data($selectedFilters, get_current_user_id(), $channel_custom_field_id);
	
	
	$report_data = json_decode($report_json_data);
	
	$global_total_companies = $results['global_total_companies'];
	$total_leads = $results['total_leads'];
	$total_positive_response = $results['total_positive_response'];
	$total_response = $results['total_response'];
	$positive_response_rate_out_of_total_response = ($results['total_positive_response'] / $results['total_response'] ) * 100 ;
	$response_rate = ($results['total_response'] / $results['global_total_companies'] ) * 100;
	$is_filters_contain_number_outreach = 0;
	//$is_filters_contain_number_outreach = $report_data->is_filters_contain_number_outreach;
	//$channels_data = json_decode($report_data->channels_data, true);


	/* print '<pre>';
	print_r($channels_data);
	die; */

	/* GET RESPONSE MEDIUMS */
	get_campaigns();
	
	

	lead_generation_metrics_report_view($global_total_companies, $total_response, $total_positive_response, $response_rate, $positive_response_rate_out_of_total_response, $positive_response_rate_out_of_total_companies, $total_leads, $channels_data, $is_filters_contain_number_outreach,get_campaigns(),$responses);
}


add_action( 'wp_ajax_qualified_lead_quickfilters', 'qualified_lead_quickfilters' );
function qualified_lead_quickfilters() {

	$api_key = get_api_key(get_current_user_id());

	if($_POST['data'] == 1){
		$query = 'http://api.close.com/api/v1/lead/?_limit=200&query="custom.qualified lead?" in ("Yes")';
	}else if($_POST['data'] == 2){
		$query = 'http://api.close.com/api/v1/lead/?_limit=200&query="custom.date replied":"yesterday" "custom.qualified lead?" in ("Yes")';
	}else if($_POST['data'] == 3){
		$query = 'http://api.close.com/api/v1/lead/?_limit=200&query="custom.qualified lead date" < "14 days ago"';
	}else if($_POST['data'] == 4){
		$query = 'http://api.close.com/api/v1/lead/?_limit=200&query="custom.qualified lead date" < "30 days ago"';
	}else if($_POST['data'] == 5){
		$query = 'http://api.close.com/api/v1/lead/?_limit=200&query="custom.qualified lead date":"last month"';
	}

	
	
	$response = wp_remote_get($query,
		array('timeout' => 3600 , 'headers' => array( 'Authorization' => 'Basic ' . base64_encode($api_key ))
	));
	$body = wp_remote_retrieve_body( $response );
	$response = json_decode($body);
	$data[] = $response;
	$skip = 200;

	if($response->has_more == 1){
		$has_more = 1;
		while($has_more){
			$response = wp_remote_get($query.'&_skip='.$skip,
				array('timeout' => 3600 , 'headers' => array( 'Authorization' => 'Basic ' . base64_encode($api_key ))
			));
			$body = wp_remote_retrieve_body( $response );
			$response = json_decode($body);
			$has_more = $response->has_more;
			$data[] = $response;
		}
		
		$skip+= 200;
	}
	
	wp_die(json_encode(array('html' => make_qualified_lead_view($data), "campaigns" => get_qualified_lead_campaigns($data))));
}


add_action( 'wp_ajax_outreach_metrics_callback', 'outreach_metrics_callback' );
function outreach_metrics_callback() {

	
	$api_key = get_api_key(get_current_user_id());
	
	if($_POST['date'] != 'First Outreach Date'){
		$dates = explode(' to ',$_POST['date']);
		$date_from = $dates[0];
		$date_to = $dates[1];
		$date_query = '"custom.First Outreach Date" > "'.$date_from.'"  "custom.First Outreach Date" < "'.$date_to.'"';
	}
	
	if($_POST['campaign'] != ''){
		$campaign_query = ' custom.Campaign:"'.$_POST['campaign'].'" ';
	}

	$mediums = [
		'http://api.close.com/api/v1/lead/?query="custom.First Response Medium":"LinkedIn"'.$campaign_query.$date_query.'&"custom.First Response Medium":"LinkedIn"&_fields=custom&_limit=200',
		'http://api.close.com/api/v1/lead/?query="custom.First Response Medium":"Email" '.$campaign_query.$date_query.'&"custom.First Response Medium":"LinkedIn"&_fields=custom&_limit=200'
	];

	$responses = [];

	foreach($mediums as $url){
		$response = wp_remote_get( $url ,
			array('timeout' => 3600 , 'headers' => array( 'Authorization' => 'Basic ' . base64_encode( $api_key ))
		));
		
		$body = wp_remote_retrieve_body( $response );
		$data_api = json_decode($body, true);
		
		foreach($data_api['data'] as $api){
			
			$response_medium = $api['custom']['First Response Medium'];
			$response_outreach = $api['custom']['# of Written Outreach'];
			
			$responses[$response_medium]['responses'][$response_outreach]['total']++;
			$responses[$response_medium]['total']++;

			if($api['custom']['First Response Sentiment'] == "Positive"){
				$responses[$response_medium]['responses'][$response_outreach]['positive']++;
				$responses[$response_medium]['total_positive']++;
			}

			if(isset($api['custom']['Qualified Lead?'])){
				$responses[$response_medium]['responses'][$response_outreach]['leads']++;
			}
			
		}
		$has_more = 0;
		$skip = 200;
		
		if($data_api['has_more'] == 1){
			$has_more = 1;
		}

		while($has_more){
			$response = wp_remote_get( $url."&_skip=".$skip ,
				array('timeout' => 3600 , 'headers' => array( 'Authorization' => 'Basic ' . base64_encode( $api_key ))
			));
			
			
			
			$body = wp_remote_retrieve_body( $response );
			$data_api = json_decode($body, true);

			foreach($data_api['data'] as $api){
				
				$response_medium = $api['custom']['First Response Medium'];
				$response_outreach = $api['custom']['# of Written Outreach'];

				$responses[$response_medium]['responses'][$response_outreach]['total']++;
				$responses[$response_medium]['total']++;

				if($api['custom']['First Response Sentiment'] == "Positive"){
					$responses[$response_medium]['responses'][$response_outreach]['positive']++;
					$responses[$response_medium]['total_positive']++;
				}

				if(isset($api['custom']['Qualified Lead?'])){
					$responses[$response_medium]['responses'][$response_outreach]['leads']++;
				}
				
			}
			$has_more = $data_api['has_more'];
			$skip+=200;
		}

		
	}

	$html = '';

	$total_responses = 0;
	foreach($responses as $metric => $response){
		$total_responses+=$response['total'];
		foreach($response['responses'] as $outreach => $data){
			if(!isset($responses[$metric]['responses'][$outreach]['positive'])){
				$responses[$metric]['responses'][$outreach]['positive'] = 0;
			}
			if(!isset($responses[$metric]['responses'][$outreach]['leads'])){
				$responses[$metric]['responses'][$outreach]['leads'] = 0;
			}
		}
	}
	
	
	foreach($responses as $metric => $values){
		ksort($responses[$metric]['responses']);
	}
	

	foreach($responses as $key => $response):
		foreach($response['responses'] as $outreach => $data):
			
			$html.='<tr class="lead-generation-table-row">
						<td><span>'.$key.'</span></td>
						<td>'.$outreach.'</td>
						<td>'.$data['total'].'</td>
						<td>'.$data['positive'].'</td>
						<td>'.round(($data['total'] / $total_responses) * 100 , 2).'%</td>
						<td>'.round(($data['positive'] / $data['total']) * 100 , 2).'%</td>
						<td>'.$data['leads'].'</td>
					</tr>';
		endforeach; 
	endforeach; 

	
	$needed_urls  = [
		'global_total_companies' => 'http://api.close.com/api/v1/lead/?_fields=total_results&query='.$campaign_query.$date_query,
		'total_positive_response' => 'http://api.close.com/api/v1/lead/?_fields=total_results&_limit=200&query="custom.first response sentiment" in ("Positive")'.$campaign_query.$date_query,
		'total_response' => 'http://api.close.com/api/v1/lead/?_fields=total_results&_limit=200&query="custom.first response sentiment" in ("Positive","Negative")'.$campaign_query.$date_query,
		'total_leads' => 'http://api.close.com/api/v1/lead/?_fields=total_results&_limit=200&query="custom.qualified lead?" in ("Yes")'.$campaign_query.$date_query,
	];

	$results = [];
	
	foreach($needed_urls as $key => $url){
		$response = wp_remote_get( $url ,
			array('timeout' => 3600 , 'headers' => array( 'Authorization' => 'Basic ' . base64_encode( $api_key ))
		));
		
		$body = wp_remote_retrieve_body( $response );
		$data_api = json_decode($body, true);
		$results[$key] = $data_api['total_results'];
	}
	
	
	$total_results = $data_api['total_results'];
	$total_response = 0;
	$positive_total_response = 0;
	$total_leads = 0;

	foreach($final_data as $finals){
		
		foreach($finals as $data){
			if(isset($data['custom']['First Response Sentiment'])){
				$total_response++;
				if($data['custom']['First Response Sentiment'] == 'Positive'){
					$total_positive_response++;
				}
			}
			if(isset($data['custom']['Qualified Lead Date'])){
				$total_leads++;
			}
		}
	}
	
	
	$response_rate = round($total_response/$total_results*100,2);
	$positive_response_rate_out_of_total_response = round($total_positive_response/$total_response*100,2);

	
	$reports = [];
	$i = 0;


	$all = 0;
	foreach($data_api['data'] as $api){
		if(!isset($api['custom']['First Response Sentiment'])) continue;
		$all++;
		$messages =  $api['custom']['# of Written Outreach'];
		
		$reports[$api['custom']['First Response Medium']][$messages]['total_response']++;
		if(isset($api['custom']['Qualified Lead Date'])){
			$reports[$api['custom']['First Response Medium']][$messages]['lead']++;    
		}
		if($api['custom']['First Response Sentiment'] == 'Positive'){
			$reports[$api['custom']['First Response Medium']][$messages]['positive']++;    
		}

	}


	


	foreach($reports as $key => $report){
		
		foreach($report as $index => $data){

			
			$reports[$key][$index]['positive_reply'] = round($data['positive'] / $data['total_response'] * 100,2);
			$reports[$key][$index]['replies_to_message_out_total'] = round($data['total_response'] / $all * 100,2);
			
			if(!isset($reports[$key][$index]['lead'])){
				$reports[$key][$index]['lead'] = 0;
			}
			if(!isset($reports[$key][$index]['positive'])){
				$reports[$key][$index]['positive'] = 0;
			}
		}
	}

	if(is_nan($positive_response_rate_out_of_total_response)){
		
		$positive_response_rate_out_of_total_response = 0;
	}

	



	

	$report_data = array(
		"global_total_companies" => $results['global_total_companies'],
		"total_response" => $results['total_response'],
		"total_positive_response" => $results['total_positive_response'],
		"response_rate" => round($results['total_response']/$results['global_total_companies']*100,2),
		"positive_response_rate_out_of_total_response" =>  round($results['total_positive_response']/$results['global_total_companies']*100,2),
		"total_leads" => $results['total_leads'],
		"responses" => $html,
	);

	
	
	wp_die(json_encode(array("report_data"=>$report_data,"reports" => $reports)));
}


/**
 * The call back ajax function to update the report data depend on selected filters.
 */
function lead_generation_metrics_report_ajax_call_back() {


	global $channel_custom_field_id;
	$filters = filter_input(INPUT_GET, 'data', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
	
	$default_selected_filter = constant('LEAD_GENERATION_METRICS_DEFULT_SELECTED_FILTERS');
	if (is_array($filters)) {
		$filters = array_merge($filters, $default_selected_filter);
	} else {
		$filters = $default_selected_filter;
	}

	// Get the "the First Response Medium" custom field id
	$channel_custom_field_id = filter_input(INPUT_GET, 'channel_custom_field_id');
	
	lead_generation_metrics_report_data($filters);
	wp_die();
}


function lead_generation_metrics_lead_report_ajax_call_back() {

	
	global $channel_custom_field_id;
	$filters = filter_input(INPUT_GET, 'data', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

	$default_selected_filter = constant('LEAD_GENERATION_METRICS_DEFULT_SELECTED_FILTERS_LEAD');
	if (is_array($filters)) {
		$filters = array_merge($filters, $default_selected_filter);
	} else {
		$filters = $default_selected_filter;
	}
	
	// Get the "the First Response Medium" custom field id
	$channel_custom_field_id = filter_input(INPUT_GET, 'channel_custom_field_id');
	lead_generation_metrics_report_data($filters);
	wp_die();
}
?>