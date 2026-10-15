<?php

/**
 * @file ConferenceSetupStep5Form.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ConferenceSetupStep5Form
 * @ingroup manager_form_setup
 *
 * @brief Form for Step 5 of conference setup.
 */

//$Id$

import("manager.form.setup.ConferenceSetupForm");

class ConferenceSetupStep5Form extends ConferenceSetupForm {
	/**
	 * Constructor.
	 */

	/**
	 * PHP 8 compatibility: forward to legacy PHP4-style constructor.
	 */
	function __construct() {
		$args = func_get_args();
		call_user_func_array(array($this, 'ConferenceSetupStep5Form'), $args);
	}
	function ConferenceSetupStep5Form() {
		parent::ConferenceSetupForm(
			5,
			array(
				'paperEventLog' => 'bool',
				'paperEmailLog' => 'bool',
				'conferenceEventLog' => 'bool'
			)
		);
	}
}

?>
