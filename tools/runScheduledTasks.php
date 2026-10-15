<?php

/**
 * @file tools/runScheduledTasks.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class runScheduledTasks
 * @ingroup tools
 *
 * @brief CLI tool to execute a set of scheduled tasks.
 */

// $Id$


require(dirname(__FILE__) . '/bootstrap.inc.php');

import('cliTool.ScheduledTaskTool');

class runScheduledTasks extends ScheduledTaskTool {
	/**
	 * Constructor.
	 * @param $argv array command-line arguments
	 * 		If specified, the first parameter should be the path to
	 *		a tasks XML descriptor file (other than the default)
	 */

	/**
	 * PHP 8 compatibility: forward to legacy PHP4-style constructor.
	 */
	function __construct() {
		$args = func_get_args();
		call_user_func_array(array($this, 'runScheduledTasks'), $args);
	}
	function runScheduledTasks($argv = array()) {
		parent::ScheduledTaskTool($argv);
	}

}

$tool = new runScheduledTasks(isset($argv) ? $argv : array());
$tool->execute();

?>
