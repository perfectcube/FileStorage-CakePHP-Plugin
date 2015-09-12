I don't think this file is used, a different view is specified in the controller.


<p>Media Browser</p>

<?php foreach ($media as $m): ?>
	<?php echo $this->Image->display($m['ImageStorage']); ?>
<?php endforeach; ?>


