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
			'core.user_setup_after'	=> 'add_languages',
			'core.permissions'		=> 'add_permissions',
		];

	}

	/**
	 * phpbb/user
	 */
	public function add_languages() {

		$this->language->add_lang( [
			'common', 'mcp', 'permissions'
		], 'danieltj/reportuser' );

	}

	/**
	 * phpbb/permissions
	 */
	public function add_permissions( $event ) {

		$event->update_subarray( 'permissions', 'm_user_report', [
			'lang'	=> 'ACL_M_USER_REPORT',
			'cat'	=> 'misc'
		] );

	}

}
