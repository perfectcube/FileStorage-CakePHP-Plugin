<?php
/**
 * AttachmentHelper
 *
 * @author Florian Krämer
 * @copyright 2012 Florian Krämer
 * @license MIT
 */
class AttachmentHelper extends AppHelper {

/**
 * Helpers
 *
 * @var array
 */
	public $helpers = array(
		'Html',
		'FileStorage.ImageStorage' => array('prefix' => 'https://s3.amazonaws.com/isby'),
		'FileStorage.VideoStorage' => array('prefix' => 'https://s3.amazonaws.com/isby'),
		'FileStorage.FileStorage' => array('prefix' => 'https://s3.amazonaws.com/isby')
	);
	
	public $prefix = 'https://s3.amazonaws.com/isby';

/**
 * Url method
 * 
 */	
	public function url($file = null, $options = array()) {
		if (!empty($file)) {
			return $this->prefix . $file['path'] . $file['filename'];
		} else {
			return $this->fallbackImageUrl($options);
		}
	}

/**
 * Generates an image url based on the image record data and the used Gaufrette adapter to store it
 *
 * @param array $image FileStorage array record or whatever else table that matches this helpers needs without the model, we just want the record fields
 * @param string $version Image version string
 * @param array $options HtmlHelper::image(), 2nd arg options array
 * @return string
 */
	public function display($file, $options = array()) {
		$className = FileStorageUtils::detectModelByFileType($file['mime_type']);
		if (class_exists($className)) {
			// eg. $this->ImageStorage->display();
			return $this->$className->display($file, $options);
		}
		return $this->ImageStorage->fallback($options);
	}

/**
 * Turns the windows \ into / so that the path can be used in an url
 *
 * @param string $path
 * @return string
 */
	// public function normalizePath($path) {
		// $path = str_replace("//", "/", $path);
		// $path = str_replace("\\", "/", $path);
		// $path = str_replace("http:/", "http://", $path);
		// return $path;
	// }
// 	
	// public function isImage($data) {
		// if($model = FileStorageUtils::detectModelByFileType($data['mime_type'])) {
			// if($model == "ImageStorage") {
				// return true;
			// }
		// }
		// return false;
	// }
	

}