<?php

/**
 * @package Report User
 * @copyright (c) 2026 Daniel James
 * @license https://opensource.org/license/gpl-2-0
 */

namespace danieltj\reportuser\includes;

use phpbb\auth\auth;
use phpbb\db\driver\driver_interface as database;
use phpbb\routing\helper as router;
use phpbb\user;

final class functions {

	/**
	 * @var auth
	 */
	protected $auth;

	/**
	 * @var driver_interface
	 */
	protected $database;

	/**
	 * @var router
	 */
	protected $router;

	/**
	 * @var user
	 */
	protected $user;

	/**
	 * Constructor
	 */
	public function __construct( auth $auth, database $database, router $router, user $user ) {

		$this->auth = $auth;
		$this->database = $database;
		$this->router = $router;
		$this->user = $user;

	}

	/**
	 * Returns whether the given user can report other users.
	 * 
	 * @todo this needs a more thorough check
	 *       - can view profiles
	 *       - cannot report self
	 *       - not have open report already
	 * 
	 * @param integer $user_id (optional) The user ID to check permissions for
	 *                         reporting other users. Leave this blank to check
	 *                         the currently authenticated user instead.
	 * 
	 * @return boolean  True if allowed, false if not.
	 */
	public function can_report_user( $user_id = 0 ) {

		$user_id = (int) $user_id;

		// Must be able to view profiles.
		if ( ! $this->auth->acl_get( 'u_viewprofile' ) ) {

			return false;

		}

		// Cannot report yourself or guests.
		if ( $user_id === (int) $this->user->data[ 'user_id' ] || ANONYMOUS === (int) $this->user->data[ 'user_id' ] ) {

			return false;

		}

		return true;

	}

	/**
	 * Returns URL to submit a new user report.
	 * 
	 * @param integer $user_id The user ID to include in the link.
	 * 
	 * @return string  The report user URL.
	 */
	public function get_report_user_url( $user_id ) {

		return $this->router->route( 'report_user_mcp_create_report', [
			'id' => (int) $user_id,
		] );

	}

}
