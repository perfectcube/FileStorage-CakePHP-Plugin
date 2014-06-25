<?php foreach ($media as $m): ?>
<li data-equalizer-watch class="media-item">
<div class="rel-con">
<div class="rel-item">

<?php /** For Images */ if($this->Image->isImage($m['FileStorage'])): ?>
  	<?php echo $this->Image->display($m['FileStorage'], null, array('width' => 100, 'height' => 100)); ?>
 <?php endif; ?>


<?php /** For Documents */ if($m['FileStorage']['model'] == "FileStorage"): ?>
	<?php
		switch ($m['FileStorage']['mime_type']) {
			case "application/pdf":
				$icon = "pdf-icon.png";
				break;
			default: 
				$icon = "default-icon.png";
		}
	?>
  	<img src="/FileStorage/img/<?php echo $icon; ?>" />
  <?php endif; ?>
  
</div>
	<p style="font-size: .8em; text-align:center; padding: 8px 0px; margin:0;">
  	<?php echo $m['FileStorage']['filename']; ?>
  	</p>
  	<div class="bottom">
  	<a href="javascript:void(0);" data-url="<?php echo $this->Image->imageUrl($m['FileStorage']); ?>" class="select-media tiny expand button split">Select<span data-dropdown="media-drop-<?php echo $m['FileStorage']['id']; ?>"></span></a>
	<ul id="media-drop-<?php echo $m['FileStorage']['id']; ?>" data-dropdown-content class="f-dropdown">
	  <li><a href="javascript:void(0);" data-id="<?php echo $m['FileStorage']['id']; ?>" class="remove-media">Delete</a></li>
	</ul>
	</div>
</div>
</li>
<?php endforeach; ?>