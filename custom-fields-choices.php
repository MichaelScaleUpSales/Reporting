<?php
function get_text_Custom_Fields_choices ($default_custom_field_details) {
    global $text_custom_Fields;
    global $user_custom_Fields;
    global $users_data;
    $api_key = get_api_key();


	// Get all organization leads data
    $response = wp_remote_get(  constant('CLOSE_API_PATH') . constant('CLOSE_API_LEAD'),
        array('timeout' => 10 , 'headers' => array( 'Authorization' => 'Basic ' . base64_encode( $api_key ))
    ));
    $body = wp_remote_retrieve_body( $response );
    $data_api = json_decode($body, true);

    if(!empty($data_api['data'])) {
        $custom_CONSTANT = constant('CUSTOM');
        $data = $data_api['data'];
        // Get all custom field values which their type is not choices
        for ($record = 0; $record < count($data); $record++) {
            if(!empty($data[$record][$custom_CONSTANT])) {
                $organization_custom_fields_CONSTANT = $data[$record][$custom_CONSTANT];
                foreach ($organization_custom_fields_CONSTANT as $key => $custom) {
                    $value = $custom;
                    if (in_array($key, $text_custom_Fields)) {
                        $current_values_list = $default_custom_field_details[$key];
                        if (array_key_exists('choices', $current_values_list)) {
                            $current_values = $default_custom_field_details[$key]['choices'];
                        } else {
                            $current_values = array();
                        }
                        if (!is_array($current_values)) {
                            $current_values = array();
                        }
                        if (!in_array($value, $current_values)) {
                            array_push($current_values, $value);
                        }
                        $default_custom_field_details[$key]['choices'] = $current_values;
                    }
                }
            }
        }
    }

    foreach($user_custom_Fields as $custom_field_key) {
        $current_values = array();
        $organization_id = $default_custom_field_details[$custom_field_key]['organization_id'];
        foreach($users_data as $user_data) {
            if (array_key_exists('organizations', $user_data)) {
                $user_organization_id = $user_data['organizations'];
                if(!empty($user_organization_id) && in_array ($organization_id, $user_organization_id)) {
                    $first_name = $user_data['first_name'];
                    $last_name = $user_data['last_name'];
                    $value = $first_name . ' ' . $last_name;
                    array_push($current_values, $value);
                }
            }

        }
        $default_custom_field_details[$custom_field_key]['choices'] = $current_values;
    }

    return $default_custom_field_details;
}

/**
 * Get the contact custom fields.
 */
function get_leads_users () {

    // Get lead custom fields.
    $response = wp_remote_get( constant('CLOSE_API_PATH') . constant('CLOSE_API_USERS') ,
        array('timeout' => 10 ,'headers' => array( 'Authorization' => 'Basic ' . base64_encode( get_api_key() ))
    ));

	$body     = wp_remote_retrieve_body( $response );
	$data_api = json_decode($body, true);
    if (array_key_exists('data', $data_api)) {
        $data = $data_api['data'];
    } else {
        $data = array();
    }

	return $data;
}
?>