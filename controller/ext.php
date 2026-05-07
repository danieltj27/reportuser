<?php

/**
 * @package Report User
 * @copyright (c) 2026 Daniel James
 * @license https://opensource.org/license/gpl-2-0
 */

namespace danieltj\reportuser\controller;

use phpbb\controller\helper as controller;
use phpbb\event\dispatcher_interface as dispatcher;
use phpbb\language\language;
use phpbb\notification\manager as notifications;
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
	public function __construct( controller $controller, dispatcher $dispatcher, language $language, notifications $notifications, request $request, router $router, template $template, user $user, functions $functions ) {

		$this->controller = $controller;
		$this->dispatcher = $dispatcher;
		$this->language = $language;
		$this->notifications = $notifications;
		$this->request = $request;
		$this->router = $router;
		$this->template = $template;
		$this->user = $user;
		$this->functions = $functions;

	}

	/**
	 * The new user report form.
	 */
	public function report( int $user_id ) {

		// Return URLs that the user may be redirected to.
		$return_user_profile_url = $this->functions->get_user_profile_url( $user_id );

		if ( ! $this->functions->can_report_user( $user_id ) ) {

			trigger_error( $this->language->lang( 'REPORT_USER_ERROR_INVALID_USER_PERMISSION' ) . '<br /><br />' . sprintf( $this->language->lang( 'RETURN_PAGE' ), '<a href="' . $return_user_profile_url . '">', '</a>' ), E_USER_WARNING );

		}

		if ( 'GET' !== strtoupper( $this->request->server( 'REQUEST_METHOD' ) ) ) {

			trigger_error( $this->language->lang( 'REPORT_USER_ERROR_INVALID_HTTP_REQUEST' ) . '<br /><br />' . sprintf( $this->language->lang( 'RETURN_INDEX' ), '<a href="' . $return_user_profile_url . '">', '</a>' ), E_USER_WARNING );

		}

		$user_data = $this->functions->get_user_data( [ $user_id ] );

		if ( empty( $user_data ) ) {

			trigger_error( $this->language->lang( 'REPORT_USER_ERROR_INVALID_USER_SELECTED' ) . '<br /><br />' . sprintf( $this->language->lang( 'RETURN_INDEX' ), '<a href="' . generate_board_url() . '">', '</a>' ), E_USER_WARNING );

		}

		$reported_user = array_first( $user_data );

		add_form_key( 'report_user_form_csrf' );

		// Add phpBB core MCP language strings.
		$this->language->add_lang( 'mcp' );

		$this->template->assign_vars( [
			'S_REPORT_USER_CSS'	=> true,
			'REPORT_USER'		=> $this->router->route( 'report_user_mcp_submit_report', [ 'user_id' => $user_id ] ),
			'REPORT_THIS_USER'	=> $this->language->lang( 'REPORT_USER_THIS_USER', get_username_string( 'full', $reported_user[ 'user_id' ], $reported_user[ 'username' ], $reported_user[ 'user_colour' ] ) ),
		] );

		/**
		 * Event to hook into the new report form.
		 * 
		 * @event danieltj.reportuser.ext_controller_report
		 * @since 1.0.0-b2
		 * 
		 * @var int   $user_id   The user ID that is being reported.
		 * @var array $user_data An array containing data of the user being reported.
		 */
		$event = [ 'user_id', 'user_data' ];
		extract( $this->dispatcher->trigger_event( 'danieltj.reportuser.ext_controller_report', compact( $event ) ) );

		return $this->controller->render( '@danieltj_reportuser/report_user_body.html', $this->language->lang( 'REPORT_USER' ) );

	}

	/**
	 * Submit a new user report.
	 */
	public function submit( int $user_id ) {

		// Return URLs that the user may be redirected to.
		$return_user_profile_url = $this->functions->get_user_profile_url( $user_id );
		$return_report_form_url = $this->router->route( 'report_user_mcp_create_report', [
			'user_id' => $user_id,
		] );

		if ( ! $this->functions->can_report_user( $user_id ) ) {

			trigger_error( $this->language->lang( 'REPORT_USER_ERROR_INVALID_USER_PERMISSION' ) . '<br /><br />' . sprintf( $this->language->lang( 'RETURN_PAGE' ), '<a href="' . $return_user_profile_url . '">', '</a>' ), E_USER_WARNING );

		}

		if ( 'POST' !== strtoupper( $this->request->server( 'REQUEST_METHOD' ) ) ) {

			trigger_error( $this->language->lang( 'REPORT_USER_ERROR_INVALID_HTTP_REQUEST' ) . '<br /><br />' . sprintf( $this->language->lang( 'RETURN_INDEX' ), '<a href="' . $return_report_form_url . '">', '</a>' ), E_USER_WARNING );

		}

		if ( ! check_form_key( 'report_user_form_csrf' ) ) {

			trigger_error( $this->language->lang( 'REPORT_USER_ERROR_INVALID_CSRF_TOKEN' ) . '<br /><br />' . sprintf( $this->language->lang( 'RETURN_PAGE' ), '<a href="' . $return_report_form_url . '">', '</a>' ), E_USER_WARNING );

		}

		// Check the type of submission.
		$submit = $this->request->variable( 'submit', [ '' ] );
		$submit = array_first( $submit );

		// Form cancelled, redirect to user profile.
		if ( $submit === $this->language->lang( 'CANCEL' ) ) {

			header( 'Location: ' . htmlspecialchars_decode( $return_user_profile_url, ENT_NOQUOTES ) );

			die();

		}

		// Fetch submitted form components.
		$report_notify = $this->request->variable( 'report_notify', 0 );
		$report_reason = $this->request->variable( 'report_reason', '' );

		if ( 1 > strlen( $report_reason ) || 250 < strlen( $report_reason ) ) {

			trigger_error( $this->language->lang( 'REPORT_USER_ERROR_INVALID_REPORT_REASON' ) . '<br /><br />' . sprintf( $this->language->lang( 'RETURN_PAGE' ), '<a href="' . $return_report_form_url . '">', '</a>' ), E_USER_WARNING );

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

			trigger_error( $this->language->lang( 'REPORT_USER_ERROR_UNKNOWN_ERROR_MESSAGE' ) . '<br /><br />' . sprintf( $this->language->lang( 'RETURN_PAGE' ), '<a href="' . $return_report_form_url . '">', '</a>' ), E_USER_WARNING );

		}

		$this->notifications->add_notifications( 'danieltj.reportuser.notification.type.new_report', [
			'report_id'			=> (int) $report_id,
			'reporter_user_id'	=> (int) $this->user->data[ 'user_id' ],
			'reported_user_id'	=> (int) $user_id,
			'report_text'		=> $report_reason,
		] );

		trigger_error( $this->language->lang( 'REPORT_USER_SUCCESS_MESSAGE' ) . '<br /><br />' . sprintf( $this->language->lang( 'RETURN_PAGE' ), '<a href="' . $return_user_profile_url . '">', '</a>' ), E_USER_WARNING );

	}

}
