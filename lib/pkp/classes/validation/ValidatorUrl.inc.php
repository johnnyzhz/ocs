<?php

/**
 * @file classes/validation/ValidatorUrl.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ValidatorUrl
 * @ingroup validation
 * @see Validator
 *
 * @brief Validation check for URLs.
 */

import('validation.ValidatorUri');

class ValidatorUrl extends ValidatorUri {
	/**
	 * Constructor.
	 */

	/**
	 * PHP 8 compatibility: forward to legacy PHP4-style constructor.
	 */
	function __construct() {
		$args = func_get_args();
		call_user_func_array(array($this, 'ValidatorUrl'), $args);
	}
	function ValidatorUrl() {
		parent::ValidatorUri(ValidatorUrl::_getAllowedSchemes());
	}

	//
	// Public static methods
	//
	/**
	 * @see ValidatorUri::getRegexp()
	 * @return string
	 */
	static function getRegexp($allowedSchemes = null) {
		return parent::getRegexp(ValidatorUrl::_getAllowedSchemes());
	}

	//
	// Private static methods
	//
	/**
	 * Return allowed schemes (PHP4 workaround for
	 * a private static field).
	 * @return array
	 */
	static function _getAllowedSchemes() {
		return array('http', 'https', 'ftp');
	}
}

?>
