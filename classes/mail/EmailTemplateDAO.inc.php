<?php

/**
 * @file classes/mail/EmailTemplateDAO.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EmailTemplateDAO
 * @ingroup mail
 * @see EmailTemplate
 *
 * @brief Operations for retrieving and modifying Email Template objects.
 */

// $Id$


import('mail.PKPEmailTemplateDAO');
import('mail.EmailTemplate');

class EmailTemplateDAO extends PKPEmailTemplateDAO {
	/**
	 * Retrieve a base email template by key.
	 * @param $emailKey string
	 * @param $conferenceId int
	 * @return BaseEmailTemplate
	 */
	function &getBaseEmailTemplate($emailKey, $assocType = null, $assocId = null) {
		if (func_num_args() < 3) {
			$assocId = $assocType;
			$assocType = ASSOC_TYPE_CONFERENCE;
		}
		return parent::getBaseEmailTemplate($emailKey, $assocType, $assocId);
	}

	/**
	 * Retrieve localized email template by key.
	 * @param $emailKey string
	 * @param $conferenceId int
	 * @return LocaleEmailTemplate
	 */
	function &getLocaleEmailTemplate($emailKey, $assocType = null, $assocId = null) {
		if (func_num_args() < 3) {
			$assocId = $assocType;
			$assocType = ASSOC_TYPE_CONFERENCE;
		}
		return parent::getLocaleEmailTemplate($emailKey, $assocType, $assocId);
	}

	/**
	 * Retrieve an email template by key.
	 * @param $emailKey string
	 * @param $locale string
	 * @param $conferenceId int
	 * @return EmailTemplate
	 */
	function &getEmailTemplate($emailKey, $locale, $assocType = null, $assocId = null) {
		if (func_num_args() < 4) {
			$assocId = $assocType;
			$assocType = ASSOC_TYPE_CONFERENCE;
		}
		return parent::getEmailTemplate($emailKey, $locale, $assocType, $assocId);
	}

	/**
	 * Delete an email template by key.
	 * @param $emailKey string
	 * @param $conferenceId int
	 */
	function deleteEmailTemplateByKey($emailKey, $assocType = null, $assocId = null) {
		if (func_num_args() < 3) {
			$assocId = $assocType;
			$assocType = ASSOC_TYPE_CONFERENCE;
		}
		return parent::deleteEmailTemplateByKey($emailKey, $assocType, $assocId);
	}

	/**
	 * Retrieve all email templates.
	 * @param $locale string
	 * @param $conferenceId int
	 * @param $rangeInfo object optional
	 * @return array Email templates
	 */
	function &getEmailTemplates($locale, $assocType = null, $assocId = null, $rangeInfo = null) {
		if (func_num_args() < 3 || (func_num_args() == 3 && is_object($assocId))) {
			$rangeInfo = func_num_args() == 3 ? $assocId : null;
			$assocId = $assocType;
			$assocType = ASSOC_TYPE_CONFERENCE;
		}
		return parent::getEmailTemplates($locale, $assocType, $assocId, $rangeInfo);
	}

	/**
	 * Delete all email templates for a specific conference.
	 * @param $conferenceId int
	 */
	function deleteEmailTemplatesByConference($conferenceId) {
		return parent::deleteEmailTemplatesByAssoc(ASSOC_TYPE_CONFERENCE, $conferenceId);
	}

	/**
	 * Check if a template exists with the given email key for a conference.
	 * @param $emailKey string
	 * @param $conferenceId int
	 * @return boolean
	 */
	function templateExistsByKey($emailKey, $assocType = null, $assocId = null) {
		if (func_num_args() < 3) {
			$assocId = $assocType;
			$assocType = ASSOC_TYPE_CONFERENCE;
		}
		return parent::templateExistsByKey($emailKey, $assocType, $assocId);
	}

	/**
	 * Check if a custom template exists with the given email key for a conference.
	 * @param $emailKey string
	 * @param $conferenceId int
	 * @return boolean
	 */
	function customTemplateExistsByKey($emailKey, $assocType = null, $assocId = null) {
		if (func_num_args() < 3) {
			$assocId = $assocType;
			$assocType = ASSOC_TYPE_CONFERENCE;
		}
		return parent::customTemplateExistsByKey($emailKey, $assocType, $assocId);
	}
}

?>
