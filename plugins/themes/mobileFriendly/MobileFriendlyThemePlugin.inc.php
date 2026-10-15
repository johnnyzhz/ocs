<?php

/**
 * @file MobileFriendlyThemePlugin.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MobileFriendlyThemePlugin
 * @ingroup plugins_themes_mobileFriendly
 *
 * @brief Classic Blue theme with responsive layout adjustments.
 */

import('classes.plugins.ThemePlugin');

class MobileFriendlyThemePlugin extends ThemePlugin {
	function getName() {
		return 'MobileFriendlyThemePlugin';
	}

	function getDisplayName() {
		return 'Mobile-Friendly Classic Blue';
	}

	function getDescription() {
		return 'Classic Blue styling with responsive layouts for phones and tablets.';
	}

	function getStylesheetFilename() {
		return 'mobile.css';
	}

	function getLocaleFilename($locale) {
		return null;
	}

	function activate(&$templateMgr) {
		$templateMgr->addStyleSheet(Request::getBaseUrl() . '/plugins/themes/classicBlue/classicBlue.css');
		parent::activate($templateMgr);

		$additionalHeadData = $templateMgr->get_template_vars('additionalHeadData');
		$customHeaders = $templateMgr->get_template_vars('metaCustomHeaders');
		$additionalHeadData = is_string($additionalHeadData) ? $additionalHeadData : '';
		$customHeaders = is_string($customHeaders) ? $customHeaders : '';

		if (!preg_match('/<meta\b[^>]*\bname\s*=\s*(["\'])viewport\1/i', $additionalHeadData . "\n" . $customHeaders)) {
			$templateMgr->assign(
				'additionalHeadData',
				$additionalHeadData . "\n<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">"
			);
		}
	}
}

?>
