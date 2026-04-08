<?php

/**
 * @package Report User
 * @copyright (c) 2026 Daniel James
 * @license https://opensource.org/license/gpl-2-0
 */

namespace danieltj\reportuser\event;

use phpbb\language\language;
use phpbb\template\template;
use danieltj\reportuser\includes\functions;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface {

	/**
	 * @var language
	 */
	protected $language;

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
	public function __construct( language $language, template $template, functions $functions ) {

		$this->language = $language;
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
			'common', 'mcp', 'permissions'
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

		$this->template->assign_vars( [
			'REPORT_USER' => ( $this->functions->can_report_user( $event[ 'member' ][ 'user_id' ] ) ) ? $this->functions->get_report_user_url( $event[ 'member' ][ 'user_id' ] ) : false,
		] );

	}

}
