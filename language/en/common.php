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
	'REPORT_USER'					=> 'Report User',
	'REPORT_USER_THIS_USER' 		=> 'You are creating a report for %s.',

	'REPORT_REASON_LABEL'			=> 'Report reason',
	'REPORT_REASON_DESCRIPTION'		=> 'This field is required and cannot be left blank.',

	// ERROR MESSAGES
	'REPORT_USER_ERROR_INVALID_HTTP'		=> 'You cannot request this page using that method.',
	'REPORT_USER_ERROR_INVALID_REPORT'		=> 'You cannot submit a report for the selected user.',
	'REPORT_USER_ERROR_INVALID_CSRF'		=> 'You have submitted an invalid form token.',
	'REPORT_USER_ERROR_INVALID_REASON'		=> 'You must include a reason for your report.',
	'REPORT_USER_ERROR_INVALID_REQUEST'		=> 'You have submitted an invalid request.',
	'REPORT_USER_ERROR_NO_REPORT_SELECTED'	=> 'You have not selected any reports to close or delete.',
	'REPORT_USER_ERROR_NOT_MODERATOR'		=> 'You do not have permission to manage user reports.',
	'REPORT_USER_ERROR_UNKNOWN_ERROR'		=> 'An unexpected error occurred whilst trying to create your report.',
	'REPORT_USER_ERROR_REPORTS_DELETED'		=> [
		1 => 'The selected report has been deleted.',
		2 => 'The selected reports have been deleted.',
	],
	'REPORT_USER_ERROR_REPORTS_CLOSED'		=> [
		1 => 'The selected report has been closed.',
		2 => 'The selected reports have been closed.',
	],
	'REPORT_USER_ERROR_REPORT_SUBMITTED'	=> 'Thank you for reporting the selected user.',
] );
