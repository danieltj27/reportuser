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
	'REPORT_USER'						=> 'Report this user',
	'REPORT_USER_THIS_USER' 			=> 'You are creating a report for %s.',
	'REPORT_USER_NOTIFY_DESCRIPTION'	=> 'Receive a notification when this report is closed.',
	'REPORT_USER_REASON_LABEL'			=> 'Reason for report',
	'REPORT_USER_REASON_DESCRIPTION'	=> 'This field is required and cannot be left blank.',

	'REPORT_USER_SUCCESS_MESSAGE'		=> 'Thank you for reporting the selected user.',

	// ERRORS
	'REPORT_USER_ERROR_INVALID_HTTP_REQUEST'		=> 'You have submitted an invalid HTTP request.',
	'REPORT_USER_ERROR_INVALID_USER_PERMISSION'		=> 'You do not have permission to submit a user report.',
	'REPORT_USER_ERROR_INVALID_USER_SELECTED'		=> 'You cannot submit a report against the selected user.',
	'REPORT_USER_ERROR_INVALID_CSRF_TOKEN'			=> 'You have submitted an incorrect CSRF token.',
	'REPORT_USER_ERROR_INVALID_REPORT_REASON'		=> 'You have submitted an invalid report reason.',
	'REPORT_USER_ERROR_UNKNOWN_ERROR_MESSAGE'		=> 'An unexpected error occurred, please try again.',
] );
