<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

// Close API constants.
define("CLOSE_API_PATH", 'https://api.close.com/api/v1/');
define("CLOSE_API_LEAD_CUSTOM_FIELDS", "custom_field/lead/");
define("CLOSE_API_CONTACT_CUSTOM_FIELDS", "custom_field/contact/");
define("CLOSE_API_USERS", "user/");
define("CLOSE_API_LEAD", 'lead/');
define("CLOSE_API_OPPORTUNITY_STATUS", 'status/opportunity/');

// Close API response object keys
define("CUSTOM", 'custom');
define("OPPORTUNITIES", 'opportunities');

// Earliest year in drop down list for "the First Outreach Date" custom field
define("EARLIEST_YEAR", 2019);

define("USER_META_LEAD_CUSTOM_FIELD", "close_lead_custom_fields");
define("USER_META_CONTACT_CUSTOM_FIELD", "close_contact_custom_fields");
define("USER_META_CONTACT_OUTREACH_NUMBER", "number_of_written_outreach");
define("USER_META_CONTACT_API_KEY", "API_KEY");


// The default value of maximum number of "# of written outreach" records will display in the table
define("MAX_OUTREACH", 3);


