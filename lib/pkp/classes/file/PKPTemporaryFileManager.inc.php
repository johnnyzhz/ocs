<?php

/**
 * @file classes/file/TemporaryFileManager.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPTemporaryFileManager
 * @ingroup file
 * @see TemporaryFileDAO
 *
 * @brief Class defining operations for temporary file management.
 */

// $Id$


import('file.FileManager');

class PKPTemporaryFileManager extends FileManager {
	/**
	 * Constructor
	 */

	/**
	 * PHP 8 compatibility: forward to legacy PHP4-style constructor.
	 */
	function __construct() {
		$args = func_get_args();
		call_user_func_array(array($this, 'PKPTemporaryFileManager'), $args);
	}
	function PKPTemporaryFileManager() {
		parent::FileManager();
		$this->filesDir = Config::getVar('files', 'files_dir') . '/temp/';

		$this->_performPeriodicCleanup();
	}

	/**
	 * Retrieve file information by file ID.
	 * @return TemporaryFile
	 */
	function &getFile($fileId, $userId) {
		$temporaryFileDao =& DAORegistry::getDAO('TemporaryFileDAO');
		$temporaryFile =& $temporaryFileDao->getTemporaryFile($fileId, $userId);
		return $temporaryFile;
	}

	/**
	 * Read a file's contents.
	 * @param $output boolean output the file's contents instead of returning a string
	 * @return boolean
	 */
	function &readFile($fileId, $userId = null, $output = false) {
		$temporaryFile =& $this->getFile($fileId, $userId);

		$returner = false;
		if (isset($temporaryFile)) {
			$filePath = $this->filesDir . $temporaryFile->getFileName();
			$returner = parent::readFile($filePath, $output);
		}
		return $returner;
	}

	/**
	 * Delete a file by ID.
	 * @param $fileId int
	 */
	function deleteFile($fileId, $userId = null) {
		$temporaryFile =& $this->getFile($fileId, $userId);
		if (!isset($temporaryFile)) {
			return false;
		}

		$filePath = $this->filesDir . $temporaryFile->getFileName();
		$fileDeleted = parent::deleteFile($filePath);
		if (!$fileDeleted && file_exists($filePath)) {
			return false;
		}

		$temporaryFileDao =& DAORegistry::getDAO('TemporaryFileDAO');
		return $temporaryFileDao->deleteTemporaryFileById($fileId, $userId);
	}

	/**
	 * Download a file.
	 * @param $fileId int the file id of the file to download
	 * @param $inline print file as inline instead of attachment, optional
	 * @return boolean
	 */
	function downloadFile($fileId, $userId = null, $inline = false) {
		$temporaryFile =& $this->getFile($fileId, $userId);
		if (isset($temporaryFile)) {
			$filePath = $this->filesDir . $temporaryFile->getFileName();
			return parent::downloadFile($filePath, null, $inline);
		} else {
			return false;
		}
	}

	/**
	 * View a file inline (variant of downloadFile).
	 * @see PKPTemporaryFileManager::downloadFile
	 */
	function viewFile($fileId, $userId = null) {
		return $this->downloadFile($fileId, $userId, true);
	}

	/**
	 * Parse the file extension from a filename/path.
	 * @param $fileName string
	 * @return string
	 */
	function parseFileExtension($fileName) {
		if (!is_string($fileName) || $fileName === '') {
			return 'txt';
		}

		$fileParts = explode('.', $fileName);
		if (is_array($fileParts)) {
			$fileExtension = strtolower($fileParts[count($fileParts) - 1]);
		}

		if (!isset($fileExtension) || stripos($fileExtension, 'php') !== false || strlen($fileExtension) > 6 || !preg_match('/^\w+$/', $fileExtension)) {
			$fileExtension = 'txt';
		}

		return $fileExtension;
	}

	/**
	 * Upload the file and add it to the database.
	 * @param $fileName string index into the $_FILES array
	 * @param $userId int
	 * @return object The new TemporaryFile or false on failure
	 */
	function handleUpload($fileName, $userId) {
		// Get the file extension, then rename the file.
		$fileExtension = $this->parseFileExtension($this->getUploadedFileName($fileName));

		if (!$this->fileExists($this->filesDir, 'dir')) {
			// Try to create destination directory
			if (!$this->mkdirtree($this->filesDir) && !$this->fileExists($this->filesDir, 'dir')) {
				return false;
			}
		}

		$tempFilePath = tempnam($this->filesDir, $fileExtension);
		if ($tempFilePath === false) {
			return false;
		}
		$newFileName = basename($tempFilePath);

		$uploadedFileType = $this->getUploadedFileType($fileName);
		if ($this->uploadFile($fileName, $this->filesDir . $newFileName)) {
			$temporaryFileDao =& DAORegistry::getDAO('TemporaryFileDAO');
			$temporaryFile = new TemporaryFile();

			$temporaryFile->setUserId($userId);
			$temporaryFile->setFileName($newFileName);
			$temporaryFile->setFileType($uploadedFileType);
			$temporaryFile->setFileSize($_FILES[$fileName]['size']);
			$temporaryFile->setOriginalFileName(TemporaryFileManager::truncateFileName($_FILES[$fileName]['name'], 127));
			$temporaryFile->setDateUploaded(Core::getCurrentDate());

			$temporaryFileDao->insertTemporaryFile($temporaryFile);

			return $temporaryFile;

		} else {
			parent::deleteFile($tempFilePath);
			return false;
		}
	}

	/**
	 * Perform periodic cleanup tasks. This is used to occasionally
	 * remove expired temporary files.
	 */
	function _performPeriodicCleanup() {
		if (time() % 100 == 0) {
			$temporaryFileDao =& DAORegistry::getDAO('TemporaryFileDAO');
			$expiredFiles = $temporaryFileDao->getExpiredFiles();
			foreach ($expiredFiles as $expiredFile) {
				$this->deleteFile($expiredFile->getId(), $expiredFile->getUserId());
			}
		}
	}
}

?>
