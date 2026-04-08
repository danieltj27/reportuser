<?php

/**
 * @package Report User
 * @copyright (c) 2026 Daniel James
 * @license https://opensource.org/license/gpl-2-0
 */

namespace danieltj\reportuser\mcp;

class reports_closed_info {

	public function module() {

		return [
			'filename'	=> '\danieltj\reportuser\mcp\reports_closed_module',
			'title'		=> 'MCP_USER_REPORTS_CLOSED',
			'modes'		=> [
				'status'	=> [
					'title'	=> 'MCP_USER_REPORTS_CLOSED',
					'auth'	=> 'ext_danieltj/reportuser && acl_m_user_report',
					'cat'	=> [ 'MCP_REPORTS' ],
				],
			],
		];

	}

}
