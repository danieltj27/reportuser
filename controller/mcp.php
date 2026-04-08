<?php

/**
 * @package Account Security
 * @copyright (c) 2026 Daniel James
 * @license https://opensource.org/license/gpl-2-0
 */

namespace danieltj\accountsecurity\controller;

use danieltj\accountsecurity\includes\functions;

final class mcp {

	/**
	 * @var functions
	 */
	protected $functions;

	/**
	 * Constructor
	 */
	public function __construct( functions $functions ) {

		$this->functions = $functions;

	}

	/**
	 * @todo
	 */
	public function reports( $action ) {

		die( 'reports' );

	}

	/**
	 * @todo
	 */
	public function reports_closed( $action ) {

		die( 'reports_closed' );

	}

}
