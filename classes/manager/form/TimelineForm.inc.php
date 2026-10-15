<?php

/**
 * @file TimelineForm.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class TimelineForm
 * @ingroup manager_form
 *
 * @brief Form for creating and modifying scheduled conference tracks.
 */

//$Id$

import('form.Form');

class TimelineForm extends Form {

	/** @var boolean can edit metadata */
	var $canEdit;

	/**
	 * Constructor.
	 * @param $trackId int omit for a new track
	 */

	/**
	 * PHP 8 compatibility: forward to legacy PHP4-style constructor.
	 */
	function __construct() {
		$args = func_get_args();
		call_user_func_array(array($this, 'TimelineForm'), $args);
	}
	function TimelineForm($overrideDates = false, $readOnly = false) {
		$this->canEdit = false;
		if (!$readOnly && Validation::isConferenceManager()) {
			$this->canEdit = true;
		}

		if ($this->canEdit) {
			parent::Form('manager/timelineEdit.tpl');
		} else {
			parent::Form('manager/timelineView.tpl');
		}

		if (!$overrideDates) {
			// Conference start must happen before conference end
			$this->addCheck(new FormValidatorCustom($this, 'endDate', 'required', 'manager.timeline.form.conferenceEndDateBeforeConferenceStart', function($endDate, $form) { return ($endDate >= $form->getData('startDate')); }, array(&$this)));

			// Conference start must happen after submission close
			$this->addCheck(new FormValidatorCustom($this, 'startDate', 'required', 'manager.timeline.form.conferenceStartDateBeforeSubmissionsClose', function($startDate, $form) { return ($startDate >= $form->getData('submissionsCloseDate')); }, array(&$this)));

			// Conference site start must happen before site end
			$this->addCheck(new FormValidatorCustom($this, 'siteStartDate', 'required', 'manager.timeline.form.siteEndDateBeforeSiteStart', function($siteStartDate, $form) { return ($siteStartDate <= $form->getData('siteEndDate')); }, array(&$this)));

			// Conference start must happen after site go-live
			$this->addCheck(new FormValidatorCustom($this, 'siteStartDate', 'required', 'manager.timeline.form.conferenceStartBeforeSiteStart', function($siteStartDate, $form) { return ($siteStartDate <= $form->getData('startDate')); }, array(&$this)));

			// Move to Conference Archive must come after Last Day of Conf
			$this->addCheck(new FormValidatorCustom($this, 'siteEndDate', 'required', 'manager.timeline.form.siteEndBeforeLastDay', function($siteEndDate, $form) { return ($siteEndDate >= $form->getData('endDate')); }, array(&$this)));

			// regAuthorOpenDate must be before regAuthorCloseDate
			$this->addCheck(new FormValidatorCustom($this, 'regAuthorOpenDate', 'required', 'manager.timeline.form.regAuthorCloseDateBeforeRegAuthorOpenDate', function($regAuthorOpenDate, $form) { return ($regAuthorOpenDate <= $form->getData('regAuthorCloseDate')); }, array(&$this)));

			// regReviewerOpenDate must be before regReviewerCloseDate
			$this->addCheck(new FormValidatorCustom($this, 'regReviewerOpenDate', 'required', 'manager.timeline.form.regReviewerCloseDateBeforeRegReviewerOpenDate', function($regReviewerOpenDate, $form) { return ($regReviewerOpenDate <= $form->getData('regReviewerCloseDate')); }, array(&$this)));

			// Submission CfP must come before Close Submissions
			$this->addCheck(new FormValidatorCustom($this, 'showCFPDate', 'required', 'manager.timeline.form.submissionsCloseBeforeCFP', function($showCFPDate, $form) { return ($showCFPDate <= $form->getData('submissionsCloseDate')); }, array(&$this)));
		}
		$this->addCheck(new FormValidatorPost($this));
	}

