<?php

/**
 * @file PaymentSettingsForm.inc.php
 *
 * Copyright (c) 2006-2009 Gunther Eysenbach, Juan Pablo Alperin, MJ Suhonos
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PaymentSettingsForm
 * @ingroup plugins
 *
 * @brief Form for conference managers to modify Payment Plugin settings
 * 
 */

import('form.Form');

class PaymentSettingsForm extends Form {
	/** @var $errors string */
	var $errors;

	/** @var $plugins array */
	var $plugins;

	/**
	 * Constructor
	 */

	/**
	 * PHP 8 compatibility: forward to legacy PHP4-style constructor.
	 */
	function __construct() {
		$args = func_get_args();
		call_user_func_array(array($this, 'PaymentSettingsForm'), $args);
	}
	function PaymentSettingsForm() {
		parent::Form('manager/paymentSettingsForm.tpl');

		// Load the plugins.
		$this->plugins =& PluginRegistry::loadCategory('paymethod');

		// Add form checks
		$this->addCheck(new FormValidatorInSet($this, 'paymentMethodPluginName', 'optional', 'manager.payment.paymentPluginInvalid', array_keys($this->plugins)));
		$this->addCheck(new FormValidatorPost($this));
	}

	/**
	 * Display the form.
	 */
	function display($request = null, $fetch = false) {
		$templateMgr =& TemplateManager::getManager($request);
		$templateMgr->assign_by_ref('paymentMethodPlugins', $this->plugins);
		return parent::display($request, $fetch);
	}

	/**
	 * Initialize form data from current group group.
	 */
	function initData() {
		$schedConf =& Request::getSchedConf();

		// Allow the current selection to supercede the stored value
		$paymentMethodPluginName = Request::getUserVar('paymentMethodPluginName');
		if (empty($paymentMethodPluginName) || !in_array($paymentMethodPluginName, array_keys($this->plugins))) {
			$paymentMethodPluginName = $schedConf->getSetting('paymentMethodPluginName');
		}

		$this->_data = array(
			'paymentMethodPluginName' => $paymentMethodPluginName
		);

		if (isset($this->plugins[$paymentMethodPluginName])) {
			$plugin =& $this->plugins[$paymentMethodPluginName];
			foreach ($plugin->getSettingsFormFieldNames() as $field) {
				$this->_data[$field] = $plugin->getSetting($schedConf->getConferenceId(), $schedConf->getId(), $field);
			}
		}
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array(
			'paymentMethodPluginName'
		));

		$paymentMethodPluginName = $this->getData('paymentMethodPluginName');
		if (isset($this->plugins[$paymentMethodPluginName])) {
			$plugin =& $this->plugins[$paymentMethodPluginName];
			$this->readUserVars($plugin->getSettingsFormFieldNames());
		}

	}

	/**
	 * Save settings 
	 */	 
	function execute() {
		$schedConf =& Request::getSchedConf();

		// Save the general settings for the form
		foreach (array('paymentMethodPluginName') as $schedConfSettingName) {
			$schedConf->updateSetting($schedConfSettingName, $this->getData($schedConfSettingName));
		}

		// Save the specific settings for the plugin
		$paymentMethodPluginName = $this->getData('paymentMethodPluginName');
		if (isset($this->plugins[$paymentMethodPluginName])) {
			$plugin =& $this->plugins[$paymentMethodPluginName];
			foreach ($plugin->getSettingsFormFieldNames() as $field) {
				$plugin->updateSetting($schedConf->getConferenceId(), $schedConf->getId(), $field, $this->getData($field));
			}
		}
	}
}

?>
