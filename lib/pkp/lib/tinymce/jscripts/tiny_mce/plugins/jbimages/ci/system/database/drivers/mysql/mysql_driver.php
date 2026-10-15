<?php

if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

require_once(BASEPATH . 'database/drivers/mysqli/mysqli_driver.php');

class CI_DB_mysql_driver extends CI_DB_mysqli_driver
{
}
