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
	'REPORT_USER'				=> 'Report User',
	'REPORT_USER_THIS_USER' 	=> 'You are creating a report for %s.',

	'REPORT_REASON_LABEL'			=> 'Report Information',
	'REPORT_REASON_DESCRIPTION'		=> 'This field is required and cannot be left blank.',

	'REPORT_USER_ERROR_INVALID_HTTP'		=> 'You cannot request this page using that method.',
	'REPORT_USER_ERROR_INVALID_PERMISSIONS'	=> 'You cannot submit a report for this user.',
	'REPORT_USER_ERROR_INVALID_USER'		=> 'You cannot report a user that does not exist.',
	'REPORT_USER_ERROR_INVALID_CSRF'		=> 'You have submitted an invalid form.',
	'REPORT_USER_ERROR_INVALID_REASON'		=> 'You must include a reason for your report.',
	'REPORT_USER_ERROR_UNKNOWN_ERROR'		=> 'An unexpected error occurred whilst trying to create your report.',
	'REPORT_USER_ERROR_REPORT_SUCCESS'		=> 'Thank you for reporting the selected user.',
] );
