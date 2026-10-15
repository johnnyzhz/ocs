<?php

if (!defined('ADODB_DIR')) {
	die();
}

if (!function_exists('mysqli_connect')) {
	throw new RuntimeException('The ADOdb MySQL driver requires the mysqli extension.');
}

require_once(ADODB_DIR . '/drivers/adodb-mysqli.inc.php');

class ADODB_mysql extends ADODB_mysqli {
	var $databaseType = 'mysqli';

	function __construct()
	{
		if (function_exists('mysqli_report')) {
			mysqli_report(MYSQLI_REPORT_OFF);
		}
	}

	function _pconnect($argHostname, $argUsername, $argPassword, $argDatabasename)
	{
		if ($argHostname && strpos($argHostname, 'p:') !== 0) {
			$argHostname = 'p:' . $argHostname;
		}

		return $this->_connect($argHostname, $argUsername, $argPassword, $argDatabasename);
	}
}
