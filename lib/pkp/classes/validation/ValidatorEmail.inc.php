<?php

/**
 * @file classes/validation/ValidatorEmail.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ValidatorEmail
 * @ingroup validation
 * @see Validator
 *
 * @brief Validation check for email addresses.
 */

import('validation.ValidatorRegExp');

class ValidatorEmail extends ValidatorRegExp {
	/**
	 * Constructor.
	 */

	/**
	 * PHP 8 compatibility: forward to legacy PHP4-style constructor.
	 */
	function __construct() {
		$args = func_get_args();
		call_user_func_array(array($this, 'ValidatorEmail'), $args);
	}
	function ValidatorEmail() {
		parent::ValidatorRegExp(ValidatorEmail::getRegexp());
	}


	//
	// Public static methods
	//
	/**
	 * @see ValidatorUri::getRegexp()
	 * @return string
	 */
	static function getRegexp() {
		return '/^' . PCRE_EMAIL_ADDRESS . '$/i';
	}
}

?>
