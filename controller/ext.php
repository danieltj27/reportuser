<?php

/**
 * @package Report User
 * @copyright (c) 2026 Daniel James
 * @license https://opensource.org/license/gpl-2-0
 */

namespace danieltj\reportuser\controller;

use phpbb\controller\helper as controller;
use phpbb\language\language;
use phpbb\request\request;
use phpbb\routing\helper as router;
use phpbb\template\template;
use phpbb\user;
use danieltj\reportuser\includes\functions;

final class ext {

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
	public function __construct( controller $controller, language $language, request $request, router $router, template $template, user $user, functions $functions ) {

		$this->controller = $controller;
		$this->language = $language;
		$this->request = $request;
		$this->router = $router;
		$this->template = $template;
		$this->user = $user;
		$this->functions = $functions;

	}

	/**
	 * Create a new user report.
	 */
	public function report( int $user_id ) {

		/**
		 * @todo FORCE GET REQUEST FOR PAGE
		 */

		// The profile URL of the user being reported.
		$user_return_url = append_sid( '/memberlist.php', [
			'mode'	=> 'viewprofile',
			'u'		=> $user_id,
		] );

		if ( ! $this->functions->can_report_user( $user_id ) ) {

			trigger_error( $this->language->lang( 'REPORT_USER_ERROR_PERMISSION_DENIED' ) . '<br /><br />' . sprintf( $this->language->lang( 'RETURN_PAGE' ), '<a href="' . $user_return_url . '">', '</a>' ), E_USER_WARNING );

		}

		$reported_user = $this->functions->get_user_data( $user_id );

		if ( false === $reported_user ) {

			trigger_error( $this->language->lang( 'REPORT_USER_ERROR_INVALID_USER' ), E_USER_WARNING );

		}

		add_form_key( 'report_user_form_csrf' );

		$this->template->assign_vars( [
			'REPORT_USER'		=> $this->router->route( 'report_user_mcp_submit_report', [ 'user_id' => $user_id ] ),
			'REPORT_THIS_USER'	=> $this->language->lang( 'REPORT_USER_THIS_USER', get_username_string( 'full', $reported_user[ 'user_id' ], $reported_user[ 'username' ], $reported_user[ 'user_colour' ] ) ),
		] );

		return $this->controller->render( '@danieltj_reportuser/report_user_body.html', $this->language->lang( 'REPORT_USER' ) );

	}

	/**
	 * Submit a new user report.
	 */
	public function submit( int $user_id ) {

		/**
		 * @todo FORCE POST REQUEST FOR PAGE
		 */

		// The profile URL of the user being reported.
		$user_return_url = append_sid( '/memberlist.php', [
			'mode'	=> 'viewprofile',
			'u'		=> $user_id,
		] );

		if ( ! $this->functions->can_report_user( $user_id ) ) {

			trigger_error( $this->language->lang( 'REPORT_USER_ERROR_PERMISSION_DENIED' ) . '<br /><br />' . sprintf( $this->language->lang( 'RETURN_INDEX' ), '<a href="' . $user_return_url . '">', '</a>' ), E_USER_WARNING );

		}

		if ( ! check_form_key( 'report_user_form_csrf' ) ) {

			trigger_error( $this->language->lang( 'REPORT_USER_ERROR_INVALID_CSRF' ) . '<br /><br />' . sprintf( $this->language->lang( 'RETURN_PAGE' ), '<a href="' . $this->router->route( 'report_user_mcp_create_report', [ 'user_id' => $user_id ] ) . '">', '</a>' ), E_USER_WARNING );

		}

		// Fetch submitted form components.
		$report_notify = $this->request->variable( 'report_notify', 0 );
		$report_reason = $this->request->variable( 'report_reason', '' );

		if ( 1 > strlen( $report_reason ) || 250 < strlen( $report_reason ) ) {

			trigger_error( $this->language->lang( 'REPORT_USER_ERROR_INVALID_REASON' ) . '<br /><br />' . sprintf( $this->language->lang( 'RETURN_PAGE' ), '<a href="' . $this->router->route( 'report_user_mcp_create_report', [ 'user_id' => $user_id ] ) . '">', '</a>' ), E_USER_WARNING );

		}

		$report_id = $this->functions->create_user_report( [
			'reason_id'							=> 0,
			'post_id'							=> 0,
			'user_id'							=> $this->user->data[ 'user_id' ], // the reporter
			'pm_id'								=> 0,
			'reported_user_id'					=> $user_id,
			'user_notify'						=> $report_notify,
			'report_closed'						=> 0,
			'report_time'						=> time(),
			'report_text'						=> $report_reason,
			'reported_post_enable_bbcode'		=> 0,
			'reported_post_enable_smilies'		=> 0,
			'reported_post_enable_magic_url'	=> 0,
			'reported_post_text'				=> 0,
			'reported_post_uid'					=> 0,
			'reported_post_bitfield'			=> 0,
		] );

		if ( false === $report_id ) {

			trigger_error( $this->language->lang( 'REPORT_USER_ERROR_UNKNOWN_ERROR' ) . '<br /><br />' . sprintf( $this->language->lang( 'RETURN_PAGE' ), '<a href="' . $this->router->route( 'report_user_mcp_create_report', [ 'user_id' => $user_id ] ) . '">', '</a>' ), E_USER_WARNING );

		}

		/**
		 * @todo send notifications to moderators
		 */

		trigger_error( $this->language->lang( 'REPORT_USER_ERROR_REPORT_SUCCESS' ) . '<br /><br />' . sprintf( $this->language->lang( 'RETURN_PAGE' ), '<a href="' . $user_return_url . '">', '</a>' ), E_USER_WARNING );

	}

}