	/**
	 * Get the earliest and latest dates for the conference. These should
	 * be included on the timeline form date ranges as possibilities.
	 * @return array ($earliestDate, $latestDate)
	 */
	static function getOutsideDates(&$schedConf) {
		$dates = array();
		$startDate = self::getDateTimestamp($schedConf->getStartDate());
		$endDate = self::getDateTimestamp($schedConf->getEndDate());
		if ($startDate !== false) $dates[] = $startDate;
		if ($endDate !== false) $dates[] = $endDate;
		foreach (array(
			'startDate', 'endDate',
			'regAuthorOpenDate', 'regAuthorCloseDate',
			'showCFPDate',
			'submissionsOpenDate', 'submissionsCloseDate',
			'regReviewerOpenDate', 'regReviewerCloseDate', 'closeReviewProcessDate',
			'postAbstractsDate', 'postScheduleDate', 'postPapersDate', 'postScheduleDate',
			'delayOpenAccessDate', 'closeCommentsDate'
		) as $dateSettingName) {
			$dateSetting = self::getDateTimestamp($schedConf->getSetting($dateSettingName));
			if ($dateSetting !== false) $dates[] = $dateSetting;
		}

		$earliestDate = $latestDate = time();
		foreach ($dates as $date) {
			if (!$earliestDate || $earliestDate > $date) $earliestDate = $date;
			if (!$latestDate || $latestDate < $date) $latestDate = $date;
		}

		$secsPerYear = 60 * 60 * 24 * 366; // (Rounding up)
		$earliestDate = min($earliestDate, time() - $secsPerYear); // Last year must be included
		$latestDate = max($latestDate, time() + ($secsPerYear * SCHED_CONF_DATE_YEAR_OFFSET_FUTURE));

		return array($earliestDate, $latestDate);
	}

	/**
	 * Convert a stored date or timestamp to a Unix timestamp.
	 * @param $date string|int
	 * @return int|boolean false if the date is invalid
	 */
	static function getDateTimestamp($date) {
		if (is_int($date)) {
			return $date;
		}
		if (is_string($date) && preg_match('/^-?\d+$/', $date)) {
			return (int) $date;
		}
		if (!is_string($date) || $date === '') {
			return false;
		}

		$timestamp = strtotime($date);
		return $timestamp === false ? false : $timestamp;
	}

	/**
	 * Format a stored date or timestamp for a conference log entry.
	 */
	function formatDateForLog($format, $date) {
		$timestamp = self::getDateTimestamp($date);
		return $timestamp === false ? (string) $date : strftime($format, $timestamp);
	}

	/**
	 * Display the form.
	 */
	function display($request = null, $fetch = false) {
		$schedConf =& Request::getSchedConf();
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('helpTopicId','conference.currentConferences.timeline');

		list($earliestDate, $latestDate) = $this->getOutsideDates($schedConf);
		$templateMgr->assign('firstYear', strftime('%Y', $earliestDate));
		$templateMgr->assign('lastYear', strftime('%Y', $latestDate));

		return parent::display($request, $fetch);
	}

