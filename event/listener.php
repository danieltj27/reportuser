<?php

/**
 * @package Report User
 * @copyright (c) 2026 Daniel James
 * @license https://opensource.org/license/gpl-2-0
 */

namespace danieltj\reportuser\event;

use phpbb\language\language;
use phpbb\routing\helper as router;
use phpbb\template\template;
use danieltj\reportuser\includes\functions;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface {

	/**
	 * @var language
	 */
	protected $language;

	/**
	 * @var router
	 */
	protected $router;

	/**
	 * @var template
	 */
	protected $template;

	/**
	 * @var functions
	 */
	protected $functions;

	/**
	 * Constructor
	 */
	public function __construct( language $language, router $router, template $template, functions $functions ) {

		$this->language = $language;
		$this->router = $router;
		$this->template = $template;
		$this->functions = $functions;

	}

	/**
	 * Register Events
	 */
	static public function getSubscribedEvents() {

		return [
			'core.user_setup_after'			=> 'add_languages',
			'core.permissions'				=> 'add_permissions',

			'core.memberlist_view_profile'	=> 'add_memberlist_template_vars',
		];

	}

	/**
	 * phpbb/user:setup
	 */
	public function add_languages() {

		$this->language->add_lang( [
			'common',
			'mcp',
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

		$this->template->assign_vars( [
			'REPORT_USER' => ( $this->functions->can_report_user( $event[ 'member' ][ 'user_id' ] ) ) ? $report_user_url : false,
		] );

	}

}
