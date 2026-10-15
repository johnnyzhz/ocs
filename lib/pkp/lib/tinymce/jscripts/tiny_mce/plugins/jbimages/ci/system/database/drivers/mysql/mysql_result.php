<?php

if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

require_once(BASEPATH . 'database/drivers/mysqli/mysqli_result.php');

class CI_DB_mysql_result extends CI_DB_mysqli_result
{
}
