<?php

/**
 * @package Report User
 * @copyright (c) 2026 Daniel James
 * @license https://opensource.org/license/gpl-2-0
 */

namespace danieltj\reportuser\includes;

use phpbb\auth\auth;
use phpbb\db\driver\driver_interface as database;

final class functions {

	/**
	 * @var auth
	 */
	protected $auth;

	/**
	 * @var driver_interface
	 */
	protected $database;

	/**
	 * Constructor
	 */
	public function __construct( auth $auth, database $database ) {

		$this->auth = $auth;
		$this->database = $database;

	}

}
