<?php

/**
 * @file Help.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Help
 * @ingroup help
 * 
 * @brief Provides methods for translating help topic keys to their respected topic
 * help ids.
 */

// $Id$


import('help.PKPHelp');

class Help extends PKPHelp {
	/**
	 * Constructor.
	 */

	/**
	 * PHP 8 compatibility: forward to legacy PHP4-style constructor.
	 */
	function __construct() {
		$args = func_get_args();
		call_user_func_array(array($this, 'Help'), $args);
	}
	function Help() {
		parent::PKPHelp();
		import('help.OCSHelpMappingFile');
		$mainMappingFile = new OCSHelpMappingFile();
		$this->addMappingFile($mainMappingFile);
	}
}

?>
