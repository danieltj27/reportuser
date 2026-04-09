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
	'MCP_USER_REPORTS'			=> 'Open user reports',
	'MCP_USER_REPORTS_CLOSED'	=> 'Closed user reports',

	'USER_REPORT_TH_USERNAME'	=> 'Reported user',
	'USER_REPORT_TH_BY'			=> 'Reported by',
	'USER_REPORT_TH_REASON'		=> 'Reason',
	'USER_REPORT_TH_TIME'		=> 'Time',
	'USER_REPORT_TH_REVIEW'		=> 'Review',
] );
