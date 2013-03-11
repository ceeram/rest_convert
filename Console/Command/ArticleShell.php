<?php
App::uses('Shell', 'Console');
App::uses('Controller', 'Controller');
App::uses('Folder', 'Utility');

Configure::write('Parsers.bbcode', array(
		'name' => 'BBCode',
		'class' => array('HtmlBbcodeParser', 'RestConvert.Parser')
));

class ArticleShell extends Shell {

	public $uses = array('Article');

	protected $_helpers = array(
		'Session', 'Html', 'Form',
		'AssetCompress.AssetCompress',
		'Problems.Problems',
		'I18n.I18n',
		'Js' => 'Jquery',
		'Utils.Gravatar',
		'Time',
		'Text',
		'Tags.TagCloud',
		'Utility',
		'Form' => array('className' => 'Foundation.FoundationForm'),
		'MarkupParsers.Parser',
	);

	protected $_viewvars = array();

	public $_categoryMap = array();

/**
 * Generate .rst files for all articles
 *
 */
	public function generate() {
		$this->_folders();
		$categories = $this->_categoryMap();
		foreach ($categories as $id => $path) {
			$this->hr();
			$this->out('Starting to generate articles in: '. $path);
			$this->hr();
			$this->_generateCategory($id);
		}
	}

/**
 * Create folder structures for all languages and categories
 *
 */
	protected function _folders() {
		$this->_categoryMap();
		foreach($this->_getLanguages() as $language) {
			$this->_folderStructure($language);
		}
	}

/**
 * Create category folder structure for a single language
 *
 * @param string $language
 */
	protected function _folderStructure($language) {
		$base = TMP . 'static' . DS . $language . DS;
		$Folder = new Folder($base, true);
		foreach($this->_categoryMap as $path) {
			$Folder->create($base . $path);
		}
	}

/**
 * Get a list of all languages used by articles
 *
 * @return array
 */
	protected function _getLanguages() {
		return array_keys($this->Article->find('list', array(
			'fields' => array('Article.lang', 'Article.lang', 'Article.lang'),
			'conditions' => array('Article.parent_id' => null)
		)));
	}

/**
 * Create map of category id to filepath
 *
 * @return array
 */
	protected function _categoryMap() {
		$this->Article->Category->virtualFields['path'] = "CONCAT_WS('/', Parent.slug, Category.slug)";

		$result = $this->_categoryMap = $this->Article->Category->find('list', array(
			'fields' => array('Category.id', 'path'),
			'joins' => array(
				array(
					'table' => 'categories',
					'alias' => 'Parent',
					'type' => 'left',
					'conditions' => array('Parent.id = Category.category_id')
				)
			)
		));
		unset($this->Article->Category->virtualFields['path']);
		return $result;
	}

/**
 * Convert all articles for a category to .rst
 *
 * @param type $id Category id
 */
	protected function _generateCategory($id) {
		$articles = $this->Article->find('list', array(
			'conditions' => array(
				'Article.category_id' => $id,
				'Article.parent_id' => null
			),
			'fields' => array('Article.id', 'Article.id')
		));
		foreach ($articles as $id) {
			$this->_generate($id);
		}
	}

/**
 * Generate .rst file for an article
 *
 * @param integer $id
 */
	protected function _generate($id = null) {
		$article = $this->Article->view($id);
		$this->out('Generating article: ' . $article['Article']['title']);
		$this->_viewVars = compact('article');
		$html = $this->_render();
		$tmpfile = TMP . 'view_' . $id . '.html';
		$this->_write($tmpfile, $html);
		$restfile = $this->_fullpath($article);
		exec("html2rest $tmpfile > $restfile");
		$contents = file_get_contents($restfile);
		$contents = str_replace("\n\n\n", "\n\n", $contents);
		file_put_contents($restfile, $contents);
		$this->_delete($tmpfile);
	}

/**
 * Build and set all the view properties needed to render the layout and template.
 *
 * @return array The rendered template wrapped in layout.
 */
	protected function _render() {
		$Controller = new Controller(new CakeRequest());
		$View = new View($Controller);
		$View->viewVars = $this->_viewVars;
		$View->helpers = $this->_helpers;
		$View->viewPath = 'Articles';
		$View->view = 'RestConvert.view';
		$View->layout = 'RestConvert.default';
		return $View->render();
	}

/**
 * Generate full path for article
 *
 * @param string $article
 * @return string
 */
	protected function _fullpath($article) {
		$path = $article['Article']['lang'] . DS . $this->_categoryMap[$article['Article']['category_id']] . DS;
		return TMP . 'static' . DS . $path . Inflector::slug($article['Article']['title'], '-') . '.rst';
	}

/**
 * Writes output to file
 *
 * @param srting $destination Absolute file path to write to
 * @param boolean $create Create file if it does not exist (if true)
 * @return boolean
 */
	protected function _write($destination, $create = true, $html = null) {
		if (!$html) {
			$html = $this->_render();
		}
		$File = new File($destination, $create);
		return $File->write($html) && $File->close();
	}

/**
 * Writes output to file
 *
 * @param string $destination Absolute file path to write to
 * @return boolean
 */
	protected function _delete($destination) {
		$File = new File($destination);
		return $File->delete();
	}
}