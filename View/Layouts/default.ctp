<!DOCTYPE html>
<html lang="en">
<head>
	<title>
		<?php echo $title_for_layout;?> ::
	</title>
	<?php
		echo $this->Html->charset();
		echo $this->AssetCompress->css('app.css');
		$meta = array();
		$metatags = array_merge(array(
			'title' => $title_for_layout,
			'description' => 'CakePHP Articles',
			'keywords' => 'cakephp, cakephp articles, articles about cakephp, cakephp tutorial, cakephp news, cakephp code, cakephp release',
		), (array) Configure::read('App.metatags'));
	?>
</head>
<body>
	<div id="content">
		<?php echo $this->fetch('content'); ?>
	</div>
</body>
.. author:: <?php echo $article['User']['username']; ?><br>
.. categories:: <?php echo (in_array($article['Category']['slug'], array('news', 'articles'))) ? $article['Category']['slug'] : 'articles, ' . $article['Category']['slug']; ?><br>
.. tags:: <?php echo Configure::read('App.metatags.keywords'); ?><br>
</html>