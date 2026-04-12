<?php

/**
 * @package Report User
 * @copyright (c) 2026 Daniel James
 * @license https://opensource.org/license/gpl-2-0
 */

namespace danieltj\reportuser\migrations;

class v100 extends \phpbb\db\migration\migration {

	/**
	 * Check extension is installed.
	 */
	public function effectively_installed() {

		return $this->db_tools->sql_column_exists( $this->table_prefix . 'reports', 'reported_user_id' );

	}

	/**
	 * Require 3.3.0 or later.
	 */
	static public function depends_on() {

		return [ '\phpbb\db\migration\data\v330\v330' ];

	}

	/**
	 * Install
	 */
	public function update_schema() {

		return [
			'add_columns' => [
				$this->table_prefix . 'reports' => [
					'reported_user_id' => [ 'UINT:8', 0 ],
				],
			],
		];

	}

	/**
	 * Uninstall
	 */
	public function revert_schema() {

		return [
			'drop_columns' => [
				$this->table_prefix . 'reports' => [
					'reported_user_id',
				],
			],
		];

	}

	/**
	 * Add additional data.
	 */
	public function update_data() {

		return [
			[
				'config.add', [ 'report_user_notify_item_id', '0' ],
			],

			[ 'permission.add', [ 'm_user_report' ] ],
			[ 'if', [
				[ 'permission.role_exists', [ 'ROLE_MOD_FULL' ] ],
				[ 'permission.permission_set', [ 'ROLE_MOD_FULL', 'm_user_report' ] ],
			] ],
			[ 'if', [
				[ 'permission.role_exists', [ 'ROLE_MOD_STANDARD' ] ],
				[ 'permission.permission_set', [ 'ROLE_MOD_STANDARD', 'm_user_report' ] ],
			] ],

			[
				'module.add', [
					'mcp', 'MCP_REPORTS',
					[
						'module_auth'		=> 'ext_danieltj/reportuser && acl_m_user_report',
						'module_basename'	=> '\danieltj\reportuser\mcp\reports_open_module',
						'module_langname'	=> 'MCP_USER_REPORTS_OPEN',
						'module_mode'		=> 'user_reports_open',
					],
				],
			],
			[
				'module.add', [
					'mcp', 'MCP_REPORTS',
					[
						'module_auth'		=> 'ext_danieltj/reportuser && acl_m_user_report',
						'module_basename'	=> '\danieltj\reportuser\mcp\reports_closed_module',
						'module_langname'	=> 'MCP_USER_REPORTS_CLOSED',
						'module_mode'		=> 'user_reports_closed',
					],
				],
			],
		];

	}

}
