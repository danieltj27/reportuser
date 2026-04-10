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
	'MCP_USER_REPORTS'				=> 'Open user reports',
	'MCP_USER_REPORTS_CLOSED'		=> 'Closed user reports',
	'MCP_USER_REPORTS_LATEST'		=> 'Latest 5 user reports',
	'MCP_USER_REPORTS_NO_RESULTS'	=> 'There are no user reports to review.',

	'USER_REPORT_TH_USERNAME'	=> 'Reported user',
	'USER_REPORT_TH_BY'			=> 'Reported by',
	'USER_REPORT_TH_REASON'		=> 'Reason',
	'USER_REPORT_TH_TIME'		=> 'Time',
	'USER_REPORT_TH_REVIEW'		=> 'Review',

	'MCP_REPORT_USER_ERROR_INVALID_HTTP'	=> 'You cannot access this route with an invalid request method.',
	'MCP_REPORT_USER_ERROR_ACCESS_DENIED'	=> 'You do not have permission to manage user reports.',
] );