	/**
	 * Initialize form data from current settings.
	 */
	function initData() {
		$schedConf =& Request::getSchedConf();

		$this->_data = array(
			'siteStartDate' => $schedConf->getStartDate(),
			'siteEndDate' => $schedConf->getEndDate(),

			'startDate' => $schedConf->getSetting('startDate'),
			'endDate' => $schedConf->getSetting('endDate'),

			'regAuthorOpenDate' => $schedConf->getSetting('regAuthorOpenDate'),
			'regAuthorCloseDate' => $schedConf->getSetting('regAuthorCloseDate'),
			'showCFPDate' => $schedConf->getSetting('showCFPDate'),
			'submissionsOpenDate' => $schedConf->getSetting('submissionsOpenDate'),
			'submissionsCloseDate' => $schedConf->getSetting('submissionsCloseDate'),
			'regReviewerOpenDate' => $schedConf->getSetting('regReviewerOpenDate'),
			'regReviewerCloseDate' => $schedConf->getSetting('regReviewerCloseDate'),
			'closeReviewProcessDate' => $schedConf->getSetting('closeReviewProcessDate'),
			'postAbstracts' => $schedConf->getSetting('postAbstracts'),
			'postAbstractsDate' => $schedConf->getSetting('postAbstractsDate'),
			'postSchedule' => $schedConf->getSetting('postSchedule'),
			'postScheduleDate' => $schedConf->getSetting('postScheduleDate'),
			'postPapers' => $schedConf->getSetting('postPapers'),
			'postPapersDate' => $schedConf->getSetting('postPapersDate'),
			'postTimeline' => $schedConf->getSetting('postTimeline'),
			'postOverview' => $schedConf->getSetting('postOverview'),
			'postCFP' => $schedConf->getSetting('postCFP'),
			'postProposalSubmission' => $schedConf->getSetting('postProposalSubmission'),
			'postTrackPolicies' => $schedConf->getSetting('postTrackPolicies'),
			'postProgram' => $schedConf->getSetting('postProgram'),
			'postPresentations' => $schedConf->getSetting('postPresentations'),
			'postAccommodation' => $schedConf->getSetting('postAccommodation'),
			'postSupporters' => $schedConf->getSetting('postSupporters'),
			'postPayment' => $schedConf->getSetting('postPayment'),
			'delayOpenAccess' => $schedConf->getSetting('delayOpenAccess'),
			'delayOpenAccessDate' => $schedConf->getSetting('delayOpenAccessDate'),
			'closeComments' => $schedConf->getSetting('closeComments'),
			'closeCommentsDate' => $schedConf->getSetting('closeCommentsDate')
		);

		// Make sure the initial values are passable
		$timing = array(
			//  site go-live happens before conference start
			array('siteStartDate', 'startDate'),

			//  Submission CfP is before Close Submissions
			array('showCFPDate', 'submissionsCloseDate'),

			//  submissions close before the conference starts
			array('submissionsCloseDate', 'startDate'),

			// conference begins before it ends
			array('startDate', 'endDate'),

			// conference start happens before site moves to archive
			array('startDate', 'siteEndDate' ),

			//  conference site starts before site end
			array('siteStartDate', 'siteEndDate'),

			//  last day of conf is before conference archive
			array('endDate', 'siteEndDate'),

			//  regAuthorOpenDate is before regAuthorCloseDate
			array('regAuthorOpenDate', 'regAuthorCloseDate'),

			//  regReviewerOpenDate is before regReviewerCloseDate
			array('regReviewerOpenDate', 'regReviewerCloseDate')
		);

		// Ensure the default dates for the form are acceptable
		$timingCorrect = false;
		while (!$timingCorrect) {
			$timingCorrect = true;
			foreach ($timing as $rule) {
				$dateTimestamps = array();
				foreach ($rule as $fieldName) {
					$dateValue = $this->getData($fieldName);
					if ($dateValue === null || $dateValue === '') {
						$dateValue = time();
						$this->setData($fieldName, $dateValue);
					}
					$dateTimestamp = self::getDateTimestamp($dateValue);
					if ($dateTimestamp === false) {
						$dateTimestamp = time();
						$this->setData($fieldName, $dateTimestamp);
					}
					$dateTimestamps[] = $dateTimestamp;
				}

				if ($dateTimestamps[0] >= $dateTimestamps[1]) {
					$this->setData($rule[1], strtotime('+1 day', $dateTimestamps[0]));
					$timingCorrect = false;
				}
			}
		}

	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserDateVars(array(
			'siteStartDate', 'siteEndDate',
			'startDate', 'endDate',
			'regAuthorOpenDate', 'regAuthorCloseDate',
			'showCFPDate',
			'submissionsOpenDate', 'submissionsCloseDate',
			'regReviewerOpenDate', 'regReviewerCloseDate', 'closeReviewProcessDate',
			'postAbstractsDate', 'postPapersDate', 'postScheduleDate',
			'delayOpenAccessDate',
			'closeCommentsDate'
		));

		$this->readUserVars(array(
			'postAbstracts',
			'postPapers',
			'postSchedule',
			'delayOpenAccess',
			'closeComments',
			'postTimeline',
			'postOverview',
			'postCFP',
			'postProposalSubmission',
			'postTrackPolicies',
			'postProgram',
			'postPresentations',
			'postAccommodation',
			'postSupporters',
			'postPayment'
		));
	}

