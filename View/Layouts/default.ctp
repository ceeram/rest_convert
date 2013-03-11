<!DOCTYPE html>
<html lang="en">
<head>
	<title>
		<?php echo $title_for_layout;?> ::
	</title>
	<?php
		echo $this->Html->charset();
		echo $this->AssetCompress->css('app.css');
	?>
</head>
<body>
	<div id="content">
		<?php echo $this->fetch('content'); ?>
	</div>
</body>
</html>