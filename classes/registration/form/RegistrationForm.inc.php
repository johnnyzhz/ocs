<?php

/**
 * @defgroup registration_form
 */
 
/**
 * @file RegistrationForm.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class RegistrationForm
 * @ingroup registration_form
 *
 * @brief Form for conference managers to create/edit registration.
 */

//$Id$

import('form.Form');

class RegistrationForm extends Form {

	/** @var registrationId int the ID of the registration being edited */
	var $registrationId;

	/**
	 * Constructor
	 * @param registrationId int leave as default for new registration
	 */

	/**
	 * PHP 8 compatibility: forward to legacy PHP4-style constructor.
	 */
	function __construct() {
		$args = func_get_args();
		call_user_func_array(array($this, 'RegistrationForm'), $args);
	}
	function RegistrationForm($registrationId = null, $userId = null) {

		$this->registrationId = isset($registrationId) ? (int) $registrationId : null;
		$this->userId = isset($userId) ? (int) $userId : null;
		$schedConf =& Request::getSchedConf();

		parent::Form('registration/registrationForm.tpl');

		// User is provided and valid
		$this->addCheck(new FormValidator($this, "userId", "required", "manager.registration.form.userIdRequired"));
		$this->addCheck(new FormValidatorCustom($this, "userId", "required", "manager.registration.form.userIdValid", function($userId, $form, $userDao) { return $userDao->userExistsById($userId); }, array(&$this, DAORegistry::getDAO("UserDAO"))));

		// Ensure that user does not already have a registration for this scheduled conference
		if ($this->registrationId == null) {
			$this->addCheck(new FormValidatorCustom($this, "userId", "required", "manager.registration.form.registrationExists", array(DAORegistry::getDAO("RegistrationDAO"), "registrationExistsByUser"), array($schedConf->getId()), true));
		} else {
			$this->addCheck(new FormValidatorCustom($this, "userId", "required", "manager.registration.form.registrationExists", function($userId, $schedConfId, $registrationId, $registrationDao) { $checkId = $registrationDao->getRegistrationIdByUser($userId, $schedConfId); return ($checkId == 0 || $checkId == $registrationId) ? true : false; }, array($schedConf->getId(), $this->registrationId, DAORegistry::getDAO("RegistrationDAO"))));
		}

		// Registration type is provided and valid
		// Registration type is provided and valid
		if ($this->registrationId == null) {
			$this->addCheck(new FormValidatorCustom($this, "typeId", "required", "manager.registration.form.typeIdValid", function($typeId, $schedConfId, $registrationTypeDao) { return $registrationTypeDao->registrationTypeExistsByTypeId($typeId, $schedConfId); }, array($schedConf->getId(), DAORegistry::getDAO("RegistrationTypeDAO"))));
		}
		if ($this->registrationId == null) {
			$this->addCheck(new FormValidatorCustom($this, "specialCode", "optional", "manager.registration.form.specialCodeValid", function($specialCode, $schedConfId, $registrationTypeDao) { return $registrationTypeDao->anyRegistrationTypeExistsBySpecialCode($specialCode, $schedConfId); }, array($schedConf->getId(), DAORegistry::getDAO("RegistrationTypeDAO"))));
		}
		if ($this->registrationId == null) {
			$this->addCheck(new FormValidatorCustom($this, "domain", "optional", "manager.registration.form.domainValid", function($domain, $schedConfId, $registrationDao) { return $registrationDao->getRegistrationIdByDomain($domain, $schedConfId) == 0; }, array($schedConf->getId(), DAORegistry::getDAO("RegistrationDAO"))));
		}
		$this->addCheck(new FormValidatorPost($this));
		$this->addCheck(new FormValidatorRegExp($this, "ipRange", "optional", "manager.registration.form.ipRangeValid", "/^((([0-9]|[1-9][0-9]|[1][0-9]{2}|[2][0-5]{2}|[" . REGISTRATION_IP_RANGE_WILDCARD . "])([.]([0-9]|[1-9][0-9]|[1][0-9]{2}|[2][0-5]{2}|[" . REGISTRATION_IP_RANGE_WILDCARD . "])){3}((s)*[" . REGISTRATION_IP_RANGE_RANGE . "](s)*([0-9]|[1-9][0-9]|[1][0-9]{2}|[2][0-5]{2}|[" . REGISTRATION_IP_RANGE_WILDCARD . "])([.]([0-9]|[1-9][0-9]|[1][0-9]{2}|[2][0-5]{2}|[" . REGISTRATION_IP_RANGE_WILDCARD . "])){3}){0,1})|(([0-9]|[1-9][0-9]|[1][0-9]{2}|[2][0-5]{2})([.]([0-9]|[1-9][0-9]|[1][0-9]{2}|[2][0-5]{2})){3}([/](([3][0-2]{0,1})|([1-2]{0,1}[0-9]))))))/"));
		$this->addCheck(new FormValidatorInSet($this, "notifyEmail", "optional", "manager.registration.form.notifyEmailValid", array("1")));
		$this->addCheck(new FormValidatorInSet($this, "notifyPaymentEmail", "optional", "manager.registration.form.notifyEmailValid", array("1")));

		$this->addCheck(new FormValidatorPost($this));
	}

