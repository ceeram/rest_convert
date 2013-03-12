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

App::uses('MarkdownParser', 'MarkupParsers.Lib/Parser');

class RestMarkdownParser extends MarkdownParser {


/**
 * code block assist
 *
 * @return string
 */
	protected function _codeBlockHelper($matches) {
		if ($this->_indentedCode) {
			$matches[2] = $this->_outdent($matches[2]);
		}
		return "\n\n" . $this->_makePlaceHolder(
			'<pre>' . htmlspecialchars(trim($matches[2])) . '</pre>'
		) . "\n\n";
	}

}