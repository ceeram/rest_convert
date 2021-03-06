<?php
/**
 * Copyright 2010-2012, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010-2012, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('BbcodeParser', 'MarkupParsers.Lib/Parser');

/**
 * Bbcode Parser
 *
 * @package markup_parsers
 * @subpackage markup_parsers.libs
 */
class HtmlBbcodeParser extends BbcodeParser {

	function __highlightCode($result) {
		return '<pre>' . htmlspecialchars($result) . '</pre>';
	}

}
