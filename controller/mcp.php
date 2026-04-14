<?php

/**
 * @package Report User
 * @copyright (c) 2026 Daniel James
 * @license https://opensource.org/license/gpl-2-0
 */

namespace danieltj\reportuser\controller;

use phpbb\auth\auth;
use phpbb\controller\helper as controller;
use phpbb\language\language;
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
	 * @var language
	 */
	protected $language;

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
	public function __construct( auth $auth, controller $controller, language $language, request $request, router $router, template $template, user $user, functions $functions ) {

		$this->auth = $auth;
		$this->controller = $controller;
		$this->language = $language;
		$this->request = $request;
		$this->router = $router;
		$this->template = $template;
		$this->user = $user;
		$this->functions = $functions;

	}

	/**
	 * Handle the user reports interface.
	 * 
	 * @todo include return links in trigger_error calls
	 */
	public function reports( string $action, string $mode ) {

		if ( ! $this->auth->acl_get( 'm_user_report' ) ) {

			trigger_error( $this->language->lang( 'MCP_USER_REPORTS_ERROR_MODERATOR_PERMISSION' ), E_USER_WARNING );

		}

		// Check which report type to look at (open or closed).
		$reports_view = match ( $mode ) {
			'user_reports_closed'	=> 1, // closed reports
			'user_reports_open'		=> 0, // open reports
			default					=> 2, // fallback for anything else
		};

		if ( 2 === $reports_view ) {

			trigger_error( $this->language->lang( 'MCP_USER_REPORTS_ERROR_INVALID_FORM_ACTION' ), E_USER_WARNING );

		}

		if ( confirm_box( true ) ) {

			$view = $this->request->variable( 'reports_view', 0 );
			$report_ids = $this->request->variable( 'report_ids', [ 0 ] );
			$submit = $this->request->variable( 'submit', '' );

			if ( ! is_array( $report_ids ) || is_array( $report_ids ) && empty( $report_ids ) ) {

				trigger_error( $this->language->lang( 'MCP_USER_REPORTS_ERROR_EMPTY_REPORT_ARRAY' ), E_USER_WARNING );

			}

			if ( ! in_array( $submit, [ 'close', 'delete' ] ) ) {

				trigger_error( $this->language->lang( 'MCP_USER_REPORTS_ERROR_INVALID_FORM_ACTION' ), E_USER_WARNING );

			}

			foreach ( $report_ids as $report ) {

				$result = match ( $submit ) {
					'delete'	=> $this->functions->delete_user_report( $report ),
					'close'		=> $this->functions->close_user_report( $report ),
				};

			}

			if ( 'delete' === $submit ) {

				trigger_error( $this->language->lang( 'MCP_USER_REPORTS_SUCCESS_REPORTS_DELETED', count( $report_ids ) ), E_USER_WARNING );

			}

			if ( 'close' === $submit ) {

				trigger_error( $this->language->lang( 'MCP_USER_REPORTS_SUCCESS_REPORTS_CLOSED', count( $report_ids ) ), E_USER_WARNING );

			}

		} else {

			if ( 'POST' === strtoupper( $this->request->server( 'REQUEST_METHOD' ) ) && $this->request->is_set_post( 'submit' ) ) {

				if ( ! check_form_key( 'report_user_mcp_csrf' ) ) {

					trigger_error( $this->language->lang( 'MCP_USER_REPORTS_ERROR_INCORRECT_CSRF_TOKEN' ), E_USER_WARNING );

				}

				$report_ids = $this->request->variable( 'report_item', [ 0 ] );

				$submit = $this->request->variable( 'submit', [ '' ] );
				$submit = $submit[ 0 ];
				$submit = ( $submit === $this->language->lang( 'CLOSE_REPORTS' ) ) ? 'close' : 'delete';

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

		add_form_key( 'report_user_mcp_csrf' );

		// Set-up pagination settings for this view.
		$count = $this->functions->get_user_report_total( ( 1 === $reports_view ) ? 'closed' : 'open' );
		$limit = 10;
		$page = $this->request->variable( 'page', 1 );
		$prev_page = $page - 1;
		$next_page = $page + 1;
		$max_page = ( $count > $limit ) ? (int) ceil( $count / $limit ) : 1;
		$offset = ( 1 < $page ) ? ( $limit * page ) - $limit : 0;

		$reports = $this->functions->get_user_reports( query: [
			[ 'reported_user_id', '!=', 0 ],
			[ 'report_closed', '=', $reports_view ],
		], order_by: [
			[ 'report_time', 'ASC' ],
		], limit_offset: [
			0, 10
		] );

		$reports_data = [];
		$_user_cache = [];

		if ( ! empty( $reports ) ) {

			$user_ids = [];

			foreach ( $reports as $report ) {

				$reports_data[ $report[ 'report_id' ] ] = [
					'report_id'				=> (int) $report[ 'report_id' ],
					'reported_user_id'		=> (int) $report[ 'reported_user_id' ],
					//'reported_user'		=> false,
					'reported_by_user_id'	=> (int) $report[ 'user_id' ],
					//'reported_by'			=> false,
					'report_text'			=> $report[ 'report_text' ],
					'report_time'			=> $this->functions->get_l10n_local_time( zone: $this->user->data[ 'user_dateformat' ], time: $report[ 'report_time' ] ),
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

				$reports_data[ $report[ 'report_id' ] ][ 'reported_user' ] = ( isset( $_user_cache[ $report[ 'reported_user_id' ] ] ) ) ? $_user_cache[ $report[ 'reported_user_id' ] ] : false;
				$reports_data[ $report[ 'report_id' ] ][ 'reported_by' ] = ( isset( $_user_cache[ $report[ 'reported_by_user_id' ] ] ) ) ? $_user_cache[ $report[ 'reported_by_user_id' ] ] : false;

			}

		}

		$this->template->assign_vars( [
			'USER_REPORTS_TITLE'	=> ( 1 === $reports_view ) ? $this->language->lang( 'MCP_USER_REPORTS_CLOSED' ) : $this->language->lang( 'MCP_USER_REPORTS_OPEN' ),
			'USER_REPORTS_EXPLAIN'	=> ( 1 === $reports_view ) ? $this->language->lang( 'MCP_USER_REPORTS_CLOSED_EXPLAIN' ) : $this->language->lang( 'MCP_USER_REPORTS_OPEN_EXPLAIN' ),
			'TOTAL_REPORTS'			=> $this->language->lang( 'MCP_USER_REPORTS_TYPE_TOTAL', $count ),
			'PAGE_NUMBER'			=> $this->language->lang( 'MCP_USER_REPORTS_PAGE', $page, $max_page ),
			'USER_REPORTS'			=> $reports_data,
			'OPEN_REPORTS'			=> ( 1 === $reports_view ) ? false : true,
			'USER_REPORT_ACTION'	=> $action,
		] );

	}

}
