<?php

/**
 * @package Report User
 * @copyright (c) 2026 Daniel James
 * @license https://opensource.org/license/gpl-2-0
 */

namespace danieltj\reportuser\controller;

use phpbb\auth\auth;
use phpbb\controller\helper as controller;
use phpbb\event\dispatcher_interface as dispatcher;
use phpbb\language\language;
use phpbb\notification\manager as notifications;
use phpbb\pagination;
use phpbb\request\request;
use phpbb\routing\helper as router;
use phpbb\template\template;
use phpbb\user;
use danieltj\reportuser\includes\functions;

final class mcp {

	/**
	 * @var auth
	 */
	protected $auth;

	/**
	 * @var controller
	 */
	protected $controller;

	/**
	 * @var dispatcher_interface
	 */
	protected $dispatcher;

	/**
	 * @var language
	 */
	protected $language;

	/**
	 * @var notifications
	 */
	protected $notifications;

	/**
	 * @var pagination
	 */
	protected $pagination;

	/**
	 * @var request
	 */
	protected $request;

	/**
	 * @var router
	 */
	protected $router;

	/**
	 * @var template
	 */
	protected $template;

	/**
	 * @var user
	 */
	protected $user;

	/**
	 * @var functions
	 */
	protected $functions;

	/**
	 * Constructor
	 */
	public function __construct( auth $auth, controller $controller, dispatcher $dispatcher, language $language, notifications $notifications, pagination $pagination, request $request, router $router, template $template, user $user, functions $functions ) {

		$this->auth = $auth;
		$this->controller = $controller;
		$this->dispatcher = $dispatcher;
		$this->language = $language;
		$this->notifications = $notifications;
		$this->pagination = $pagination;
		$this->request = $request;
		$this->router = $router;
		$this->template = $template;
		$this->user = $user;
		$this->functions = $functions;

	}

