<?php

if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

require_once(BASEPATH . 'database/drivers/mysqli/mysqli_utility.php');

class CI_DB_mysql_utility extends CI_DB_mysqli_utility
{
}
