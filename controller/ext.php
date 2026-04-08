<?php

/**
 * @package Report User
 * @copyright (c) 2026 Daniel James
 * @license https://opensource.org/license/gpl-2-0
 */

namespace danieltj\reportuser\controller;

use danieltj\reportuser\includes\functions;

final class ext {

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
	 * Create a new user report.
	 */
	public function report() {

		die( 'report' );

	}

	/**
	 * Submit a new user report.
	 */
	public function submit() {

		die( 'submit' );

	}

}