	/**
	 * Display the form.
	 */
	function display() {
		$templateMgr =& TemplateManager::getManager();
		$schedConf =& Request::getSchedConf();

		$templateMgr->assign('registrationId', $this->registrationId);
		$templateMgr->assign('yearOffsetPast', REGISTRATION_YEAR_OFFSET_PAST);
		$templateMgr->assign('yearOffsetFuture', REGISTRATION_YEAR_OFFSET_FUTURE);

		$userDao =& DAORegistry::getDAO('UserDAO');
		$user =& $userDao->getUser(isset($this->userId)?$this->userId:$this->getData('userId'));

		$templateMgr->assign_by_ref('user', $user);

		$registrationTypeDao =& DAORegistry::getDAO('RegistrationTypeDAO');
		$registrationTypes =& $registrationTypeDao->getRegistrationTypesBySchedConfId($schedConf->getId());
		$templateMgr->assign('registrationTypes', $registrationTypes);

		$registrationOptionDao =& DAORegistry::getDAO('RegistrationOptionDAO');
		$registrationOptions =& $registrationOptionDao->getRegistrationOptionsBySchedConfId($schedConf->getId());
		$registrationOptionsArray =& $registrationOptions->toArray();
		$templateMgr->assign('registrationOptions', $registrationOptionsArray);

		$templateMgr->assign('helpTopicId', 'conference.currentConferences.registration');

		parent::display();
	}

	/**
	 * Initialize form data from current registration.
	 */
	function initData() {	
		if (isset($this->registrationId)) {
			$registrationOptionDao =& DAORegistry::getDAO('RegistrationOptionDAO');
			$registrationDao =& DAORegistry::getDAO('RegistrationDAO');
			$registration =& $registrationDao->getRegistration($this->registrationId);

			if ($registration != null) {
				$this->_data = array(
					'userId' => $registration->getUserId(),
					'typeId' => $registration->getTypeId(),
					'membership' => $registration->getMembership(),
					'domain' => $registration->getDomain(),
					'ipRange' => $registration->getIPRange(),
					'specialRequests' => $registration->getSpecialRequests(),
					'datePaid' => $registration->getDatePaid(),
					'registrationOptionIds' => $registrationOptionDao->getRegistrationOptions($this->registrationId)
				);

			} else {
				$this->registrationId = null;
			}
		}
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array('userId', 'typeId', 'membership', 'domain', 'ipRange', 'notifyEmail', 'notifyPaymentEmail', 'specialRequests', 'datePaid', 'registrationOptionIds'));

