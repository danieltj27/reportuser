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
	'REPORT_USER_NOTIFICATIONS_NEW_REPORT_TITLE'		=> '<strong>User reported</strong>: %s',
	'REPORT_USER_NOTIFICATIONS_NEW_REPORT_REFERENCE'	=> '%s reported this user.',
	'REPORT_USER_NOTIFICATIONS_NEW_REPORT_REASON'		=> '<em>Reason</em>: %s',

	// UCP
	'REPORT_USER_NOTIFICATIONS_NEW_REPORT_SAMPLE'		=> 'Someone reports a user',
] );
