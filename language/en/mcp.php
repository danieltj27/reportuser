<?php

/**
 * @package Report User
 * @copyright (c) 2026 Daniel James
 * @license https://opensource.org/license/gpl-2-0
 */

if ( ! defined( 'IN_PHPBB' ) ) {

	exit;

}

if ( empty( $lang ) || ! is_array( $lang ) ) {

	$lang = [];

}

$lang = array_merge( $lang, [
	'MCP_USER_REPORTS_OPEN'					=> 'Open user reports',
	'MCP_USER_REPORTS_CLOSED'				=> 'Closed user reports',
	'MCP_USER_REPORTS_DETAILS'				=> 'User report details',
	'MCP_USER_REPORTS_OPEN_EXPLAIN'			=> 'This is a list of all user reports that are open and need review.',
	'MCP_USER_REPORTS_CLOSED_EXPLAIN'		=> 'This is a list of all user reports that have been closed.',
	'MCP_USER_REPORTS_TOTAL_OPEN_REPORTS'	=> [
		0 => 'No open reports',
		1 => '%d open report',
		2 => '%d open reports',
	],
	'MCP_USER_REPORTS_TOTAL_CLOSED_REPORTS'	=> [
		0 => 'No closed reports',
		1 => '%d closed report',
		2 => '%d closed reports',
	],
	'MCP_USER_REPORTS_LATEST_TITLE'			=> 'Latest 5 user reports',
	'MCP_USER_REPORTS_LATEST_OVERVIEW'		=> [
		0 => 'There are no user reports to review.',
		1 => 'In total there is <strong>%d</strong> user report to review.',
		2 => 'In total there are <strong>%d</strong> user reports to review.',
	],
	'MCP_USER_REPORTS_COLUMN_USERNAME'		=> 'Reported user',
	'MCP_USER_REPORTS_COLUMN_REPORTER'		=> 'Reported by',
	'MCP_USER_REPORTS_COLUMN_REASON'		=> 'Reason',
	'MCP_USER_REPORTS_COLUMN_TIME'			=> 'Time',
	'MCP_USER_REPORTS_COLUMN_REVIEW'		=> 'Review',
	'MCP_USER_REPORTS_BUTTON_VIEW_REPORT'	=> 'View report',
	'MCP_USER_REPORTS_NO_RESULTS'			=> 'No user reports were found.',

	// REPORT DETAILS
	'MCP_USER_REPORTS_REPORT_INFO_TITLE'		=> 'Report #%d',
	'MCP_USER_REPORTS_REPORT_BY_USER'			=> 'Reported by %s',
	'MCP_USER_REPORTS_REPORT_CLOSED_NOTICE'		=> 'This report has been closed.',
	'MCP_USER_REPORTS_USER_DETAILS_TITLE'		=> 'User information',
	'MCP_USER_REPORTS_USER_DETAILS_EXPLAIN'		=> 'This is a preview of the user\'s profile which they may have updated since the report was sent.',
	'MCP_USER_REPORTS_USER_NAME_PLACEHOLDER'	=> 'unknown user %d',
	'MCP_USER_REPORTS_REQUEST_CHANGES_TITLE'	=> 'Request changes',
	'MCP_USER_REPORTS_REQUEST_CHANGES_EXPLAIN'	=> 'Send a notification to the reported user asking them to update their profile. This notification will also be saved as a moderator note on this user\'s profile.',
	'MCP_USER_REPORTS_REQUEST_CHANGES_LABEL'	=> 'Message',
	'MCP_USER_REPORTS_REQUEST_CHANGES_SUBMIT'	=> 'Request changes',

	// CONFIRM MESSAGES
	'MCP_USER_REPORTS_ACTION_CONFIRM_CLOSE'		=> [
		1 => 'Are you sure you would like to close this report?',
		2 => 'Are you sure you would like to close the reports?',
	],
	'MCP_USER_REPORTS_ACTION_CONFIRM_DELETE'	=> [
		1 => 'Are you sure you would like to delete this report?',
		2 => 'Are you sure you would like to delete the reports?',
	],
	'MCP_USER_REPORTS_ACTION_CONFIRM_CHANGES'	=> 'Are you sure that you would like to send the request changes notification?',

	// SUCCESS MESSAGES
	'MCP_USER_REPORTS_SUCCESS_REPORTS_CLOSED'		=> [
		1 => 'The selected report has been successfully closed.',
		2 => 'The selected reports have been successfully closed.',
	],
	'MCP_USER_REPORTS_SUCCESS_REPORTS_DELETED'		=> [
		1 => 'The selected report has been successfully deleted.',
		2 => 'The selected reports have been successfully deleted.',
	],
	'MCP_USER_REPORTS_SUCCESS_CHANGES_REQUESTED'	=> 'You have sent a notification requesting profile changes.',

	// RETURNS
	'MCP_USER_REPORTS_RETURN_MCP'		=> '%1$sReturn to the Moderator Control Panel%2$s',
	'MCP_USER_REPORTS_RETURN_MODULE'	=> '%1$sReturn to the list of user reports%2$s',
	'MCP_USER_REPORTS_RETURN_DETAILS'	=> '%1$sReturn to the user report%2$s',

	// ERRORS
	'MCP_USER_REPORTS_ERROR_MODERATOR_PERMISSION'	=> 'You do not have permission to manage user reports.',
	'MCP_USER_REPORTS_ERROR_INVALID_FORM_ACTION'	=> 'You have submitted an invalid form action.',
	'MCP_USER_REPORTS_ERROR_EMPTY_REPORT_ARRAY'		=> 'You must select at least one report to close or delete.',
	'MCP_USER_REPORTS_ERROR_INVALID_REPORT_ID'		=> 'You have submitted an invalid report identifier.',
	'MCP_USER_REPORTS_ERROR_INCORRECT_CSRF_TOKEN'	=> 'You have submitted an incorrect CSRF token.',
	'MCP_USER_REPORTS_ERROR_REPORT_NOT_FOUND'		=> 'The selected report does not exist.',
	'MCP_USER_REPORTS_ERROR_REQUEST_CHANGES'		=> 'There was an error attempting to send the request changes notification.',

	// LOGS
	'MCP_USER_REPORT_LOG_CLOSED_REPORT'		=> '<strong>Closed user Report:</strong><br />» %1$s (user_id: %2$d) ',
	'MCP_USER_REPORT_LOG_DELETED_REPORT'	=> '<strong>Deleted user report:</strong><br />» %1$s (user_id: %2$d)',
	'MCP_USER_REPORT_LOG_REQUESTED_CHANGES'	=> '<strong>Requested profile changes:</strong><br />User report reference: %1$d<br />» %2$s',
] );
