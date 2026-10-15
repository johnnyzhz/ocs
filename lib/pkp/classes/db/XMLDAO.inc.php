<?php

/**
 * @file classes/db/XMLDAO.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class XMLDAO
 * @ingroup db
 *
 * @brief Operations for retrieving and modifying objects from an XML data source.
 */

// $Id$


import('xml.XMLParser');

class XMLDAO {
	/**
	 * Constructor.
	 */

	/**
	 * PHP 8 compatibility: forward to legacy PHP4-style constructor.
	 */
	function __construct() {
		$args = func_get_args();
		call_user_func_array(array($this, 'XMLDAO'), $args);
	}
	function XMLDAO() {
	}

	/**
	 * Parse an XML file and return data in an object.
	 * @see xml.XMLParser::parse()
	 */
	function &parse($file) {
		$parser = new PKPXMLParser();
		$data =& $parser->parse($file);
		$parser->destroy();
		return $data;
	}

	/**
	 * Parse an XML file with the specified handler and return data in an object.
	 * @see xml.XMLParser::parse()
	 * @param $handler reference to the handler to use with the parser.
	 */
	function &parseWithHandler($file, &$handler) {
		$parser = new PKPXMLParser();
		$parser->setHandler($handler);
		$data =& $parser->parse($file);
		$parser->destroy();
		return $data;
	}

	/**
	 * Parse an XML file and return data in an array.
	 * @see xml.XMLParser::parseStruct()
	 */
	function &parseStruct($file, $tagsToMatch = array()) {
		$parser = new PKPXMLParser();
		$data =& $parser->parseStruct($file, $tagsToMatch);
		$parser->destroy();
		return $data;
	}
}

?>
