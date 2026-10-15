<?php

/**
 * @file SchedConfSetupStep3Form.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SchedConfSetupStep3Form
 * @ingroup manager_form_schedConfSetup
 *
 * @brief Form for Step 3 of scheduled conference setup.
 */

import("manager.form.schedConfSetup.SchedConfSetupForm");

class SchedConfSetupStep3Form extends SchedConfSetupForm {


	/**
	 * PHP 8 compatibility: forward to legacy PHP4-style constructor.
	 */
	function __construct() {
		$args = func_get_args();
		call_user_func_array(array($this, 'SchedConfSetupStep3Form'), $args);
	}
	function SchedConfSetupStep3Form() {
		parent::SchedConfSetupForm(
			3,
			array(
				'reviewPolicy' => 'string',
				'reviewGuidelines' => 'string',
				'remindForInvite' => 'int',
				'remindForSubmit' => 'int',
				'rateReviewerOnQuality' => 'int',
				'restrictReviewerFileAccess' => 'int',
				'reviewerAccessKeysEnabled' => 'int',
				'reviewDeadlineType' => 'int',
				'numDaysBeforeInviteReminder' => 'int',
				'numDaysBeforeSubmitReminder' => 'int',
				'numWeeksPerReviewRelative'	=> 'int',
				'numWeeksPerReviewAbsolute'	=> 'date',
				'notifyAllAuthorsOnDecision' => 'bool'
			)
		);

		$this->addCheck(new FormValidatorEmail($this, 'copySubmissionAckAddress', 'optional', 'user.profile.form.emailRequired'));
	}

	/**
	 * Get the list of field names for which localized settings are used.
	 * @return array
	 */
	function getLocaleFieldNames() {
		return array('reviewPolicy', 'reviewGuidelines');
	}


	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$settingNames = array_keys($this->settings);
		$this->readUserVars($settingNames);
		$this->readUserDateVars(array('numWeeksPerReviewAbsolute'));
	}

	/**
	 * Display the form
	 */
	function display($request = null, $fetch = false) {
		$templateMgr =& TemplateManager::getManager($request);

		import('mail.MailTemplate');
		$mail = new MailTemplate('SUBMISSION_ACK');
		if ($mail->isEnabled()) {
			$templateMgr->assign('submissionAckEnabled', true);
		}

		if ($this->_data['reviewDeadlineType'] == REVIEW_DEADLINE_TYPE_ABSOLUTE) {
			$templateMgr->assign('absoluteReviewDate', $this->_data['numWeeksPerReviewAbsolute']);
		}

		if (Config::getVar('general', 'scheduled_tasks'))
			$templateMgr->assign('scheduledTasksEnabled', true);

		import('manager.form.TimelineForm');
		$schedConf =& Request::getSchedConf();
		list($earliestDate, $latestDate) = TimelineForm::getOutsideDates($schedConf);
		$templateMgr->assign('firstYear', strftime('%Y', $earliestDate));
		$templateMgr->assign('lastYear', strftime('%Y', $latestDate));

		return parent::display($request, $fetch);
	}


}

?>
