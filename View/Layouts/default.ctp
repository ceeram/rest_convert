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
			'abstract' => 'cakephp php development programming framework mvc'
		), (array) Configure::read('App.metatags'));
	?>
</head>
<body>
	<div id="content">
		<?php echo $this->fetch('content'); ?>
	</div>
</body>
.. meta<pre><?php foreach ($metatags as $name => $content) : ?>&nbsp;&nbsp;&nbsp;&nbsp;:<?php echo $name;?>: <?php echo $content; ?><br><?php endforeach; ?></pre>
</html>