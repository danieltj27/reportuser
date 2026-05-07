<?php

/**
 * @package Report User
 * @copyright (c) 2026 Daniel James
 * @license https://opensource.org/license/gpl-2-0
 */

namespace danieltj\reportuser\includes;

use phpbb\auth\auth;
use phpbb\db\driver\driver_interface as database;
use phpbb\event\dispatcher_interface as dispatcher;
use phpbb\language\language;
use phpbb\log\log;
use phpbb\notification\manager as notifications;
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
	 * @var dispatcher_interface
	 */
	protected $dispatcher;

	/**
	 * @var language
	 */
	protected $language;

	/**
	 * @var log
	 */
	protected $log;

	/**
	 * @var notifications
	 */
	protected $notifications;

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
	public function __construct( auth $auth, database $database, dispatcher $dispatcher, language $language, log $log, notifications $notifications, router $router, user $user, $datetime_class ) {

		$this->auth = $auth;
		$this->database = $database;
		$this->dispatcher = $dispatcher;
		$this->language = $language;
		$this->log = $log;
		$this->notifications = $notifications;
		$this->router = $router;
		$this->user = $user;
		$this->datetime = $datetime_class;

	}

	/**
	 * Returns whether the user can report the specified user.
	 * 
	 * @param integer $user_id A user id.
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

		// Does this user exist?
		$users = $this->get_user_data( [ $user_id ] );

		if ( empty( $users ) ) {

			return false;

		}

		$user = array_first( $users );

		// Moderators can skip this.
		if ( ! $this->auth->acl_get( 'm_user_report' ) ) {

			$my_reports = $this->get_user_reports( query: [
				[ 'user_id', '=', (int) $this->user->data[ 'user_id' ] ], // This is me.
				[ 'report_closed', '=', 0 ], // Open reports only.
				[ 'reported_user_id', '=', (int) $user[ 'user_id' ] ],
			] );

			// You've already reported this user.
			if ( ! empty( $my_reports ) ) {

				return false;

			}

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

		$reports = $this->get_user_reports( query: [
			[ 'report_closed', '=', 0 ],
			[ 'reported_user_id', '=', $user_id ],
		] );

		if ( empty( $reports ) ) {

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

		// Trim whitespace from the report text and check it.
		$options[ 'report_text' ] = trim( $options[ 'report_text' ] );

		if ( 1 > strlen( $options[ 'report_text' ] ) ) {

			return false;

		}

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

		/**
		 * Event to hook into the create report workflow.
		 * 
		 * @event danieltj.reportuser.create_user_report_after
		 * @since 1.0.0-b2
		 * 
		 * @var int   $report_id     The report ID that was created.
		 * @var array $reported_user An array containing user data of the reported user.
		 * @var array $options       An array containing report data.
		 */
		$event = [ 'report_id', 'reported_user', 'options' ];
		extract( $this->dispatcher->trigger_event( 'danieltj.reportuser.create_user_report_after', compact( $event ) ) );

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

		// Mark all notifications (of this type) as read now the report is closed.
		$this->notifications->mark_notifications(
			'danieltj.reportuser.notification.type.new_report', // This type.
			$report_data[ 'report_id' ], // This report.
			false, // For all users.
			false, // Mark all read now.
			true // Mark as read.
		);

		// Notify the user that made the report?
		if ( 1 === (int) $report_data[ 'user_notify' ] ) {

			$this->notifications->add_notifications( 'danieltj.reportuser.notification.type.report_closed', [
				'report_id'			=> $report_id,
				'report_mod_id'		=> (int) $this->user->data[ 'user_id' ], // The moderator that is closing this report.
				'reporter_user_id'	=> (int) $report_data[ 'user_id' ],
				'reported_user_id'	=> (int) $report_data[ 'reported_user_id' ],
				'report_text'		=> $report_data[ 'report_text' ],
			] );

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

		/**
		 * Event to hook into the close report workflow.
		 * 
		 * @event danieltj.reportuser.close_user_report_after
		 * @since 1.0.0-b2
		 * 
		 * @var int   $report_id   The report ID that was closed.
		 * @var array $report_data An array containing report data.
		 */
		$event = [ 'report_id', 'report_data' ];
		extract( $this->dispatcher->trigger_event( 'danieltj.reportuser.close_user_report_after', compact( $event ) ) );

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

		// Delete any notifications created for this report.
		$this->notifications->delete_notifications(
			'danieltj.reportuser.notification.type.new_report',
			$report_data[ 'report_id' ]
		);

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

		/**
		 * Event to hook into the delete report workflow.
		 * 
		 * @event danieltj.reportuser.delete_user_report_after
		 * @since 1.0.0-b2
		 * 
		 * @var int   $report_id   The report ID that was deleted.
		 * @var array $report_data An array containing report data.
		 */
		$event = [ 'report_id', 'report_data' ];
		extract( $this->dispatcher->trigger_event( 'danieltj.reportuser.delete_user_report_after', compact( $event ) ) );

		return true;

	}

	/**
	 * Return the request changes notification for a report.
	 * 
	 * @param int $report_id The report ID this notification was created from.
	 * 
	 * @return array|bool  The array of notification data or false if it hasn't been sent.
	 */
	public function get_request_changes_notification( int $report_id ) : array|bool {

		// Fetch the type ID for the 'request_changes' notification.
		$result = $this->database->sql_query(
			'SELECT notification_type_id FROM ' . NOTIFICATION_TYPES_TABLE . ' WHERE ' . $this->database->sql_build_array( 'SELECT', [
				'notification_type_name' => 'danieltj.reportuser.notification.type.request_changes',
			] )
		);

		$type_data = $this->database->sql_fetchrow( $result );
		$this->database->sql_freeresult( $result );

		if ( false === $type_data ) {

			return false;

		}

		$type_id = (int) $type_data[ 'notification_type_id' ];

		// Fetch the notification data (if it exists).
		$result = $this->database->sql_query(
			'SELECT * FROM ' . NOTIFICATIONS_TABLE . ' WHERE ' . $this->database->sql_build_array( 'SELECT', [
				'notification_type_id'	=> $type_id,
				'item_id'				=> $report_id,
			] )
		);

		$notification_data = $this->database->sql_fetchrow( $result );
		$this->database->sql_freeresult( $result );

		if ( false === $notification_data ) {

			return false;

		}

		return $notification_data;

	}

	/**
	 * Send a request changes notification to a user.
	 * 
	 * @param int $report_id The report ID used to fetch the user.
	 * 
	 * @return bool  True if the notification was sent, false if not.
	 */
	public function send_request_changes_notification( int $report_id, string $notification_text ) : bool {

		$report_data = $this->get_user_report( $report_id );

		if ( false === $report_data ) {

			return false;

		}

		$user_data = $this->get_user_data( [ $report_data[ 'reported_user_id' ] ] );

		if ( false === $user_data ) {

			return false;

		}

		$reported_user = array_first( $user_data );

		$notification_text = trim( $notification_text );

		if ( 1 > strlen( $notification_text ) || $this->get_rcn_character_length() < strlen( $notification_text ) ) {

			return false;

		}

		$request_changes_notification = $this->get_request_changes_notification( $report_id );

		if ( false === $request_changes_notification ) {

			// This hasn't been sent before so create a new notification.
			$this->notifications->add_notifications( 'danieltj.reportuser.notification.type.request_changes', [
				'report_id'			=> $report_id,
				'report_mod_id'		=> (int) $this->user->data[ 'user_id' ],
				'reported_user_id'	=> (int) $report_data[ 'reported_user_id' ],
				'notification_text'	=> $notification_text,
			] );

		} else {

			// Update the existing notification data.
			$this->notifications->update_notifications( 'danieltj.reportuser.notification.type.request_changes', [
				'report_id'			=> $report_id,
				'report_mod_id'		=> (int) $this->user->data[ 'user_id' ],
				'reported_user_id'	=> (int) $report_data[ 'reported_user_id' ],
				'notification_text'	=> $notification_text,
			], [
				'notification_type_id'	=> $request_changes_notification[ 'notification_type_id' ],
				'item_id'				=> $request_changes_notification[ 'item_id' ],
			] );

			// Mark the notification as unread.
			$this->notifications->mark_notifications_by_id(
				'notification.method.board', // Only update forum-based notifications.
				(int) $request_changes_notification[ 'notification_id' ],
				false, // Mark all notification of this type prior to now as read.
				false // Mark this notification as unread.
			);

		}

		$this->log->add(
			'user',
			$this->user->data[ 'user_id' ],
			$this->user->data[ 'user_ip' ],
			'MCP_USER_REPORT_LOG_REQUESTED_CHANGES',
			time(),
			[
				'reportee_id'		=> $reported_user[ 'user_id' ],
				'report_id'			=> $report_id,
				'notification_text'	=> $notification_text,
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
	public function get_user_report_total( string $report_type = 'open', int $user_id = 0 ) : int {

		if ( 0 !== $user_id ) {

			$where_user = 'reported_user_id != ' . $user_id;

		} else {

			$where_user = 'reported_user_id != 0';

		}

		$where = match ( $report_type ) {
			'closed' => $where_user . ' AND report_closed = 1',
			default => $where_user . ' AND report_closed = 0',
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

						if ( '' !== $user_cpf_data[ 'pf_' . $fields[ $lang[ 'field_id' ] ][ 'key' ] ] ) {

							$fields[ $lang[ 'field_id' ] ][ 'language' ] = $this->language->lang( $lang[ 'lang_name' ] );
							$fields[ $lang[ 'field_id' ] ][ 'value' ] = $user_cpf_data[ 'pf_' . $fields[ $lang[ 'field_id' ] ][ 'key' ] ];

						} else {

							unset( $fields[ $lang[ 'field_id' ] ] );

						}

					}

				}

			}

		}

		return $fields;

	}

	/**
	 * Return all moderators that can manage user reports.
	 * 
	 * @param array $ignore_ids An array of user IDs to filter out.
	 * 
	 * @return array  An array of moderator user IDs.
	 */
	public function get_user_report_mod_ids( array $ignore_ids = [] ) : array {

		$user_ids = $this->auth->acl_get_list( false, 'm_user_report', 0 );

		if ( ! is_array( $user_ids ) || empty( $user_ids ) ) {

			return [];

		}

		if ( isset( $user_ids[ 0 ][ 'm_user_report' ] ) && ! empty( $ignore_ids ) ) {

			// Make sure all IDs are integers when we check type later.
			foreach ( $ignore_ids as $key => $value ) {

				$ignore_ids[ $key ] = (int) $value;

			}

			foreach ( $user_ids[ 0 ][ 'm_user_report' ] as $key => $user_id ) {

				// Check if we need to ignore this user ID.
				if ( in_array( $user_id, $ignore_ids, true ) ) {

					unset( $user_ids[ 0 ][ 'm_user_report' ][ $key ] );

				}

			}

			return $user_ids[ 0 ][ 'm_user_report' ];

		}

		return [];

	}

	/**
	 * Return the user reports module URL.
	 * 
	 * @param string $mode   Module base name used to return module data.
	 * @param array  $params Additional parameters to add to the URL.
	 * 
	 * @return string  The module URL.
	 */
	public function get_mcp_module_url( string $module, array $params = [], bool $board_url = true ) : string {

		$module_url = trim( generate_board_url(), '/' ) . '/mcp.php';

		if ( false === $board_url ) {

			$module_url = './mcp.php';

		}

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
	 * Return the profile URL of the specified user.
	 * 
	 * @param int $user_id The user ID to use.
	 * 
	 * @return string  The link to the user's profile.
	 */
	public function get_user_profile_url( int $user_id, bool $board_url = true ) : string {

		$profile_url = '';

		if ( true === $board_url ) {

			$profile_url = trim( generate_board_url(), '/' );

		}

		$profile_url .= append_sid( '/memberlist.php', [
			'mode'	=> 'viewprofile',
			'u'		=> $user_id,
		] );

		return $profile_url;

	}

	/**
	 * Returns the allowed character length of the notification text
	 * when a moderator requests changes from a reported user.
	 * 
	 * @return int  The allowed character length.
	 */
	public function get_rcn_character_length() : int {

		$length = 64;

		/**
		 * Event to hook into the character length for request changes notifications.
		 * 
		 * @event danieltj.reportuser.rcn_character_length
		 * @since 1.0.0-b3
		 * 
		 * @var int $length The allowed character length.
		 */
		$event = [ 'length' ];
		extract( $this->dispatcher->trigger_event( 'danieltj.reportuser.rcn_character_length', compact( $event ) ) );

		return (int) $length;

	}

	/**
	 * Return a localised version of a timestamp.
	 * 
	 * @param  string $zone (optional) An ISO formatted timezone code.
	 * @param  int    $time A UNIX timestamp.
	 * 
	 * @return string  A localised timestamp as a string.
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
