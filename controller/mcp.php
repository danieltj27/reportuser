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
	 * @todo
	 */
	public function reports( string $action ) {

		if ( 'GET' !== strtoupper( $this->request->server( 'REQUEST_METHOD' ) ) ) {

			trigger_error( $this->language->lang( 'MCP_REPORT_USER_ERROR_INVALID_HTTP' ), E_USER_WARNING );

		}

		if ( ! $this->auth->acl_get( 'm_user_report' ) ) {

			trigger_error( $this->language->lang( 'MCP_REPORT_USER_ERROR_ACCESS_DENIED' ), E_USER_WARNING );

		}

		add_form_key( 'report_user_form_csrf' );

		$reports = $this->functions->get_user_reports( query: [
			[ 'reported_user_id', '!=', 0 ],
			[ 'report_closed', '=', 0 ],
		], order_by: [
			[ 'report_time', 'ASC' ],
		], limit_offset: [
			0, 10
		] );

		/**
		 * @todo loop through every user and cache it so all users can be queried at the same time.
		 */

		$reports_data = [];
		$_user_cache = [];

		if ( ! empty( $reports ) ) {

			foreach ( $reports as $report ) {

				$reported_user = $this->functions->get_user_data( $report[ 'reported_user_id' ] );
				$reported_by = $this->functions->get_user_data( $report[ 'user_id' ] );

				$reported_user_html = ( false !== $reported_user ) ? get_username_string( 'full', $reported_user[ 'user_id' ], $reported_user[ 'username' ], $reported_user[ 'user_colour' ] ) : '_ERROR_';
				$reported_by_html = ( false !== $reported_by ) ? get_username_string( 'full', $reported_by[ 'user_id' ], $reported_by[ 'username' ], $reported_by[ 'user_colour' ] ) : '_ERROR_';

				$reports_data[] = [
					'reported_user'		=> $reported_user_html,
					'reported_by'		=> $reported_by_html,
					'report_reason'		=> $report[ 'report_text' ],
					'report_time'		=> $this->functions->get_l10n_local_time( $this->user->data[ 'user_dateformat' ] ),
				];

			}

		}

		$this->template->assign_vars( [
			'USER_REPORTS' => $reports_data,
		] );

		var_dump( $reports_data ); die();

		//die( 'reports' );

	}

	/**
	 * @todo
	 */
	public function reports_closed( string $action ) {

		if ( 'GET' !== strtoupper( $this->request->server( 'REQUEST_METHOD' ) ) ) {

			trigger_error( $this->language->lang( 'MCP_REPORT_USER_ERROR_INVALID_HTTP' ), E_USER_WARNING );

		}

		if ( ! $this->auth->acl_get( 'm_user_report' ) ) {

			trigger_error( $this->language->lang( 'MCP_REPORT_USER_ERROR_ACCESS_DENIED' ), E_USER_WARNING );

		}

		add_form_key( 'report_user_form_csrf' );

		//die( 'reports_closed' );

	}

	/**
	 * @todo
	 */
	public function close_report( int $report_id ) {

		if ( 'POST' !== strtoupper( $this->request->server( 'REQUEST_METHOD' ) ) ) {

			trigger_error( $this->language->lang( 'MCP_REPORT_USER_ERROR_INVALID_HTTP' ), E_USER_WARNING );

		}

		if ( ! $this->auth->acl_get( 'm_user_report' ) ) {

			trigger_error( $this->language->lang( 'MCP_REPORT_USER_ERROR_ACCESS_DENIED' ), E_USER_WARNING );

		}

		die( 'close_report' );

	}

	/**
	 * @todo
	 */
	public function delete_report( int $report_id ) {

		if ( 'POST' !== strtoupper( $this->request->server( 'REQUEST_METHOD' ) ) ) {

			trigger_error( $this->language->lang( 'MCP_REPORT_USER_ERROR_INVALID_HTTP' ), E_USER_WARNING );

		}

		if ( ! $this->auth->acl_get( 'm_user_report' ) ) {

			trigger_error( $this->language->lang( 'MCP_REPORT_USER_ERROR_ACCESS_DENIED' ), E_USER_WARNING );

		}

		die( 'delete_report' );

	}

}
