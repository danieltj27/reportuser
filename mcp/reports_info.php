<?php

/**
 * @package Report User
 * @copyright (c) 2026 Daniel James
 * @license https://opensource.org/license/gpl-2-0
 */

namespace danieltj\reportuser\mcp;

class reports_info {

	public function module() {

		return [
			'filename'	=> '\danieltj\reportuser\mcp\reports_module',
			'title'		=> 'MCP_USER_REPORTS',
			'modes'		=> [
				'status'	=> [
					'title'	=> 'MCP_USER_REPORTS',
					'auth'	=> 'ext_danieltj/reportuser && acl_m_user_report',
					'cat'	=> [ 'MCP_REPORTS' ],
				],
			],
		];

	}

}