		$this->_data['datePaid'] = Request::getUserVar('paid')?Request::getUserDateVar('datePaid'):null;

		// If registration type requires it, membership is provided
		$registrationTypeDao =& DAORegistry::getDAO('RegistrationTypeDAO');
		$registrationType =& $registrationTypeDao->getRegistrationType($this->getData('typeId'));
		$needMembership = $registrationType->getMembership();

		if ($needMembership) { 
			$this->addCheck(new FormValidator($this, "membership", "required", "manager.registration.form.membershipRequired"));
		}

		// If registration type requires it, domain and/or IP range is provided
		$isInstitutional = $registrationTypeDao->getRegistrationTypeInstitutional($this->getData('typeId'));
		$isOnline = $registrationType->getAccess() != REGISTRATION_TYPE_ACCESS_PHYSICAL ? true : false;

		if ($isInstitutional && $isOnline) { 
		if ($isInstitutional && $isOnline) {
			$this->addCheck(new FormValidatorCustom($this, "domain", "required", "manager.registration.form.domainIPRangeRequired", function($domain, $ipRange) { return !empty($domain) || !empty($ipRange); }, array($this->getData("domain"), $this->getData("ipRange"))));
		}

		// If notify email is requested, ensure registration contact name and email exist.
		if ($this->_data["notifyEmail"] == 1) {
			$this->addCheck(new FormValidatorCustom($this, "notifyEmail", "required", "manager.registration.form.registrationContactRequired", function($dummy, $schedConfId) { $schedConfSettingsDao =& DAORegistry::getDAO("SchedConfSettingsDAO"); $registrationName = $schedConfSettingsDao->getSetting($schedConfId, "registrationName"); $registrationEmail = $schedConfSettingsDao->getSetting($schedConfId, "registrationEmail"); return $registrationName != "" && $registrationEmail != "" ? true : false; }, array($schedConf->getId())));
		}
		if ($this->_data["notifyPaymentEmail"] == 1) {
			$this->addCheck(new FormValidatorCustom($this, "notifyPaymentEmail", "required", "manager.registration.form.registrationContactRequired", function($dummy, $schedConfId) { $schedConfSettingsDao =& DAORegistry::getDAO("SchedConfSettingsDAO"); $registrationName = $schedConfSettingsDao->getSetting($schedConfId, "registrationName"); $registrationEmail = $schedConfSettingsDao->getSetting($schedConfId, "registrationEmail"); return $registrationName != "" && $registrationEmail != "" ? true : false; }, array($schedConf->getId())));
		}
	}
	}
	/**
	 * Save registration.
	 */
	function execute() {

		if (!isset($registration)) {
			$registration = new Registration();
			$registration->setDateRegistered(time());
		}

		$registration->setSchedConfId($schedConf->getId());
		$registration->setUserId($this->getData('userId'));
		$registration->setTypeId($this->getData('typeId'));
		$registration->setMembership($this->getData('membership') ? $this->getData('membership') : null);
		$registration->setDomain($this->getData('domain') ? $this->getData('domain') : null);
		$registration->setIPRange($this->getData('ipRange') ? $this->getData('ipRange') : null);
		$registration->setSpecialRequests($this->getData('specialRequests') ? $this->getData('specialRequests') : null);

		// Send an email to the registrant informing them that their payment was received
		if ($this->getData('notifyPaymentEmail')) {
			$userDao =& DAORegistry::getDAO('UserDAO');

			$schedConfName = $schedConf->getSchedConfTitle();
			$schedConfId = $schedConf->getId();
			$user =& $userDao->getUser($this->getData('userId'));

			list($registrationEmail, $registrationName, $registrationContactSignature) = $this->getRegistrationContactInformation($schedConfId);

			$paramArray = array(
				'registrantName' => $user->getFullName(),
				'schedConfName' => $schedConfName,
				'registrationContactSignature' => $registrationContactSignature 
			);

			import('mail.MailTemplate');
			$mail = new MailTemplate('MANUAL_PAYMENT_RECEIVED');
			$mail->setFrom($registrationEmail, $registrationName);
			$mail->assignParams($paramArray);
			$mail->addRecipient($user->getEmail(), $user->getFullName());
			$mail->send();
		}
		
		$registration->setDatePaid($this->getData('datePaid'));

		// Update or insert registration
		if ($registration->getId() != null) {
			$registrationDao->updateRegistration($registration);
		} else {
			$registrationDao->insertRegistration($registration);
		}

		$registrationOptionDao =& DAORegistry::getDAO('RegistrationOptionDAO');
		$registrationOptions =& $registrationOptionDao->getRegistrationOptionsBySchedConfId($schedConf->getId());
		$registrationOptionIds = (array) $this->getData('registrationOptionIds');
		$registrationOptionDao->deleteRegistrationOptionAssocByRegistrationId($this->registrationId);

		while ($registrationOption =& $registrationOptions->next()) {
			$optionId = (int) $registrationOption->getOptionId();
			if (in_array($optionId, $registrationOptionIds)) {
				$registrationOptionDao->insertRegistrationOptionAssoc($this->registrationId, $registrationOption->getOptionId());
			}
			unset($registrationOption);
		}

		if ($this->getData('notifyEmail')) {
			// Send user registration notification email
			$userDao =& DAORegistry::getDAO('UserDAO');
			$registrationTypeDao =& DAORegistry::getDAO('RegistrationTypeDAO');

			$schedConfName = $schedConf->getSchedConfTitle();
			$schedConfId = $schedConf->getId();
			$user =& $userDao->getUser($this->getData('userId'));
			$registrationType =& $registrationTypeDao->getRegistrationType($this->getData('typeId'));

			list($registrationEmail, $registrationName, $registrationContactSignature) = $this->getRegistrationContactInformation($schedConfId);

			$paramArray = array(
				'registrantName' => $user->getFullName(),
				'schedConfName' => $schedConfName,
				'registrationType' => $registrationType->getSummaryString(),
				'username' => $user->getUsername(),
				'registrationContactSignature' => $registrationContactSignature 
			);

			import('mail.MailTemplate');
			$mail = new MailTemplate('REGISTRATION_NOTIFY', null, null, null, null, false);
			$mail->setFrom($registrationEmail, $registrationName);
			$mail->assignParams($paramArray);
			$mail->addRecipient($user->getEmail(), $user->getFullName());
			$mail->send();
		}
	}
	
	/**
	 * Get the scheduled conference's contact information
	 * @param $schedConfId int
	 * @return array
	 */
	function getRegistrationContactInformation($schedConfId) {
		$schedConfSettingsDao =& DAORegistry::getDAO('SchedConfSettingsDAO');
		
		$registrationName = $schedConfSettingsDao->getSetting($schedConfId, 'registrationName');
		$registrationEmail = $schedConfSettingsDao->getSetting($schedConfId, 'registrationEmail');
		$registrationPhone = $schedConfSettingsDao->getSetting($schedConfId, 'registrationPhone');
		$registrationFax = $schedConfSettingsDao->getSetting($schedConfId, 'registrationFax');
		$registrationMailingAddress = $schedConfSettingsDao->getSetting($schedConfId, 'registrationMailingAddress');
		$registrationContactSignature = $registrationName;

		if ($registrationMailingAddress != '') $registrationContactSignature .= "n" . $registrationMailingAddress;
		if ($registrationPhone != '') $registrationContactSignature .= "n" . AppLocale::Translate('user.phone') . ': ' . $registrationPhone;
		if ($registrationFax != '')	$registrationContactSignature .= "n" . AppLocale::Translate('user.fax') . ': ' . $registrationFax;

		$registrationContactSignature .= "n" . AppLocale::Translate('user.email') . ': ' . $registrationEmail;
		
		return array($registrationEmail, $registrationName, $registrationContactSignature);
	}

}

?>