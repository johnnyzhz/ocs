<?php

/**
 * @file classes/core/Handler.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Handler
 * @ingroup handler
 *
 * @brief Base request handler application class
 */


import('handler.PKPHandler');
import('handler.validation.HandlerValidatorConference');
import('handler.validation.HandlerValidatorSchedConf');
import('handler.validation.HandlerValidatorRoles');
import('handler.validation.HandlerValidatorSubmissionComment');

class Handler extends PKPHandler {

	/**
	 * PHP 8 compatibility: forward to legacy PHP4-style constructor.
	 */
	function __construct() {
		$args = func_get_args();
		call_user_func_array(array($this, 'Handler'), $args);
	}
	function Handler() {
		parent::PKPHandler();

		$conference =& Request::getConference();
		$page = Request::getRequestedPage();
		if ( $conference && $conference->getSetting('restrictSiteAccess')) { 
			$this->addCheck(new HandlerValidatorCustom($this, true, null, null, function($page) { if (!Validation::isLoggedIn() && !in_array($page, Handler::getLoginExemptions())) return false; else return true; }, array($page)));
		}
	}
}

?>