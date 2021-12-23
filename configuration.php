<?php
// Close lead custom fields name
define("FIRST_RESPONSE_MEDIUM", "First Response Medium");
define("NUMBER_OF_WRITTEN_OUTREACH", "# of Written Outreach");
define("FIRST_RESPONSE_SENTIMENT", "First Response Sentiment");
define("LEAD_SOURCE", "Lead Source");
define("LEAD_OWNER", "Lead Owner");
define("COMPANY_SIZE", "Company Size");
define("FIRST_OUTREACH_DATE", "First Outreach Date");
define("QUALIFIED_LEAD","Qualified Lead?");
define("QUALIFIED_LEAD_DATE", "Qualified Lead Date");

// Data fields for "Pipeline Overview" and "Sales Process Metrics" reports.
define("PIPELINE_AND_SALES_DATA_FIELDS_REPORTS", "custom,display_name,opportunities");

// Data fields for "Lead Generation Metrics" report.
define("LEAD_DATA_FIELDS_REPORT", "custom");

// Default filters list for all reports.
define("DEFAULT_FILTERS_LIST", array('Lead Source', 'Lead Owner', 'Company Size', 'First Response Medium', 'First Response Sentiment', 'First Outreach Date'));
define("FIRST_OUTREACH_DATE_LIST", array('this week', 'this month', 'this quarter', 'this year', 'yesterday', 'last week', 'last month', 'last quarter', 'last year'));

// The order of the opportunity statuses will be shown in the "Pipeline report".
define("OPPORTUNITY_STATUSES_ORDER", array('Demo Set', 'Demo Completed', 'Proposal', 'Contract Sent', 'Contract Signed', 'Lost','Future Opportunity'));

// Marked value for "Qualified Lead?" custom field.
define("QUALIFIED_LEAD_MARKED_VALUE","Yes");

// Close contact custom fields name
define("CAMPAIGN", "Campaign");
define("JOB_TITLE", "Job Title Responded");

// Close custom fields 'First Response Sentiment' select choices.
define("POSITIVE", "Positive");
define("NEGATIVE", "Negative");

// Close opportunities fields name
define("STATUS_ID", "status_id");
// Close Opportunity status label key
define("STATUS_LABEL", "status_label");
// Date contract signed key
define("DATE_WON", "date_won");

// Close company name key
define("DISPLAY_NAME", "display_name");

// Opportunity statuses
define("CONTRACT_SIGNED", 'Contract Signed');

// The deafult selected filter for lead generation metric report.
define("LEAD_GENERATION_METRICS_DEFULT_SELECTED_FILTERS", array(array('name' => 'Lead Source', 'value' => 'ScaleUpSales lead')));
define("LEAD_GENERATION_METRICS_DEFULT_SELECTED_FILTERS_LEAD", array(array('name' => 'Lead Source', 'value' => 'ScaleUpSales lead lead')));

// Define the exclude select option from list
define("EXCLUDE_ITEM_FROM_SELECT_LIST", array('key'=> 'Lead Owner', 'value' => 'ScaleUpSales Lead Gen'));

// The order of the list of filters.
define("CUSTOM_FIELDS_ORDER", array('First Outreach Date', 'Lead Owner', 'Lead Source', 'Company Size', 'First Response Medium', 'First Response Sentiment'));

// Change opportunity statuses label.
define("CHANGE_OPPORTUNITY_STATUSES_LABEL", array('Demo Set' => 'Demos Set', 'Demo Completed' => 'Demos Completed', 'Proposal' => 'Proposals', 'Contract Sent' => 'Contracts Sent', 'Contract Signed' => 'Contracts Signed', 'Lost' => 'Lost Opportunities', 'Future Opportunity' => 'Future Opportunities'));
