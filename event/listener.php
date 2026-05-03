<?php

/**
 * @package Report User
 * @copyright (c) 2026 Daniel James
 * @license https://opensource.org/license/gpl-2-0
 */

namespace danieltj\reportuser\event;

use phpbb\auth\auth;
use phpbb\language\language;
use phpbb\request\request;
use phpbb\routing\helper as router;
use phpbb\template\template;
use phpbb\user;
use danieltj\reportuser\includes\functions;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface {

	/**
	 * @var auth
	 */
	protected $auth;

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
	public function __construct( auth $auth, language $language, request $request, router $router, template $template, user $user, functions $functions ) {

		$this->auth = $auth;
		$this->language = $language;
		$this->request = $request;
		$this->router = $router;
		$this->template = $template;
		$this->user = $user;
		$this->functions = $functions;

	}

	/**
	 * Register Events
	 */
	static public function getSubscribedEvents() {

		return [
			'core.user_setup_after'								=> 'add_languages',
			'core.permissions'									=> 'add_permissions',
			'core.memberlist_view_profile'						=> 'add_memberlist_template_vars',
			'core.modify_mcp_modules_display_option'			=> 'update_mcp_module_display',
		];

	}

	/**
	 * phpbb/user:setup
	 */
	public function add_languages() {

		$this->language->add_lang( [
			'common',
			'mcp',
			'notifications',
			'permissions',
		], 'danieltj/reportuser' );

	}

	/**
	 * phpbb/permissions:__construct
	 */
	public function add_permissions( $event ) {

		$event->update_subarray( 'permissions', 'm_user_report', [
			'lang'	=> 'ACL_M_USER_REPORT',
			'cat'	=> 'misc'
		] );

	}

	/**
	 * memberlist
	 */
	public function add_memberlist_template_vars( $event ) {

		$report_user_url = $this->router->route( 'report_user_mcp_create_report', [
			'user_id' => $event[ 'member' ][ 'user_id' ],
		] );

		$can_report_user = $this->functions->can_report_user( $event[ 'member' ][ 'user_id' ] );

		$this->template->assign_vars( [
			'S_REPORT_USER_CSS'	=> ( $can_report_user ) ? true : false,
			'REPORT_USER'		=> ( $can_report_user ) ? $report_user_url : false,
		] );

	}

	/**
	 * mcp
	 */
	public function update_mcp_module_display( $event ) {

		if ( 'user_reports_open' === $event[ 'mode' ] || 'user_reports_closed' === $event[ 'mode' ] || 'user_report_details' === $event[ 'mode' ] ) {

			$event[ 'module' ]->set_display( 'reports', 'report_details', false );
			$event[ 'module' ]->set_display( 'pm_reports', 'pm_report_details', false );

		}

		if ( 'user_report_details' !== $event[ 'mode' ] ) {

			$event[ 'module' ]->set_display( '\danieltj\reportuser\mcp\report_details_module', 'user_report_details', false );

		}

		if ( 'user_report_details' === $event[ 'mode' ] ) {

			$report_id = $this->request->variable( 'r', 0 );

			$event[ 'module' ]->adjust_url( 'r=' . $report_id );

		}

		// Check the open reports module is loaded first.
		if ( $event[ 'module' ]->loaded( '\danieltj\reportuser\mcp\reports_open_module' ) ) {

			$reports = $this->functions->get_user_reports( query: [
				[ 'reported_user_id', '!=', 0 ],
				[ 'report_closed', '=', 0 ],
			], order_by: [
				[ 'report_time', 'DESC' ],
			], limit_offset: [
				0, 5
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

					$reports_data[ $report[ 'report_id' ] ][ 'reported_user' ] = ( isset( $_user_cache[ $report[ 'reported_user_id' ] ] ) ) ? $_user_cache[ $report[ 'reported_user_id' ] ] : false;
					$reports_data[ $report[ 'report_id' ] ][ 'reported_by' ] = ( isset( $_user_cache[ $report[ 'reported_by_user_id' ] ] ) ) ? $_user_cache[ $report[ 'reported_by_user_id' ] ] : false;

				}

			}

			$this->template->assign_vars( [
				'S_USER_REPORTS'					=> ( $this->auth->acl_get( 'm_user_report' ) ) ? true : false,
				'MCP_USER_REPORTS_LATEST_OVERVIEW'	=> $this->language->lang( 'MCP_USER_REPORTS_LATEST_OVERVIEW', 0 ),
				'USER_REPORTS'						=> $reports_data,
			] );

		}

	}

}
