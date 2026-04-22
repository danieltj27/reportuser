<?php

/**
 * @package Report User
 * @copyright (c) 2026 Daniel James
 * @license https://opensource.org/license/gpl-2-0
 */

namespace danieltj\reportuser\includes;

use phpbb\auth\auth;
use phpbb\db\driver\driver_interface as database;
use phpbb\language\language;
use phpbb\log\log;
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
	 * @var language
	 */
	protected $language;

	/**
	 * @var log
	 */
	protected $log;

	/**
	 * @var router
	 */
	protected $router;

	/**
	 * @var user
	 */
	protected $user;

	/**
	 * @var datetime
	 */
	protected $datetime;

	/**
	 * Constructor
	 */
	public function __construct( auth $auth, database $database, language $language, log $log, router $router, user $user, $datetime_class ) {

		$this->auth = $auth;
		$this->database = $database;
		$this->language = $language;
		$this->log = $log;
		$this->router = $router;
		$this->user = $user;
		$this->datetime = $datetime_class;

	}

	/**
	 * Returns whether the user can report the specified user.
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
	 * Returns whether the user has open reports against them.
	 * 
	 * @param int $user_id The user ID used to search for.
	 * 
	 * @return bool  True if reported, false if not.
	 */
	public function is_user_reported( int $user_id ) : bool {

		$result = $this->database->sql_query(
			'SELECT * FROM ' . REPORTS_TABLE . ' WHERE ' . $this->database->sql_build_array( 'SELECT', [
				'reported_user_id' => $user_id,
			] )
		);

		$reports = $this->database->sql_fetchrow( $result );
		$this->database->sql_freeresult( $result );

		if ( false === $reports ) {

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
		$reported_user = $this->get_user_data( [ $options[ 'reported_user_id' ] ] );

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
	 * Return the data of a user report.
	 * 
	 * @param int $report_id The user report ID.
	 * 
	 * @return array|bool  An array of report data or false if it cannot be found.
	 */
	public function get_user_report( int $report_id ) : array|bool {

		$result = $this->database->sql_query(
			'SELECT * FROM ' . REPORTS_TABLE . ' WHERE ' . $this->database->sql_build_array( 'SELECT', [
				'report_id' => $report_id,
			] )
		);

		$report = $this->database->sql_fetchrow( $result );
		$this->database->sql_freeresult( $result );

		return $report;

	}

	/**
	 * Close a user report.
	 * 
	 * @todo add user notification (if requested)
	 * 
	 * @param int $report_id A report id.
	 * 
	 * @return bool  True if successful, false if failed.
	 */
	public function close_user_report( int $report_id ) : bool {

		$report_data = $this->get_user_report( $report_id );

		if ( false === $report_data ) {

			return false;

		}

		$this->database->sql_query(
			'UPDATE ' . REPORTS_TABLE . ' SET ' . $this->database->sql_build_array( 'UPDATE', [
				'report_closed' => 1,
			] ) . ' WHERE ' . $this->database->sql_build_array( 'SELECT', [
				'report_id' => $report_data[ 'report_id' ],
			] )
		);

		if ( false === $this->database->sql_affectedrows() ) {

			return false;

		}

		$user_data = $this->get_user_data( [ $report_data[ 'reported_user_id' ] ] );
		$reported_user_name = '';

		if ( ! empty( $user_data ) ) {

			$this_user = array_first( $user_data );

			$reported_user_name = get_username_string( 'no_profile', $this_user[ 'user_id' ], $this_user[ 'username' ], $this_user[ 'user_colour' ] );

		}

		$this->log->add(
			'mod',
			$this->user->data[ 'user_id' ],
			$this->user->data[ 'user_ip' ],
			'MCP_USER_REPORT_LOG_CLOSED_REPORT',
			time(),
			[
				'reported_user_name'	=> $reported_user_name,
				'reported_user_id'		=> $report_data[ 'reported_user_id' ],
			]
		);

		return true;

	}

	/**
	 * Delete an existing user report.
	 * 
	 * @todo add user notification (if requested)
	 * 
	 * @param int $report_id A report id.
	 * 
	 * @return bool  True if successful, false if failed.
	 */
	public function delete_user_report( int $report_id ) : bool {

		$report_data = $this->get_user_report( $report_id );

		if ( false === $report_data ) {

			return false;

		}

		$this->database->sql_query(
			'DELETE FROM ' . REPORTS_TABLE . ' WHERE ' . $this->database->sql_build_array( 'SELECT', [
				'report_id' => $report_data[ 'report_id' ],
			] )
		);

		if ( false === $this->database->sql_affectedrows() ) {

			return false;

		}

		$user_data = $this->get_user_data( [ $report_data[ 'reported_user_id' ] ] );
		$reported_user_name = '';

		if ( ! empty( $user_data ) ) {

			$this_user = array_first( $user_data );

			$reported_user_name = get_username_string( 'no_profile', $this_user[ 'user_id' ], $this_user[ 'username' ], $this_user[ 'user_colour' ] );

		}

		$this->log->add(
			'mod',
			$this->user->data[ 'user_id' ],
			$this->user->data[ 'user_ip' ],
			'MCP_USER_REPORT_LOG_DELETED_REPORT',
			time(),
			[
				'reported_user_name'	=> $reported_user_name,
				'reported_user_id'		=> $report_data[ 'reported_user_id' ],
			]
		);

		return true;

	}

	/**
	 * Return a collection of user reports.
	 * 
	 * @param int   $report_id    The report ID to fetch, ignores all other parameters.
	 * @param array $query        Array of query parameters used to search for reports.
	 * @param array $order_by     Array containing order by parameters.
	 * @param array $limit_offset Array containing limit and offset parameters.
	 * 
	 * @return array  An array containing user reports.
	 */
	public function get_user_reports( int $report_id = 0, array $query = [], array $order_by = [], array $limit_offset = [] ) : array {

		// Columns allowed in queries.
		$allowed_columns = [
			'report_id',
			'report_closed',
			'report_time',
			'report_text',
			'user_id',
			'reported_user_id',
		];

		// Placeholders for sql query.
		$where = '';
		$order = '';
		$limit = '';

		if ( 0 !== $report_id ) {

			$where = $this->database->sql_build_array( 'SELECT', [
				'report_id' => $report_id,
			] );

		} elseif ( ! empty( $query ) ) {

			$where_collection = [];

			foreach ( $query as $key => $value ) {

				if ( isset( $value[ 0 ] ) && isset( $value[ 1 ] ) && isset( $value[ 2 ] ) && in_array( $value[ 0 ], $allowed_columns, true ) && in_array( $value[ 1 ], [ '=', '!=', '>', '<', '>=', '<=', 'IN', 'NOT IN' ], true ) ) {

					$where_collection[] = ( 'IN' === $value[ 1 ] || 'NOT IN' === $value[ 1 ] ) ? $value[ 0 ] . ' ' . $value[ 1 ] . '(' . $value[ 2 ] . ')' : $value[ 0 ] . ' ' . $value[ 1 ] . ' ' . $value[ 2 ];

				}

			}

			if ( ! empty( $where_collection ) ) {

				$where = ' WHERE ' . implode( ' AND ', $where_collection );

			}

		}

		if ( ! empty( $order_by ) ) {

			$order_collection = [];

			foreach ( $order_by as $key => $value ) {

				if ( isset( $value[ 0 ] ) && isset( $value[ 1 ] ) && in_array( $value[ 0 ], $allowed_columns, true ) && in_array( $value[ 1 ], [ 'ASC', 'DESC' ], true ) ) {

					$order_collection[] = $value[ 0 ] . ' ' . $value[ 1 ];

				}

			}

			if ( ! empty( $order_collection ) ) {

				$order  = ' ORDER BY ' . implode( ', ', $order_collection );

			}

		}

		if ( ! empty( $limit_offset ) ) {

			$limit =  ' LIMIT ' . implode( ', ', $limit_offset );

		}

		$result = $this->database->sql_query( 'SELECT * FROM ' . REPORTS_TABLE . $where . $order . $limit );

		$reports = $this->database->sql_fetchrowset( $result );

		$this->database->sql_freeresult( $result );

		if ( false === $reports ) {

			return [];

		}

		return $reports;

	}

	/**
	 * Return the total number of user reports.
	 * 
	 * @param bool $open Flag that counts open or closed user reports.
	 * 
	 * @return int  The total number of user reports (open or closed).
	 */
	public function get_user_report_total( string $report_type = 'open' ) : int {

		$where = match ( $report_type ) {
			'closed' => 'reported_user_id != 0 AND report_closed = 1',
			default => 'reported_user_id != 0 AND report_closed = 0',
		};

		$result = $this->database->sql_query(
			'SELECT count(*) FROM ' . REPORTS_TABLE . ' WHERE ' . $where
		);

		$report_count = $this->database->sql_fetchrow( $result );

		$this->database->sql_freeresult( $result );

		return $report_count[ 'count(*)' ];

	}

	/**
	 * Return an array of user data.
	 * 
	 * @param array $user_ids An array containing user IDs.
	 * 
	 * @return array  An array of user data (can be empty).
	 */
	public function get_user_data( array $user_ids ) : array {

		if ( empty( $user_ids ) ) {

			return [];

		}

		$result = $this->database->sql_query(
			'SELECT * FROM ' . USERS_TABLE . ' WHERE ' . $this->database->sql_in_set( 'user_id', $user_ids )
		);

		$user_data = $this->database->sql_fetchrowset( $result );
		$this->database->sql_freeresult( $result );

		if ( false === $user_data ) {

			return [];

		}

		$users = [];

		foreach ( $user_data as $user ) {

			$users[ $user[ 'user_id' ] ] = $user;

		}

		return $users;

	}

	/**
	 * Return an array of custom fields data.
	 * 
	 * @param int $user_id The user ID to fetch profile fields for.
	 * 
	 * @return array  An array containing custom profile fields.
	 */
	public function get_user_cpf_data( int $user_id ) : array {

		// The users formatted profile fields.
		$fields = [];

		$result = $this->database->sql_query(
			'SELECT * FROM ' . PROFILE_FIELDS_DATA_TABLE . ' WHERE ' . $this->database->sql_build_array( 'SELECT', [
				'user_id' => $user_id,
			] )
		);

		$user_cpf_data = $this->database->sql_fetchrow( $result );
		$this->database->sql_freeresult( $result );

		if ( false === $user_cpf_data ) {

			return [];

		}

		// Fetch the profile field data.
		$result = $this->database->sql_query( 'SELECT * FROM ' . PROFILE_FIELDS_TABLE );

		$field_data = $this->database->sql_fetchrowset( $result );
		$this->database->sql_freeresult( $result );

		if ( false !== $field_data ) {

			// Fetch the language string data.
			$result = $this->database->sql_query( 'SELECT * FROM ' . PROFILE_LANG_TABLE );

			$lang_data = $this->database->sql_fetchrowset( $result );
			$this->database->sql_freeresult( $result );

			if ( false !== $lang_data ) {

				foreach ( $field_data as $field ) {

					$fields[ $field[ 'field_id' ] ] = [
						'language'	=> false,
						'key'		=> $field[ 'field_name' ],
						'value'		=> false,
					];

				}

				foreach ( $lang_data as $lang ) {

					if ( $fields[ $lang[ 'field_id' ] ] ) {

						$fields[ $lang[ 'field_id' ] ][ 'language' ] = $this->language->lang( $lang[ 'lang_name' ] );
						$fields[ $lang[ 'field_id' ] ][ 'value' ] = $user_cpf_data[ 'pf_' . $fields[ $lang[ 'field_id' ] ][ 'key' ] ];

					}

				}

			}

		}

		return $fields;

	}

	/**
	 * Return the user reports module URL.
	 * 
	 * @param string $mode   Module base name used to return module data.
	 * @param array  $params Additional parameters to add to the URL.
	 * 
	 * @return string  The module URL.
	 */
	public function get_mcp_module_url( string $module, array $params = [] ) : string {

		$module_url = './mcp.php';

		$result = $this->database->sql_query(
			'SELECT * FROM ' . MODULES_TABLE . ' WHERE ' . $this->database->sql_build_array( 'SELECT', [
				'module_basename' => $module,
			] )
		);

		$module = $this->database->sql_fetchrow( $result );
		$this->database->sql_freeresult( $result );

		if ( false === $module ) {

			return $module_url;

		}

		$module_url .= '?i=' . $module[ 'module_id' ];

		if ( is_array( $params ) && ! empty( $params ) ) {

			foreach ( $params as $key => $value ) {

				$module_url .= '&' . (string) $key . '=' . urlencode( (string) $value );

			}

		}

		return $module_url;

	}

	/**
	 * Return a localised version of a timestamp.
	 * 
	 * @param  string $zone (optional) An ISO formatted timezone code.
	 * @param  int    $time A UNIX timestamp.
	 * 
	 * @return string           A localised timestamp as a string.
	 */
	public function get_l10n_local_time( string $zone = 'UTC', int $time ) : string {

		try {

			/**
			 * Required for \phpbb\datetime wrapper.
			 * 
			 * @link https://www.php.net/manual/en/class.datetimezone.php
			 */
			$dtz = new \DateTimeZone( $zone );

		} catch ( \DateInvalidTimeZoneException $error ) {

			// Always fallback to UTC.
			$dtz = new \DateTimeZone( 'UTC' );

		}

		// phpBB wrapper class for php DateTime to localise timestamps.
		$datetime = new $this->datetime( $this->user, date( 'Y-m-d H:i:s', $time ), $dtz );

		return $datetime->format( $this->user->data[ 'user_dateformat' ], true );

	}

}
