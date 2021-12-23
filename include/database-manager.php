<?php 
function create_close_reports_caching_table(){
    global $wpdb;

    $table_name = $wpdb->prefix . "close_reports"; 

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        created_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        report_name varchar(100) DEFAULT '' NOT NULL,
        report_data mediumtext NOT NULL,
        user_id mediumint(9) NOT NULL,
        channel_custom_field_id varchar(500) DEFAULT '' NOT NULL,
        PRIMARY KEY  (id)
      ) $charset_collate;";
      
      require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
      dbDelta( $sql );

}
function caching_report_data($report_name, $report_data, $user_id, $channel_custom_field_id){
    global $wpdb;

    $table_name = $wpdb->prefix . "close_reports"; 

    $wpdb->insert( 
        $table_name, 
        array( 
            'created_time' => current_time( 'mysql' ), 
            'report_name' => $report_name, 
            'report_data' => $report_data, 
            'user_id' => $user_id,
            'channel_custom_field_id' => $channel_custom_field_id, 

        ) 
    );
    
}

function retrieve_report_caching_data($report_name, $user_id){
    global $wpdb;

    $table_name = $wpdb->prefix . "close_reports"; 
    $wpdb->query("set wait_timeout = 1200");
    $report_data = $wpdb->get_row( "SELECT * FROM ".$table_name." WHERE report_name = '". $report_name ."' And user_id = '". $user_id . "'" );

    return $report_data;
}

function retrieve_report_caching_users($report_name){
    global $wpdb;

    $table_name = $wpdb->prefix . "close_reports"; 
    $wpdb->query("set wait_timeout = 1200");
    $users = $wpdb->get_results( "SELECT distinct(user_id) FROM ".$table_name." WHERE report_name = '". $report_name ."'" );

    return $users;
}

function clear_report_caching_data($report_name, $user_id){
    global $wpdb;

    $table_name = $wpdb->prefix . "close_reports"; 
    $wpdb->query("set wait_timeout = 1200");
    $deleted = $wpdb->query( "DELETE FROM ".$table_name." WHERE report_name = '". $report_name ."' And user_id ='" . $user_id ."' " );
}
?>