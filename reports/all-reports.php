<?php 

// Get the report data with filters (HTML).

add_action( 'wp_ajax_qualified_lead_get', 'qualified_lead_get' );
add_action( 'wp_ajax_nopriv_qualified_lead_get', 'qualified_lead_get' );
function qualified_lead_get(){

	$dates = explode(' to ',$_POST['data']['date']);
	
	$query = 'https://api.close.com/api/v1/lead/?_limit=200&_skip=0&query="custom.Qualified Lead?":"Yes" "custom.Qualified Lead Date" > "'.$dates[0].'" "custom.Qualified Lead Date" < "'.$dates[1].'"';
	
	$api_key = get_api_key(get_current_user_id());
	

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

function get_qualified_lead_campaigns($data){
	$campaigns = [];
	$options = '<option value="all">Filter by campaign</option>';
	
	foreach($data as $data_fields):
		foreach($data_fields->data as $d) :
			if(!in_array($d->custom->{'Campaign'},$campaigns)){
				array_push($campaigns,$d->custom->{'Campaign'});
				$options.="<option value='".$d->custom->{'Campaign'}."'>".$d->custom->{'Campaign'}."</option>";
			}
		endforeach;
	endforeach;

	return $options;
}


function make_qualified_lead_view($data){
	
	ob_start();
	?>
	<table>
		<thead>
			<tr>
				<td>#</td>
				<td>Name</td>
				<td>Status</td>
				<td>Qualified lead date</td>
				<td>URL</td>
				<td>Campaign</td>
				<td>Medium</td>
				<td>Message #</td>
			</tr>
		</thead>
		<tbody>
			<?php $counter = 0; ?>
			<?php foreach($data as $responses): ?>
				<?php foreach($responses->data as $d) :?>
					<?php $counter++; ?>
					<tr data-campaign="<?= $d->custom->{'Campaign'}; ?>">
						<td><?= $counter ?></td>
						<td><?= $d->display_name; ?></td>
						<td><?= $d->status_label; ?></td>
						<td><?= $d->custom->{'Qualified Lead Date'}; ?></td>
						<td><a href="<?= $d->url; ?>"><?= $d->url; ?></a></td>
						<td><?= $d->custom->{'Campaign'}; ?></td>
						<td><?= $d->custom->{'First Response Medium'}; ?></td>
						<td><?= $d->custom->{'# of Written Outreach'}; ?></td>
					</tr>
				<?php endforeach; ?>
			<?php endforeach; ?>
		<tbody>
	</table>

	<div class="qualified-count">
		<p>Total Leads: <span><?= $counter ?></span></p>
	</div>
	<?php 
	return ob_get_clean();

}



add_action( 'wp_ajax_get_report_tab', 'get_report_ajax_call_back' );
add_action( 'wp_ajax_nopriv_get_report_tab', 'get_report_ajax_call_back' );

function close_reports_page() {
	ob_start();

	if (!is_user_logged_in()) {
		
		global $wp;
		?>
		
		<script>window.location.href='<?php echo wp_login_url() . "?redirect_to=". $wp->request; ?>';</script>
		
		<?php
		
	} else  {
		
		$user_id = get_current_user_id();
		$user_info = wp_get_current_user();
    	$first_name = get_user_meta($user_id, 'first_name', true);
		$last_name = get_user_meta($user_id, 'last_name', true);
		$uploads = wp_upload_dir();
		?>
			<div class='report__wrapper d-flex flex-column'>
				<div class="header d-flex">
					<div class="col-3 d-flex align-items-center">
					
						<a class="d-block" href="/">
						<img class="header-logo-img" src="<?php echo $uploads['baseurl']; ?>/2020/04/logo-hd-2.png" alt="Logo">
					</a>
					</div>
						<ul class="nav col-7 align-content-center justify-content-around" id="headerTabs" role="tablist">
						<li class="nav-item nav-item-lead">
							<a class="nav-link-lead active" data-toggle="tab" id="lead-generation-metrics-tab" href="#lead-generation-metrics" role="tab" aria-controls="home" aria-selected="true">Campaign Metrics</a>
						</li>
						<li class="nav-item nav-item-lead">
							<a class="nav-link-lead" data-toggle="tab" id="sales-process-metrics-tab" href="#sales-process-metrics" role="tab" aria-controls="home" aria-selected="false">Sales Process Metrics</a>
						</li>
						<li class="nav-item nav-item-lead">
							<a class="nav-link-lead" data-toggle="tab" id="pipeline-overview-tab" href="#pipeline-overview" role="tab" aria-controls="home" aria-selected="false">Pipeline Overview</a>
						</li>
						<li class="nav-item nav-item-lead">
								<a class="nav-link-lead" data-toggle="tab" id="qualified-leads-tab" href="#qualified-leads" role="tab" aria-controls="home" aria-selected="false">Qualified Leads</a>
							</li>
					</ul>
					<a href="<?php echo get_edit_profile_url() ?>" class="avatar-container d-flex h-hand">
						<div class="my-auto">
							<div class="header-welcome-text">
								<div class="user-name"><?php echo esc_html($first_name) . ' ' . esc_html($last_name); ?></div>
								<div class="user-email"><?php echo esc_html($user_info->user_email); ?></div>
							</div>
						</div>
						<div class="avatar-name my-auto d-flex justify-content-center">
							<h5 class="text-center my-auto"><?php echo esc_html($first_name[0]) . esc_html($last_name[0]); ?></h5>
						</div>
					</a>
				</div>
				<div class="tab-content tab-content-container">
					<div class="tab-pane active" id="lead-generation-metrics" role="tabpanel" aria-labelledby="lead-generation-metrics-tab">
						<div class="d-flex lead_generation_metrics_container"></div>
					</div>
					<div class="tab-pane" id="sales-process-metrics" role="tabpanel" aria-labelledby="sales-process-metrics-tab">
						<div class="d-flex sales_process_metrics_container"></div>
					</div>
					<div class="tab-pane" id="pipeline-overview" role="tabpanel" aria-labelledby="pipeline-overview-tab">
						<div class="pipeline_overview_container"></div>
					</div>
					<div class="tab-pane" id="qualified-leads" role="tabpanel" aria-labelledby="qualified-leads-tab">
						<div class="qualified_leads_overview_container">
							<div class="qualified_leads_container">
								<div class="container">
									<div class="row">
										<h3 class="qualified-tab-title">Qualified Leads</h3>
										<div class="sub-filters d-flex">
											<div class="lead_generation_metrics_report_filters_leadtab">
												<div id="datepicker_lead_generation_metrics_report_leadtab" class="datepicker_lead_generation_metrics_report_leadtab">
													<input type="text" name="date_leadtab" report-name="lead_generation_metrics_report_leadtab" class="datepicker  datepicker_lead_generation_metrics_report_leadtab_input" value="Qualified Lead Date"/><i class="datepicker_icon"></i>	
												</div>
											</div>
										</div>
										<div class="quick-filters d-flex">
											<div class="quick-filter-box">
												<a href="#" data-quickfilter="1">All Time</a>
											</div>
											<div class="quick-filter-box">
												<a href="#" data-quickfilter="2">Yesterday</a>
											</div>
											<div class="quick-filter-box">
												<a href="#" data-quickfilter="3">Last 14 Days</a>
											</div>
											<div class="quick-filter-box">
												<a href="#" data-quickfilter="4">Last 30 Days</a>
											</div>
											<div class="quick-filter-box">
												<a href="#" data-quickfilter="5">Last Month</a>
											</div>
										</div>
										<select id="qualified-leads-campaign" class="hidden"></select>
										<div class="qualified_leads_wrapper"></div>
									</div>
								</div>
								
							</div>
						</div>
					</div>
				</div>
			</div>
		<?php
	
		?>
			<style>
				body {
					padding-bottom: 46px;
				}
			</style>
		<?php
	}
	?>
		<style>
			.elementor-location-header {
				display: none !important;
			}
			/* Footer at bottom */
			html {
				height: 100%;
			}

			body {
				min-height: 100%;
				padding-bottom: 46px;
				position: relative;
			}

			[data-elementor-type="footer"] {
				position: absolute;
				bottom: 0;
				left: 0;
				width: 100%;
			}
		</style>
	<?php
	return ob_get_clean();
}
//To get report tab content
function get_report_ajax_call_back(){
	
	if (filter_input(INPUT_GET, 'report_name')) {
		$report_name = filter_input(INPUT_GET, 'report_name');
		if($report_name == "pipeline_overview"){
			get_custom_fields("pipeline_overview_report");
			pipeline_overview_report_data(null);
		}else if($report_name == "sales_process_metrics"){
			sales_process_metrics_report(null);
		}else{
			$default_selected_filter = constant('LEAD_GENERATION_METRICS_DEFULT_SELECTED_FILTERS');
			get_custom_fields("lead_generation_metrics_report");
			
			lead_generation_metrics_report_data($default_selected_filter);
		}
	}
	wp_die();
}
add_shortcode('closeReportsPage', 'close_reports_page');