	/**
	 * Handle the user reports interface.
	 */
	public function reports( string $module_id, string $action, string $mode ) {

		$this->language->add_lang( 'mcp' );

		if ( ! $this->auth->acl_get( 'm_user_report' ) ) {

			trigger_error( $this->language->lang( 'MCP_USER_REPORTS_ERROR_MODERATOR_PERMISSION' ) . '<br /><br />' . sprintf( $this->language->lang( 'RETURN_MCP' ), '<a href="' . $this->functions->get_mcp_module_url( 'mcp_main', [ 'mode' => 'front' ] ) . '">', '</a>' ), E_USER_WARNING );

		}

		// Check which report type to look at (open or closed).
		$reports_view = match ( $mode ) {
			'user_reports_closed'	=> 1, // id: \danieltj\reportuser\mcp\reports_closed_module
			'user_reports_open'		=> 0, // id: \danieltj\reportuser\mcp\reports_open_module
			default					=> 2, // fallback for anything else
		};

		if ( 2 === $reports_view ) {

			trigger_error( $this->language->lang( 'MCP_USER_REPORTS_ERROR_INVALID_FORM_ACTION' ) . '<br /><br />' . sprintf( $this->language->lang( 'RETURN_MCP' ), '<a href="' . $this->functions->get_mcp_module_url( 'mcp_main', [ 'mode' => 'front' ] ) . '">', '</a>' ), E_USER_WARNING );

		}

		// Return URL to redirect the user back to *this* page.
		$return_module_url = $this->functions->get_mcp_module_url( $module_id, [
			'mode'	=> $mode,
		] );

		$report_ids = $this->request->variable( 'report_ids', [ 0 ] );

		if ( confirm_box( true ) ) {

			if ( ! is_array( $report_ids ) || is_array( $report_ids ) && empty( $report_ids ) ) {

				trigger_error( $this->language->lang( 'MCP_USER_REPORTS_ERROR_EMPTY_REPORT_ARRAY' ) . '<br /><br />' . sprintf( $this->language->lang( 'RETURN_PAGE' ), '<a href="' . $return_module_url . '">', '</a>' ), E_USER_WARNING );

			}

			$submit = $this->request->variable( 'submit', '' );

			if ( ! in_array( $submit, [ 'delete', 'close' ] ) ) {

				trigger_error( $this->language->lang( 'MCP_USER_REPORTS_ERROR_INVALID_FORM_ACTION' ) . '<br /><br />' . sprintf( $this->language->lang( 'RETURN_PAGE' ), '<a href="' . $return_module_url . '">', '</a>' ), E_USER_WARNING );

			}

			foreach ( $report_ids as $report ) {

				$result = match ( $submit ) {
					'delete'	=> $this->functions->delete_user_report( $report ),
					'close'		=> $this->functions->close_user_report( $report ),
				};

			}

			if ( 'delete' === $submit ) {

				trigger_error( $this->language->lang( 'MCP_USER_REPORTS_SUCCESS_REPORTS_DELETED', count( $report_ids ) ) . '<br /><br />' . sprintf( $this->language->lang( 'RETURN_PAGE' ), '<a href="' . $return_module_url . '">', '</a>' ), E_USER_WARNING );

			}

			if ( 'close' === $submit ) {

				trigger_error( $this->language->lang( 'MCP_USER_REPORTS_SUCCESS_REPORTS_CLOSED', count( $report_ids ) ) . '<br /><br />' . sprintf( $this->language->lang( 'RETURN_PAGE' ), '<a href="' . $return_module_url . '">', '</a>' ), E_USER_WARNING );

			}

		} else {

			if ( 'POST' === strtoupper( $this->request->server( 'REQUEST_METHOD' ) ) && $this->request->is_set_post( 'submit' ) ) {

				if ( ! check_form_key( 'mcp_user_reports_list_csrf' ) ) {

					trigger_error( $this->language->lang( 'MCP_USER_REPORTS_ERROR_INCORRECT_CSRF_TOKEN' ) . '<br /><br />' . sprintf( $this->language->lang( 'RETURN_PAGE' ), '<a href="' . $return_module_url . '">', '</a>' ), E_USER_WARNING );

				}

				$submit = $this->request->variable( 'submit', [ '' ] );
				$submit = ( $submit[ 0 ] === $this->language->lang( 'CLOSE_REPORTS' ) ) ? 'close' : 'delete';

				confirm_box(
					false,
					$this->language->lang( ( 'close' === $submit ) ? 'MCP_USER_REPORTS_ACTION_CONFIRM_CLOSE' : 'MCP_USER_REPORTS_ACTION_CONFIRM_DELETE', count( $report_ids ) ),
					build_hidden_fields( [
						'reports_view'	=> $reports_view,
						'report_ids'	=> $report_ids,
						'action'		=> $action,
						'mode'			=> $mode,
						'submit'		=> $submit,
					] ),
				);

			}

		}

		add_form_key( 'mcp_user_reports_list_csrf' );

		/**
		 * Set-up the pagination variables to fetch the current set of reports.
		 * 
		 * This variables get used later in a phpBB core function to create the
		 * correct template variables that give us the write page buttons.
		 */
		$count = $this->functions->get_user_report_total( ( 1 === $reports_view ) ? 'closed' : 'open' );
		$limit = 10;
		$offset = $this->request->variable( 'start', 0 );

		$reports = $this->functions->get_user_reports( query: [
			[ 'reported_user_id', '!=', 0 ],
			[ 'report_closed', '=', $reports_view ],
		], order_by: [
			[ 'report_time', 'DESC' ],
		], limit_offset: [
			$offset, $limit
		] );

		$reports_data = [];
		$_user_cache = [];

		if ( ! empty( $reports ) ) {

			$user_ids = [];

			foreach ( $reports as $report ) {

				$reports_data[ $report[ 'report_id' ] ] = [
					'report_id'				=> (int) $report[ 'report_id' ],
					'reported_user_id'		=> (int) $report[ 'reported_user_id' ],
					'reported_user'			=> false, // This gets overwritten later.
					'reported_by_user_id'	=> (int) $report[ 'user_id' ],
					'reported_by'			=> false, // This gets overwritten later.
					'report_text'			=> $report[ 'report_text' ],
					'report_time'			=> $this->functions->get_l10n_local_time( zone: $this->user->data[ 'user_dateformat' ], time: $report[ 'report_time' ] ),
					'report_details_link'	=> $this->functions->get_mcp_module_url( '\danieltj\reportuser\mcp\report_details_module', [
						'mode'	=> 'user_report_details',
						'r'		=> (int) $report[ 'report_id' ],
					] ),
				];

				if ( (int) $report[ 'user_id' ] !== (int) $report[ 'reported_user_id' ] ) {

					$user_ids[] = (int) $report[ 'reported_user_id' ];

				}

				$user_ids[] = (int) $report[ 'user_id' ];

			}

			$users = $this->functions->get_user_data( $user_ids );

			foreach ( $users as $user ) {

				if ( ! isset( $_user_cache[ $user[ 'user_id' ] ] ) ) {

					$_user_cache[ $user[ 'user_id' ] ] = get_username_string( 'full', $user[ 'user_id' ], $user[ 'username' ], $user[ 'user_colour' ] );

				}

			}

			foreach ( $reports_data as $report ) {

				$reports_data[ $report[ 'report_id' ] ][ 'reported_user' ] = ( isset( $_user_cache[ $report[ 'reported_user_id' ] ] ) ) ? $_user_cache[ $report[ 'reported_user_id' ] ] : $this->language->lang( 'MCP_USER_REPORTS_UNKNOWN_USER_NAME', (int) $report[ 'reported_user_id' ] );
				$reports_data[ $report[ 'report_id' ] ][ 'reported_by' ] = ( isset( $_user_cache[ $report[ 'reported_by_user_id' ] ] ) ) ? $_user_cache[ $report[ 'reported_by_user_id' ] ] : $this->language->lang( 'MCP_USER_REPORTS_UNKNOWN_USER_NAME', (int) $report[ 'reported_by_user_id' ] );

			}

		}

		/**
		 * Event to hook into the MCP user report lists.
		 * 
		 * @event danieltj.reportuser.ext_controller_report
		 * @since 1.0.0-b2
		 * 
		 * @var string module_id    The current module identifier.
		 * @var string mode         The current mode of this module.
		 * @var array  reports_data An array containing formatted report data (not the same as the return result of `get_user_reports()`).
		 * @var int    limit        The maximum number of reports per page.
		 * @var int    offset       The current offset of reports to display (pagination).
		 */
		$event = [ 'module_id', 'mode', 'reports_data', 'limit', 'offset' ];
		extract( $this->dispatcher->trigger_event( 'danieltj.reportuser.ext_controller_report', compact( $event ) ) );

		$this->pagination->generate_template_pagination(
			$this->functions->get_mcp_module_url( $module_id ),
			'pagination',
			'start',
			$count,
			$limit,
			$offset
		);

		$this->template->assign_vars( [
			'USER_REPORTS_TITLE'	=> ( 1 === $reports_view ) ? $this->language->lang( 'MCP_USER_REPORTS_CLOSED' ) : $this->language->lang( 'MCP_USER_REPORTS_OPEN' ),
			'USER_REPORTS_EXPLAIN'	=> ( 1 === $reports_view ) ? $this->language->lang( 'MCP_USER_REPORTS_CLOSED_EXPLAIN' ) : $this->language->lang( 'MCP_USER_REPORTS_OPEN_EXPLAIN' ),
			'TOTAL_REPORTS'			=> ( 1 === $reports_view ) ? $this->language->lang( 'MCP_USER_REPORTS_TOTAL_CLOSED_REPORTS', $count ) : $this->language->lang( 'MCP_USER_REPORTS_TOTAL_OPEN_REPORTS', $count ),
			'USER_REPORTS'			=> $reports_data,
			'OPEN_REPORTS'			=> ( 1 === $reports_view ) ? false : true,
			'USER_REPORT_ACTION'	=> $action,
		] );

	}