	/**
	 * Save track.
	 */
	function execute() {
		$schedConfDao =& DAORegistry::getDao('SchedConfDAO');
		$schedConf =& Request::getSchedConf();

		import('conference.log.ConferenceLog');
		import('conference.log.ConferenceEventLogEntry');

		//
		// Don't log these, since they aren't particularly nefarious.
		//

		// Website start date and end date.

		if ($schedConf->getStartDate() != $this->_data['siteStartDate']) {
			$schedConf->setStartDate($this->_data['siteStartDate']);
			$schedConfDao->updateSchedConf($schedConf);
		}

		if ($schedConf->getEndDate() != $this->_data['siteEndDate']) {
			$schedConf->setEndDate($this->_data['siteEndDate']);
			$schedConfDao->updateSchedConf($schedConf);
		}

		// Conference Information display flags
		$schedConf->updateSetting('postTimeline', $this->getData('postTimeline'), 'bool');
		$schedConf->updateSetting('postOverview', $this->getData('postOverview'), 'bool');
		$schedConf->updateSetting('postCFP', $this->getData('postCFP'), 'bool');
		$schedConf->updateSetting('postProposalSubmission', $this->getData('postProposalSubmission'), 'bool');
		$schedConf->updateSetting('postTrackPolicies', $this->getData('postTrackPolicies'), 'bool');
		$schedConf->updateSetting('postProgram', $this->getData('postProgram'), 'bool');
		$schedConf->updateSetting('postPresentations', $this->getData('postPresentations'), 'bool');
		$schedConf->updateSetting('postAccommodation', $this->getData('postAccommodation'), 'bool');
		$schedConf->updateSetting('postSupporters', $this->getData('postSupporters'), 'bool');
		$schedConf->updateSetting('postPayment', $this->getData('postPayment'), 'bool');


		//
		// Log the rest.
		//

		$dateFormatShort = Config::getVar('general', 'date_format_short');

		// Physical scheduled conference start date and end date
		if ($schedConf->getSetting('startDate') != $this->_data['startDate']) {
			ConferenceLog::logEvent(
				$schedConf->getConferenceId(),
				$schedConf->getId(),
				CONFERENCE_LOG_CONFIGURATION,
				LOG_TYPE_DEFAULT,
				0, 'log.timeline.startDateChanged',
				array(	'oldStartDate' => $this->formatDateForLog($dateFormatShort, $schedConf->getSetting('startDate')),
					'newStartDate' => $this->formatDateForLog($dateFormatShort, $this->_data['startDate'])));
			$schedConf->updateSetting('startDate', $this->_data['startDate'], 'date');
		}

		if ($schedConf->getSetting('endDate') != $this->_data['endDate']) {
			ConferenceLog::logEvent(
				$schedConf->getConferenceId(),
				$schedConf->getId(),
				CONFERENCE_LOG_CONFIGURATION,
				LOG_TYPE_DEFAULT,
				0, 'log.timeline.endDateChanged',
				array('oldEndDate' => $this->formatDateForLog($dateFormatShort, $schedConf->getSetting('endDate')),
					'newEndDate' => $this->formatDateForLog($dateFormatShort, $this->_data['endDate'])));
			$schedConf->updateSetting('endDate', $this->_data['endDate'], 'date');
		}

		if ($schedConf->getSetting('regAuthorOpenDate') != $this->_data['regAuthorOpenDate']) {
			ConferenceLog::logEvent(
				$schedConf->getConferenceId(),
				$schedConf->getId(),
				CONFERENCE_LOG_CONFIGURATION,
				LOG_TYPE_DEFAULT,
				0, 'log.timeline.regAuthorOpenDateChanged',
				array('oldRegAuthorOpenDate' => $this->formatDateForLog($dateFormatShort, $schedConf->getSetting('regAuthorOpenDate')),
					'newRegAuthorOpenDate' => $this->formatDateForLog($dateFormatShort, $this->_data['regAuthorOpenDate'])));
			$schedConf->updateSetting('regAuthorOpenDate', $this->_data['regAuthorOpenDate'], 'date');
		}
		if ($schedConf->getSetting('regAuthorCloseDate') != $this->_data['regAuthorCloseDate']) {
			ConferenceLog::logEvent(
				$schedConf->getConferenceId(),
				$schedConf->getId(),
				CONFERENCE_LOG_CONFIGURATION,
				LOG_TYPE_DEFAULT,
				0, 'log.timeline.regAuthorCloseDateChanged',
				array('oldRegAuthorCloseDate' => $this->formatDateForLog($dateFormatShort, $schedConf->getSetting('regAuthorCloseDate')),
					'newRegAuthorCloseDate' => $this->formatDateForLog($dateFormatShort, $this->_data['regAuthorCloseDate'])));
			$schedConf->updateSetting('regAuthorCloseDate', $this->_data['regAuthorCloseDate'], 'date');
		}
		if ($schedConf->getSetting('showCFPDate') != $this->_data['showCFPDate']) {
			ConferenceLog::logEvent(
				$schedConf->getConferenceId(),
				$schedConf->getId(),
				CONFERENCE_LOG_CONFIGURATION,
				LOG_TYPE_DEFAULT,
				0, 'log.timeline.showCFPDateChanged',
				array(	'oldShowCFPDate' => $this->formatDateForLog($dateFormatShort, $schedConf->getSetting('showCFPDate')),
					'newShowCFPDate' => $this->formatDateForLog($dateFormatShort, $this->_data['showCFPDate'])));
			$schedConf->updateSetting('showCFPDate', $this->_data['showCFPDate'], 'date');
		}

		if ($schedConf->getSetting('submissionsOpenDate') != $this->_data['submissionsOpenDate']) {
			ConferenceLog::logEvent(
				$schedConf->getConferenceId(),
				$schedConf->getId(),
				CONFERENCE_LOG_CONFIGURATION,
				LOG_TYPE_DEFAULT,
				0, 'log.timeline.submissionsOpenDateChanged',
				array(	'oldSubmissionsOpenDate' => $this->formatDateForLog($dateFormatShort, $schedConf->getSetting('submissionsOpenDate')),
					'newSubmissionsOpenDate' => $this->formatDateForLog($dateFormatShort, $this->_data['submissionsOpenDate'])));
			$schedConf->updateSetting('submissionsOpenDate', $this->_data['submissionsOpenDate'], 'date');
		}
		if ($schedConf->getSetting('submissionsCloseDate') != $this->_data['submissionsCloseDate']) {
			ConferenceLog::logEvent(
				$schedConf->getConferenceId(),
				$schedConf->getId(),
				CONFERENCE_LOG_CONFIGURATION,
				LOG_TYPE_DEFAULT,
				0, 'log.timeline.submissionsCloseDateChanged',
				array('oldSubmissionsCloseDate' => $this->formatDateForLog($dateFormatShort, $schedConf->getSetting('submissionsCloseDate')),
					'newSubmissionsCloseDate' => $this->formatDateForLog($dateFormatShort, $this->_data['submissionsCloseDate'])));
			$schedConf->updateSetting('submissionsCloseDate', $this->_data['submissionsCloseDate'], 'date');
		}
		if ($schedConf->getSetting('regReviewerOpenDate') != $this->_data['regReviewerOpenDate']) {
			ConferenceLog::logEvent(
				$schedConf->getConferenceId(),
				$schedConf->getId(),
				CONFERENCE_LOG_CONFIGURATION,
				LOG_TYPE_DEFAULT,
				0, 'log.timeline.regReviewerOpenDateChanged',
				array('oldRegReviewerOpenDate' => $this->formatDateForLog($dateFormatShort, $schedConf->getSetting('regReviewerOpenDate')),
					'newRegReviewerOpenDate' => $this->formatDateForLog($dateFormatShort, $this->_data['regReviewerOpenDate'])));
			$schedConf->updateSetting('regReviewerOpenDate', $this->_data['regReviewerOpenDate'], 'date');
		}
		if ($schedConf->getSetting('regReviewerCloseDate') != $this->_data['regReviewerCloseDate']) {
			ConferenceLog::logEvent(
				$schedConf->getConferenceId(),
				$schedConf->getId(),
				CONFERENCE_LOG_CONFIGURATION,
				LOG_TYPE_DEFAULT,
				0, 'log.timeline.regReviewerCloseDateChanged',
				array('oldRegReviewerCloseDate' => $this->formatDateForLog($dateFormatShort, $schedConf->getSetting('regReviewerCloseDate')),
					'newRegReviewerCloseDate' => $this->formatDateForLog($dateFormatShort, $this->_data['regReviewerCloseDate'])));
			$schedConf->updateSetting('regReviewerCloseDate', $this->_data['regReviewerCloseDate'], 'date');
		}
		if ($schedConf->getSetting('postAbstractsDate') != $this->_data['postAbstractsDate']) {
			ConferenceLog::logEvent(
				$schedConf->getConferenceId(),
				$schedConf->getId(),
				CONFERENCE_LOG_CONFIGURATION,
				LOG_TYPE_DEFAULT,
				0, 'log.timeline.postAbstractsDateChanged',
				array(	'oldPostAbstractsDate' => $this->formatDateForLog($dateFormatShort, $schedConf->getSetting('postAbstractsDate')),
					'newPostAbstractsDate' => $this->formatDateForLog($dateFormatShort, $this->_data['postAbstractsDate'])));
			$schedConf->updateSetting('postAbstractsDate', $this->_data['postAbstractsDate'], 'date');
		}
		if ($schedConf->getSetting('postAbstracts') != $this->_data['postAbstracts']) {
			ConferenceLog::logEvent(
				$schedConf->getConferenceId(),
				$schedConf->getId(),
				CONFERENCE_LOG_CONFIGURATION,
				LOG_TYPE_DEFAULT,
				0, 'log.timeline.postAbstractsChanged',
				array(	'oldPostAbstracts' => __($schedConf->getSetting('postAbstracts')?'common.true':'common.false'),
					'newPostAbstracts' => __($this->_data['postAbstracts'])?'common.true':'common.false'));
			$schedConf->updateSetting('postAbstracts', $this->_data['postAbstracts'], 'bool');
		}
		if ($schedConf->getSetting('postScheduleDate') != $this->_data['postScheduleDate']) {
			ConferenceLog::logEvent(
				$schedConf->getConferenceId(),
				$schedConf->getId(),
				CONFERENCE_LOG_CONFIGURATION,
				LOG_TYPE_DEFAULT,
				0, 'log.timeline.postScheduleDateChanged',
				array(  'oldPostScheduleDate' => $this->formatDateForLog($dateFormatShort, $schedConf->getSetting('postScheduleDate')),
					'newPostScheduleDate' => $this->formatDateForLog($dateFormatShort, $this->_data['postScheduleDate'])));
			$schedConf->updateSetting('postScheduleDate', $this->_data['postScheduleDate'], 'date');
		}
		if ($schedConf->getSetting('postSchedule') != $this->_data['postSchedule']) {
			ConferenceLog::logEvent(
				$schedConf->getConferenceId(),
				$schedConf->getId(),
				CONFERENCE_LOG_CONFIGURATION,
				LOG_TYPE_DEFAULT,
				0, 'log.timeline.postScheduleChanged',
				array(  'oldPostSchedule' => __($schedConf->getSetting('postSchedule')?'common.true':'common.false'),
					'newPostSchedule' => __($this->_data['postSchedule']?'common.true':'common.false')));
			$schedConf->updateSetting('postSchedule', $this->_data['postSchedule'], 'bool');
		}
		if ($schedConf->getSetting('postPapersDate') != $this->_data['postPapersDate']) {
			ConferenceLog::logEvent(
				$schedConf->getConferenceId(),
				$schedConf->getId(),
				CONFERENCE_LOG_CONFIGURATION,
				LOG_TYPE_DEFAULT,
				0, 'log.timeline.postPapersDateChanged',
				array(	'oldPostPapersDate' => $this->formatDateForLog($dateFormatShort, $schedConf->getSetting('postPapersDate')),
					'newPostPapersDate' => $this->formatDateForLog($dateFormatShort, $this->_data['postPapersDate'])));
			$schedConf->updateSetting('postPapersDate', $this->_data['postPapersDate'], 'date');
		}
		if ($schedConf->getSetting('postPapers') != $this->_data['postPapers']) {
			ConferenceLog::logEvent(
				$schedConf->getConferenceId(),
				$schedConf->getId(),
				CONFERENCE_LOG_CONFIGURATION,
				LOG_TYPE_DEFAULT,
				0, 'log.timeline.postPapersChanged',
				array(	'oldPostPapers' => __($schedConf->getSetting('postPapers')?'common.true':'common.false'),
					'newPostPapers' => __($this->_data['postPapers']?'common.true':'common.false')));
			$schedConf->updateSetting('postPapers', $this->_data['postPapers'], 'bool');
		}
		if ($schedConf->getSetting('delayOpenAccessDate') != $this->_data['delayOpenAccessDate']) {
			ConferenceLog::logEvent(
				$schedConf->getConferenceId(),
				$schedConf->getId(),
				CONFERENCE_LOG_CONFIGURATION,
				LOG_TYPE_DEFAULT,
				0, 'log.timeline.delayOpenAccessDateChanged',
				array(	'oldDelayOpenAccessDate' => $this->formatDateForLog($dateFormatShort, $schedConf->getSetting('delayOpenAccessDate')),
					'newDelayOpenAccessDate' => $this->formatDateForLog($dateFormatShort, $this->_data['delayOpenAccessDate'])));
			$schedConf->updateSetting('delayOpenAccessDate', $this->_data['delayOpenAccessDate'], 'date');
		}
		if ($schedConf->getSetting('delayOpenAccess') != $this->_data['delayOpenAccess']) {
			ConferenceLog::logEvent(
				$schedConf->getConferenceId(),
				$schedConf->getId(),
				CONFERENCE_LOG_CONFIGURATION,
				LOG_TYPE_DEFAULT,
				0, 'log.timeline.delayOpenAccessChanged',
				array(	'oldDelayOpenAccess' => __($schedConf->getSetting('delayOpenAccess')?'common.true':'common.false'),
					'newDelayOpenAccess' => __($this->_data['delayOpenAccess']?'common.true':'common.false')));
			$schedConf->updateSetting('delayOpenAccess', $this->_data['delayOpenAccess'], 'bool');
		}
		if ($schedConf->getSetting('closeCommentsDate') != $this->_data['closeCommentsDate']) {
			ConferenceLog::logEvent(
				$schedConf->getConferenceId(),
				$schedConf->getId(),
				CONFERENCE_LOG_CONFIGURATION,
				LOG_TYPE_DEFAULT,
				0, 'log.timeline.closeCommentsDateChanged',
				array(	'oldCloseCommentsDate' => $this->formatDateForLog($dateFormatShort, $schedConf->getSetting('closeCommentsDate')),
					'newCloseCommentsDate' => $this->formatDateForLog($dateFormatShort, $this->_data['closeCommentsDate'])));
			$schedConf->updateSetting('closeCommentsDate', $this->_data['closeCommentsDate'], 'date');
		}
		if ($schedConf->getSetting('closeComments') != $this->_data['closeComments']) {
			ConferenceLog::logEvent(
				$schedConf->getConferenceId(),
				$schedConf->getId(),
				CONFERENCE_LOG_CONFIGURATION,
				LOG_TYPE_DEFAULT,
				0, 'log.timeline.closeCommentsChanged',
				array(	'oldCloseComments' => __($schedConf->getSetting('closeComments')?'common.true':'common.false'),
					'newCloseComments' => __($this->_data['closeComments']?'common.true':'common.false')));
			$schedConf->updateSetting('closeComments', $this->_data['closeComments'], 'bool');
		}
	}
}

?>