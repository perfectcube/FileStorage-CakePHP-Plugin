<?php foreach ($media as $m): ?>
<li>
  	<?php echo $this->Image->display($m['FileStorage'], null, array('width' => 100, 'height' => 100)); ?>
  	<a href="javascript:void(0);" data-url="<?php echo $this->Image->imageUrl($m['FileStorage']); ?>" class="select-media button tiny expand">Select</a>
</li>
<?php endforeach; ?>