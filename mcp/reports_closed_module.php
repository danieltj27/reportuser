<?php

/**
 * @package Report User
 * @copyright (c) 2026 Daniel James
 * @license https://opensource.org/license/gpl-2-0
 */

namespace danieltj\reportuser\mcp;

class reports_closed_module {

	/**
	 * @var $action;
	 */
	public $u_action;

	/**
	 * @var $tpl_name;
	 */
	public $tpl_name;

	/**
	 * @var $page_title;
	 */
	public $page_title;

	/**
	 * UCP module
	 */
	public function main( $id, $mode ) {

		global $phpbb_container;

		$language = $phpbb_container->get( 'language' );

		$this->tpl_name = 'mcp_report_user';
		$this->page_title = $language->lang( 'MCP_USER_REPORTS_CLOSED' );

		$controller = $phpbb_container->get( 'danieltj.accountsecurity.controller.mcp' );
		$controller->reports_closed( $this->u_action );

	}

}
