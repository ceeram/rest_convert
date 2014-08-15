<?php
App::uses('Shell', 'Console');
App::uses('Controller', 'Controller');
App::uses('Folder', 'Utility');

Configure::write('Parsers.bbcode', array(
		'name' => 'BBCode',
		'class' => array('HtmlBbcodeParser', 'RestConvert.Parser')
));
Configure::write('Parsers.doc_markdown', array(
		'name' => 'Markdown',
		'class' => array('RestMarkdownParser', 'RestConvert.Parser')
));
Configure::write('Parsers.html', array(
		'name' => 'Html',
		'class' => array('RestHtmlParser', 'RestConvert.Parser')
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

	protected $_basePath = APP;

	public function single() {
		$id = null;
		if (!empty($this->args)) {
			$id = $this->args[0];
		}
		$this->_basePath($this->_base());

		$options = $this->_options();
		$this->_folders($options['subdir']);

		while (!$this->Article->exists($id)) {
			$id = $this->in('Article id: ');
		}
		$this->_generate($id, $options['subdir']);
	}

/**
 * Generate .rst files for all articles
 *
 */
	public function generate() {
		$this->_basePath($this->_base());

		$options = $this->_options();
		$this->_folders($options['subdir']);

		$categories = $this->_categoryMap();
		foreach ($categories as $id => $path) {
			$this->hr();
			$this->out('Starting to generate articles in: '. $path);
			$this->hr();
			$this->_generateCategory($id, $options);
		}
	}

/**
 *
 * @return type
 */
	protected function _options() {
		$lang = !empty($this->params['lang']) ? $this->params['lang'] : 'eng';
		$subdir = !empty($this->params['subdir']) ? $this->params['subdir'] : 'src';

		return array('lang' => $lang, 'subdir' => $subdir);
	}

/**
 * User input with absolute path, or relative to APP
 *
 * @return string Absolute path to write
 */
	protected function _base() {
		$path = null;
		while (!is_writeable($path)) {
			$path = $this->in('Please provide path to write location', null, $this->_basePath);
			if (strpos($path, DS) !== 0) {
				$path = $this->_basePath . $path;
			}
			if (strpos($path, DS, strlen($path) -1) === false) {
				$path .= DS;
			}
		}
		$ok = $this->in('Write location: ' . $path, array('yes' => 'y', 'abort' => 'a'), 'y');
		if ($ok == 'a') {
			$this->out('Aborted');
			$this->_stop();
		}
		return $path;
	}

/**
 *
 * @param string $path
 * @return string Absolute path
 */
	protected function _basepath($path = null) {
		if ($path) {
			$this->_basePath = $path;
		}
		return $this->_basePath;
	}

/**
 * Create folder structures for all languages and categories
 *
 */
	protected function _folders($subdir = 'src') {
		$this->_categoryMap();
		$this->_folderStructure($subdir);
	}

/**
 * Create category folder structure for a single language
 *
 * @param string $language
 */
	protected function _folderStructure($subdir = 'src') {
		$base = $this->_basePath() . $subdir . DS;
		$Folder = new Folder($base, true);
//		$map = array_unique($this->_categoryMap);
//		foreach($map as $path) {
//			$Folder->create($base . $path);
//		}
	}

/**
 * Create map of category id to filepath
 *
 * @return array
 */
	protected function _categoryMap() {
		$result = $this->Article->Category->find('list');
		foreach ($result as $id => $path) {
			if ($path !== 'News') {
				$path = 'articles';
			}
			$result[$id] = strtolower($path);
		}
		return $this->_categoryMap = $result;
	}

/**
 * Convert all articles for a category to .rst
 *
 * @param type $id Category id
 */
	protected function _generateCategory($id, $options) {
		$articles = $this->Article->find('list', array(
			'conditions' => array(
				'Article.category_id' => $id,
				'Article.parent_id' => null,
				'Article.lang' => $options['lang'],
				'Article.published' => 1,
				'Article.deleted' => 0,
				'NOT' => array('Article.published_date IS NULL')
			),
			'fields' => array('Article.id', 'Article.id')
		));
		foreach ($articles as $id) {
			$this->_generate($id, $options['subdir']);
		}
	}

/**
 * Generate .rst file for an article
 *
 * @param integer $id
 */
	protected function _generate($id = null, $subdir = 'src') {
		try {
			$article = $this->Article->view($id);
		} catch (Exception $e) {
			$this->out('skipping article: ' . $id);
			return;
		}

		$this->out('Generating article: ' . $article['Article']['title']);
		$this->_viewVars = compact('article');
		$html = $this->_render();
		if (mb_detect_encoding($html, 'UTF-8', true) === false) {
			echo "NOT UTF8  !!!!!!!!!!\n";
			return false;
		}
		$tmpfile = TMP . 'view_' . $id . '.html';
		$this->_write($tmpfile, $html);
		$restfile = $this->_fullpath($article, $subdir);
		exec("html2rest $tmpfile > $restfile");
		$this->_delete($tmpfile);
		$contents = file_get_contents($restfile);

		if (strlen($contents) == 0) {
			unlink($restfile);
			echo "CONTENT LENGTH IS ZERO!!!!!!!!!!\n";
			return false;
		}
		$contents = str_replace("\n\n\n", "\n\n", $contents);
		$meta = "\n.. meta\n\n::\n";
		$contents = str_replace($meta, ".. meta::", $contents);
		$contents = ltrim($contents);
		file_put_contents($restfile, $contents);
	}

/**
 * Build and set all the view properties needed to render the layout and template.
 *
 * @return array The rendered template wrapped in layout.
 */
	protected function _render() {
		//$Controller = new Controller(new CakeRequest());
		$View = new View(null);
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
	protected function _fullpath($article, $subdir) {
		$base = $this->_basePath() . $subdir . DS;// . $this->_categoryMap[$article['Article']['category_id']] . DS;
		$date = $this->_datedirs($article['Article']['created'], $base);
		$file = Inflector::slug($article['Article']['title'], '-') . '.rst';
		return $base . $date . $file;
	}

/**
 *
 * @param type $created
 * @param type $base
 * @return string
 */
	protected function _datedirs($created, $base) {
		$date = explode(' ', $created, 2);
		$parts = explode('-', $date[0], 3);
		$path = implode(DS, $parts) . DS;
		$Folder = new Folder($base, true);
		$Folder->create($base . $path);
		return $path;
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