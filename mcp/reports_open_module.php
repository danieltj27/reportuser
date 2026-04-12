<?php

/**
 * @package Report User
 * @copyright (c) 2026 Daniel James
 * @license https://opensource.org/license/gpl-2-0
 */

namespace danieltj\reportuser\mcp;

class reports_open_module {

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
	 * MCP module
	 */
	public function main( $id, $mode ) {

		global $phpbb_container;

		$language = $phpbb_container->get( 'language' );

		$this->tpl_name = 'mcp_user_reports';
		$this->page_title = $language->lang( 'MCP_USER_REPORTS_OPEN' );

		$controller = $phpbb_container->get( 'danieltj.reportuser.controller.mcp' );
		$controller->reports( $this->u_action, $mode );

	}

}
