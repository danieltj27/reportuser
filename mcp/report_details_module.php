<?php

/**
 * @package Report User
 * @copyright (c) 2026 Daniel James
 * @license https://opensource.org/license/gpl-2-0
 */

namespace danieltj\reportuser\mcp;

class report_details_module {

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

		$this->tpl_name = 'mcp_user_report_details';
		$this->page_title = $language->lang( 'MCP_USER_REPORTS_DETAILS' );

		$controller = $phpbb_container->get( 'danieltj.reportuser.controller.mcp' );
		$controller->details( $id, $this->u_action, $mode );

	}

}
