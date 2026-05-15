<?php

/**
 * @package Report User
 * @copyright (c) 2026 Daniel James
 * @license https://opensource.org/license/gpl-2-0
 */

namespace danieltj\reportuser\mcp;

class reports_closed_module {

	/**
	 * @var string
	 */
	public $u_action;

	/**
	 * @var string
	 */
	public $tpl_name;

	/**
	 * @var string
	 */
	public $page_title;

	/**
	 * MCP module
	 */
	public function main( $id, $mode ) {

		global $phpbb_container;

		$language = $phpbb_container->get( 'language' );

		$this->tpl_name = 'mcp_user_reports';
		$this->page_title = $language->lang( 'MCP_USER_REPORTS_CLOSED' );

		$controller = $phpbb_container->get( 'danieltj.reportuser.controller.mcp' );
		$controller->reports( $id, $this->u_action, $mode );

	}

}
