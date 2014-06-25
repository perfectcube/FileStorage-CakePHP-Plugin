<?php
App::uses('FileStorageAppController', 'Controller');
/**
 * FileStorage
 *
 * @author Florian Krämer
 * @copyright 2012 Florian Krämer
 * @license MIT
 */
class FileStorageAppController extends AppController {
	
	
	/**
	 * Simple Method for detecting what model to save to
	 * by file mime_type
	 */
	
	protected function _detectModelByFileType ($mime_type) {
		return FileStorageUtils::detectModelByFileType($mime_type);
	}
	
}