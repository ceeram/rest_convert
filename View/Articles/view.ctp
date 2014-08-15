<?php
/**
 * Copyright 2010, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
?><?php

	$tags = isset($article['Article']['tags']) ? $article['Article']['tags'] : 'cakephp article';
	$categories = isset($article['Category']['name']) ? $article['Category'] : array('name' => 'Uncategorized', 'slug' => 'uncategorized');
	if (!isset($categories[0])) {
		$categories = array($categories);
	}
	$metaTags = $tags;
	$categoryLinks = null;

	foreach ($categories as $key => $value) {
		$metaTags .= ', ' . $value['name'];
	}
	$markup = $article['Article']['markup'];
	$heading = $article['Article']['title'];
	$pages = $this->Parser->parse($article['Article']['body'], $markup);
	$intro = $this->Text->autoLinkUrls($article['Article']['intro']);
?>
<div class="row">
		<article class="columns ten <?php echo $markup ?>">
			<h1><?php echo $heading;?></h1>
			<div class="intro">
				<p>
					<?php echo $article['Article']['intro']; ?>
				</p>
			</div>
			<?php foreach ($pages as $i => $page): ?>
				<div id="content-page-<?php echo $i+1; ?>" class="body"><?php echo $page; ?></div>
			<?php endforeach; ?>
			<p class="right pages-paging">
			<?php
				if (!empty($pages) && isset($pages[1])):
					$links = array();
					foreach ($pages as $i => $page) {
						$links[] = '<span>' . $this->Html->link($i+1, array(
							'admin' => false,
							'controller' => 'articles',
							'action' => 'view',
							$article['Article']['id'],
							'#' => 'page-' . ($i+1)
						)) . '</span>';
					}
					__d('blazon', 'Page: ');
					echo implode('&nbsp;|&nbsp;', $links);
				endif;
			?>
			</p>
		</article>
		<div class="columns two">
				<?php if (!empty($pages) && isset($pages[1])): ?>
					<div class="info">
						<h4><?php echo __d('blazon', 'More'); ?></h4>
						<ul class="pages-paging">
							<?php
							foreach ($pages as $i => $page) {
								echo '<li>' . $this->Html->link(sprintf(__d('blazon', 'Page %d'), $i+1), array(
									'admin' => false,
									'controller' => 'articles',
									'action' => 'view',
									$article['Article']['id'],
									'#' => 'page-' . ($i+1)
								)) . '</li>';
							}
							?>
						</ul>
					</div>
				<?php endif; ?>


			<?php
				$author = sprintf(__d('blazon', 'By %s'), $article['User']['username']);
				$intro = $this->Parser->parse($article['Article']['intro'], $article['Article']['markup']);
				$url = Router::url(array('admin' => false, 'controller' => 'articles', 'action' => 'download_code', $article['Article']['id'], 'block', '%s'));
				$url = str_replace(Router::url('/'), '/', $url);
			?>

		</div>
</div>

<?php
	$metaTags = explode(', ', $metaTags);
	$metaTags = implode(',', array_flip(array_flip($metaTags)));

	$this->set('title_for_layout', $heading);
	Configure::write('App.metatags', array(
		'title' => $heading,
		'description' => 'CakePHP Article related to ' . $metaTags,
		'keywords' => $metaTags,
		'copyright' => 'Copyright ' . date('Y', strtotime($article['Article']['created'])) . ' ' . $article['User']['username'],
		'category' => $article['Category']['slug']
	));
?>
