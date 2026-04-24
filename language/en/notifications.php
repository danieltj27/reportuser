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
	'REPORT_USER_NOTIFICATIONS_NEW_REPORT_REFERENCE'	=> '%s sent this report.',
	'REPORT_USER_NOTIFICATIONS_REPORT_CLOSED_TITLE'		=> '<strong>Report closed</strong> by %s',
	'REPORT_USER_NOTIFICATIONS_REPORT_CLOSED_REFERENCE'	=> 'You reported %s.',

	// UCP
	'REPORT_USER_NOTIFICATIONS_NEW_REPORT_SAMPLE'		=> 'Someone reports a user',
	'REPORT_USER_NOTIFICATIONS_REPORT_CLOSED_SAMPLE'	=> 'Your report on a user is closed by a moderator',
] );