	/**
	 * Handle the user report details interface.
	 */
	public function details( string $module_id, string $action, string $mode ) {

		$this->language->add_lang( 'mcp' );

		// Return URL to redirect the user back to the open reports.
		$return_module_url = $this->functions->get_mcp_module_url( '\danieltj\reportuser\mcp\reports_open_module', [
			'mode'	=> 'user_reports_open',
		] );

		if ( ! $this->auth->acl_get( 'm_user_report' ) ) {

			trigger_error( $this->language->lang( 'MCP_USER_REPORTS_ERROR_MODERATOR_PERMISSION' ) . '<br /><br />' . sprintf( $this->language->lang( 'RETURN_MCP' ), '<a href="' . $return_module_url . '">', '</a>' ), E_USER_WARNING );

		}

		$report_id = $this->request->variable( 'r', 0 );
		$submit = $this->request->variable( 'submit', [ '' ] );

		// Return URL to redirect the user back to this details page.
		$return_report_details_url = $this->functions->get_mcp_module_url( '\danieltj\reportuser\mcp\report_details_module', [
			'mode'	=> 'user_report_details',
			'r'		=> $report_id,
		] );

		if ( confirm_box( true ) ) {

			if ( 0 === $report_id ) {

				trigger_error( $this->language->lang( 'MCP_USER_REPORTS_ERROR_INVALID_REPORT_ID' ) . '<br /><br />' . sprintf( $this->language->lang( 'RETURN_MCP' ), '<a href="' . $return_module_url . '">', '</a>' ), E_USER_WARNING );

			}

			$submit = $this->request->variable( 'submit', '' );

			if ( ! in_array( $submit, [ 'delete', 'close' ] ) ) {

				trigger_error( $this->language->lang( 'MCP_USER_REPORTS_ERROR_INVALID_FORM_ACTION' ) . '<br /><br />' . sprintf( $this->language->lang( 'RETURN_MCP' ), '<a href="' . $return_report_details_url . '">', '</a>' ), E_USER_WARNING );

			}

			$result = match ( $submit ) {
				'delete'	=> $this->functions->delete_user_report( $report_id ),
				'close'		=> $this->functions->close_user_report( $report_id ),
			};

			if ( 'delete' === $submit ) {

				trigger_error( $this->language->lang( 'MCP_USER_REPORTS_SUCCESS_REPORTS_DELETED', 1 ) . '<br /><br />' . sprintf( $this->language->lang( 'RETURN_MCP' ), '<a href="' . $return_module_url . '">', '</a>' ), E_USER_WARNING );

			}

			if ( 'close' === $submit ) {

				trigger_error( $this->language->lang( 'MCP_USER_REPORTS_SUCCESS_REPORTS_CLOSED', 1 ) . '<br /><br />' . sprintf( $this->language->lang( 'RETURN_MCP' ), '<a href="' . $return_report_details_url . '">', '</a>' ), E_USER_WARNING );

			}

		} else {

			if ( 'POST' === strtoupper( $this->request->server( 'REQUEST_METHOD' ) ) && $this->request->is_set_post( 'submit' ) ) {

				if ( ! check_form_key( 'mcp_user_report_details_csrf' ) ) {

					trigger_error( $this->language->lang( 'MCP_USER_REPORTS_ERROR_INCORRECT_CSRF_TOKEN' ) . '<br /><br />' . sprintf( $this->language->lang( 'RETURN_MCP' ), '<a href="' . $return_report_details_url . '">', '</a>' ), E_USER_WARNING );

				}

				$submit = $this->request->variable( 'submit', [ '' ] );
				$submit = match ( $submit[ 0 ] ) {
					$this->language->lang( 'DELETE_REPORT' )							=> 'delete',
					$this->language->lang( 'CLOSE_REPORT' )								=> 'close',
					$this->language->lang( 'MCP_USER_REPORTS_REQUEST_CHANGES_SUBMIT' )	=> 'changes',
					default																=> 'error',
				};

				if ( 'error' === $submit ) {

					trigger_error( $this->language->lang( 'MCP_USER_REPORTS_ERROR_INVALID_FORM_ACTION' ) . '<br /><br />' . sprintf( $this->language->lang( 'RETURN_MCP' ), '<a href="' . $return_report_details_url . '">', '</a>' ), E_USER_WARNING );

				}

				if ( 'delete' === $submit || 'close' === $submit ) {

					$confirm_box_lang_string = match ( $submit ) {
						'delete'	=> 'MCP_USER_REPORTS_ACTION_CONFIRM_DELETE',
						'close'		=> 'MCP_USER_REPORTS_ACTION_CONFIRM_CLOSE',
					};

					confirm_box(
						false,
						$this->language->lang( $confirm_box_lang_string, 1 ),
						build_hidden_fields( [
							'reports_view'	=> 'user_report_details',
							'report_id'		=> $report_id,
							'action'		=> $action,
							'mode'			=> $mode,
							'submit'		=> $submit,
						] ),
					);

				} elseif ( 'changes' === $submit ) {

					$notification_text = $this->request->variable( 'notification_text', '' );

					$result = $this->functions->send_request_changes_notification( $report_id, $notification_text );

					if ( false === $result ) {

						trigger_error( $this->language->lang( 'MCP_USER_REPORTS_ERROR_REQUEST_CHANGES' ) . '<br /><br />' . sprintf( $this->language->lang( 'RETURN_MCP' ), '<a href="' . $return_report_details_url . '">', '</a>' ), E_USER_WARNING );

					} else {

						trigger_error( $this->language->lang( 'MCP_USER_REPORTS_SUCCESS_CHANGES_REQUESTED' ) . '<br /><br />' . sprintf( $this->language->lang( 'RETURN_MCP' ), '<a href="' . $return_report_details_url . '">', '</a>' ), E_USER_WARNING );

					}

				}

			}

		}

		add_form_key( 'mcp_user_report_details_csrf' );

		$reports = $this->functions->get_user_reports( query: [
			[ 'report_id', '=', $report_id ],
		] );

		$_user_cache = [];
		$user_ids = [];

		if ( empty( $reports ) ) {

			trigger_error( $this->language->lang( 'MCP_USER_REPORTS_ERROR_REPORT_NOT_FOUND' ) . '<br /><br />' . sprintf( $this->language->lang( 'RETURN_MCP' ), '<a href="' . $return_module_url . '">', '</a>' ), E_USER_WARNING );

		}

		if ( (int) $reports[ 0 ][ 'user_id' ] !== (int) $reports[ 0 ][ 'reported_user_id' ] ) {

			$user_ids[] = (int) $reports[ 0 ][ 'reported_user_id' ];

		}

		$user_ids[] = (int) $reports[ 0 ][ 'user_id' ];

		$users = $this->functions->get_user_data( $user_ids );

		// Set up some profile defaults for the reported user.
		$reported_user_avatar = false;
		$reported_user_cpf = false;
		$reported_user_signature = false;

		foreach ( $users as $user ) {

			if ( ! isset( $_user_cache[ $user[ 'user_id' ] ] ) ) {

				$_user_cache[ $user[ 'user_id' ] ] = get_username_string( 'full', $user[ 'user_id' ], $user[ 'username' ], $user[ 'user_colour' ] );

				if ( (int) $user[ 'user_id' ] === (int) $reports[ 0 ][ 'reported_user_id' ] ) {

					$reported_user_avatar = phpbb_get_avatar( [
						'avatar'			=> $user[ 'user_avatar' ],
						'avatar_type'		=> $user[ 'user_avatar_type' ],
						'avatar_width'		=> $user[ 'user_avatar_width' ],
						'avatar_height'		=> $user[ 'user_avatar_height' ],
					], 'USER_AVATAR' );

					$reported_user_signature = generate_text_for_display( $user[ 'user_sig' ], $user[ 'user_sig_bbcode_uid' ], $user[ 'user_sig_bbcode_bitfield' ], false, false );

					$reported_user_cpf = $this->functions->get_user_cpf_data( $user[ 'user_id' ] );

				}

			}

		}

		$report_data = [
			'report_id'					=> (int) $reports[ 0 ][ 'report_id' ],
			'reported_user_id'			=> (int) $reports[ 0 ][ 'reported_user_id' ],
			'reported_user'				=> ( isset( $_user_cache[ $reports[ 0 ][ 'reported_user_id' ] ] ) ) ? $_user_cache[ $reports[ 0 ][ 'reported_user_id' ] ] : $this->language->lang( 'MCP_USER_REPORTS_UNKNOWN_USER_NAME', (int) $reports[ 0 ][ 'reported_user_id' ] ),
			'reported_user_avatar'		=> $reported_user_avatar,
			'reported_user_cpfs'		=> ( empty( $reported_user_cpf ) ) ? false : $reported_user_cpf,
			'reported_user_signature'	=> $reported_user_signature,
			'reported_by_user_id'		=> (int) $reports[ 0 ][ 'user_id' ],
			'reported_by'				=> ( isset( $_user_cache[ $reports[ 0 ][ 'user_id' ] ] ) ) ? $_user_cache[ $reports[ 0 ][ 'user_id' ] ] : $this->language->lang( 'MCP_USER_REPORTS_UNKNOWN_USER_NAME', (int) $reports[ 0 ][ 'user_id' ] ),
			'report_text'				=> $reports[ 0 ][ 'report_text' ],
			'report_time'				=> $this->functions->get_l10n_local_time( zone: $this->user->data[ 'user_dateformat' ], time: $reports[ 0 ][ 'report_time' ] ),
			'report_details_link'		=> $this->functions->get_mcp_module_url( '\danieltj\reportuser\mcp\report_details_module', [
				'mode'					=> 'user_report_details',
				'report_id'				=> (int) $reports[ 0 ][ 'report_id' ],
			] ),
		];

		/**
		 * Event to hook into the MCP user report details.
		 * 
		 * @event danieltj.reportuser.ext_controller_details
		 * @since 1.0.0-b2
		 * 
		 * @var string module_id   The current module identifier.
		 * @var string mode        The current mode of this module.
		 * @var array  report_data An array containing formatted report data of a single user report.
		 */
		$event = [ 'module_id', 'mode', 'report_data' ];
		extract( $this->dispatcher->trigger_event( 'danieltj.reportuser.ext_controller_details', compact( $event ) ) );

		$this->template->assign_vars( [
			'S_REPORT_USER_CSS'						=> true,
			'USER_REPORT'							=> $report_data,
			'MCP_USER_REPORTS_REPORT_INFO_TITLE'	=> $this->language->lang( 'MCP_USER_REPORTS_REPORT_INFO_TITLE', $report_data[ 'report_id' ] ),
			'MCP_USER_REPORTS_REPORT_BY_USER'		=> $this->language->lang( 'MCP_USER_REPORTS_REPORT_BY_USER', $report_data[ 'reported_by' ] ),
			'MAX_NOTIFY_TEXT_LENGTH'				=> $this->functions->get_rcn_character_length(),
			'S_REPORT_OPEN'							=> ( 1 === (int) $reports[ 0 ][ 'report_closed' ] ) ? false : true,
		] );

	}

}
