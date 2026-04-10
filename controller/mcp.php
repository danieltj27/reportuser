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

		//return $this->controller->render( '@danieltj_reportuser/mcp_user_reports.html', $this->language->lang( 'REPORT_USER' ) );

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

		//return $this->controller->render( '@danieltj_reportuser/mcp_user_reports_closed.html', $this->language->lang( 'REPORT_USER' ) );

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
