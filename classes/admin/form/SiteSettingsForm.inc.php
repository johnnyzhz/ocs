<?php

/**
 * @file SiteSettingsForm.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SiteSettingsForm
 * @ingroup admin_form
 * @see PKPSiteSettingsForm
 *
 * @brief Form to edit site settings.
 */

// $Id$


import('admin.form.PKPSiteSettingsForm');

class SiteSettingsForm extends PKPSiteSettingsForm {

	/**
	 * Constructor.
	 */

	/**
	 * PHP 8 compatibility: forward to legacy PHP4-style constructor.
	 */
	function __construct() {
		$args = func_get_args();
		call_user_func_array(array($this, 'SiteSettingsForm'), $args);
	}
	function SiteSettingsForm() {
		parent::PKPSiteSettingsForm();
	}

	/**
	 * Display the form.
	 */
	function display($request = null, $fetch = false) {
		$conferenceDao =& DAORegistry::getDAO('ConferenceDAO');
		$conferences =& $conferenceDao->getConferenceTitles();
		$templateMgr =& TemplateManager::getManager($request);
		$templateMgr->assign('redirectOptions', $conferences);
		return parent::display($request, $fetch);
	}
}

?>
