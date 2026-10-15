<?php

/**
 * @file GenericPlugin.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class GenericPlugin
 * @ingroup plugins
 *
 * @brief Abstract class for generic plugins
 */

//$Id$

import('plugins.Plugin');

class GenericPlugin extends Plugin {

	/**
	 * PHP 8 compatibility: forward to legacy PHP4-style constructor.
	 */
	function __construct() {
		$args = func_get_args();
		call_user_func_array(array($this, 'GenericPlugin'), $args);
	}
	function GenericPlugin() {
		parent::Plugin();
	}

	// No additional functions for now.
}

?>
