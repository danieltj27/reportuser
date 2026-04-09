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
	 * Check if the current user can report the specified user.
	 * 
	 * @todo this needs a more thorough check
	 *       - can view profiles
	 *       - cannot report self
	 *       - not have open report already
	 * 
	 * @param integer $user_id  A user id.
	 * 
	 * @return bool  True if permission is allowed, false if not.
	 */
	public function can_report_user( int $user_id ) : bool {

		// Must be able to view profiles.
		if ( ! $this->auth->acl_get( 'u_viewprofile' ) ) {

			return false;

		}

		// Cannot report yourself or guests.
		if ( $user_id === (int) $this->user->data[ 'user_id' ] || ANONYMOUS === (int) $this->user->data[ 'user_id' ] ) {

			return false;

		}

		$result = $this->database->sql_query(
			'SELECT user_id FROM ' . USERS_TABLE . ' WHERE ' . $this->database->sql_build_array( 'SELECT', [
				'user_id' => $user_id,
			] )
		);

		$user = $this->database->sql_fetchrow( $result );
		$this->database->sql_freeresult( $result );

		if ( false === $user ) {

			return false;

		}

		return true;

	}

	/**
	 * Create a new user report.
	 * 
	 * @param array $options An array of report data.
	 * 
	 * @return int|bool  A report id or false.
	 */
	public function create_user_report( array $options ) : int|bool {

		$options = array_merge( [
			'reason_id'							=> 0,
			'post_id'							=> 0,
			'user_id'							=> 0,
			'pm_id'								=> 0,
			'reported_user_id'					=> 0,
			'user_notify'						=> 0,
			'report_closed'						=> 0,
			'report_time'						=> 0,
			'report_text'						=> '',
			'reported_post_enable_bbcode'		=> 0,
			'reported_post_enable_smilies'		=> 0,
			'reported_post_enable_magic_url'	=> 0,
			'reported_post_text'				=> '',
			'reported_post_uid'					=> '',
			'reported_post_bitfield'			=> '',
		], $options );

		// Check the reported user exists.
		$reported_user = $this->get_user_data( $options[ 'reported_user_id' ] );

		if ( false === $reported_user ) {

			return false;

		}

		$query = $this->database->sql_query(
			'INSERT INTO ' . REPORTS_TABLE . $this->database->sql_build_array( 'INSERT', $options )
		);

		if ( true !== $query ) {

			return false;

		}

		$report_id = $this->database->sql_nextid();

		return $report_id;

	}

	/**
	 * Close a user report.
	 * 
	 * @param int $report_id A report id.
	 * 
	 * @return bool  True if successful, false if failed.
	 */
	public function close_user_report( int $report_id ) : bool {

		$this->database->sql_query(
			'UPDATE ' . REPORTS_TABLE . ' SET ' . $this->database->sql_build_array( 'UPDATE', [
				'report_closed' => 1,
			] ) . ' WHERE ' . $this->database->sql_build_array( 'SELECT', [
				'report_id' => $report_id,
			] )
		);

		if ( false === $this->database->sql_affectedrows() ) {

			return false;

		}

		return true;

	}

	/**
	 * Delete an existing user report.
	 * 
	 * @param int $report_id A report id.
	 * 
	 * @return bool  True if successful, false if failed.
	 */
	public function delete_user_report( int $report_id ) : bool {

		$this->database->sql_query(
			'DELETE FROM ' . REPORTS_TABLE . ' WHERE ' . $this->database->sql_build_array( 'SELECT', [
				'report_id' => $report_id,
			] )
		);

		if ( false === $this->database->sql_affectedrows() ) {

			return false;

		}

		return true;

	}

	/**
	 * Return an array of user data.
	 * 
	 * @param integer $user_id A user id.
	 * 
	 * @return array|bool  An array of user data or false.
	 */
	public function get_user_data( int $user_id ) : array|bool {

		$result = $this->database->sql_query(
			'SELECT * FROM ' . USERS_TABLE . ' WHERE ' . $this->database->sql_build_array( 'SELECT', [
				'user_id' => $user_id,
			] )
		);

		$user = $this->database->sql_fetchrow( $result );
		$this->database->sql_freeresult( $result );

		if ( false === $user ) {

			return false;

		}

		return $user;

	}

}
