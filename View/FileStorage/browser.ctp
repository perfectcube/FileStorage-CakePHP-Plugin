<p>Media Browser</p>
<?php debug($media); ?>
<?php foreach ($media as $m): ?>
	<?php echo $this->Image->display($m['ImageStorage']); ?>
<?php endforeach; ?>