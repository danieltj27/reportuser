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
	'MCP_USER_REPORTS_OPEN'				=> 'Open user reports',
	'MCP_USER_REPORTS_CLOSED'			=> 'Closed user reports',
	'MCP_USER_REPORTS_OPEN_EXPLAIN'		=> 'This is a list of all user reports that are open and need review.',
	'MCP_USER_REPORTS_CLOSED_EXPLAIN'	=> 'This is a list of all user reports that have been resolved.',
	'MCP_USER_REPORTS_LATEST'			=> 'Latest 5 user reports',
	'MCP_USER_REPORTS_NO_RESULTS'		=> 'There are no user reports to review.',
	'MCP_USER_REPORTS_TYPE_TOTAL'		=> [
		1 => '1 report',
		2 => '%s reports',
	],
	'MCP_USER_REPORTS_LAST_5'			=> [
		1 => 'In total there is 1 report to review.',
		2 => 'In total there is %s reports to review.',
	],
	'MCP_USER_REPORTS_PAGE'				=> 'Page <strong>%s</strong> of <strong>%s</strong>',

	'USER_REPORT_COLUMN_USERNAME'	=> 'Reported user',
	'USER_REPORT_COLUMN_REPORTER'	=> 'Reported by',
	'USER_REPORT_COLUMN_REASON'		=> 'Reason',
	'USER_REPORT_COLUMN_TIME'		=> 'Time',
] );
