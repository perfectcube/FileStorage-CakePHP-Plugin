<?php
/**
 * FileStorage
 *
 * @author Florian Krämer
 * @copyright 2012 Florian Krämer
 * @license MIT
 */
class FileStorageController extends FileStorageAppController {
	
	public $uses = array('FileStorage.FileStorage', 'FileStorage.ImageStorage', 'FileStorage.VideoStorage');
	
	public $helpers = array('FileStorage.Image');
	
	public function browser() {
		if(isset($this->request->query['CKEditor'])) {
			$this->layout = false;
			$this->view = 'ckebrowser';
		}
		
		//Debugging
		$this->layout = false;
		$this->view = 'ckebrowser';
		
		
		$this->set('media', $this->FileStorage->find('all'));
	}
	
	public function upload() {
		
		if (!$this->request->is('get')) {
			$data = $this->request->data;
			$data[$this->ImageStorage->alias]['adapter'] = 'S3Storage';
			$model = $this->_detectModelByFileType($data['File']['file']['type']);
			if($model) {
				$data['File']['model'] = $this->$model->alias;
				$data['File']['adapter'] = 'S3Storage';
				if ($data = $this->$model->save(array($this->$model->alias => $data['File']))) {
					$this->response->statusCode(200);
					$message = "Upload Successful";
				}else {
					$this->response->statusCode(500);
					$message = "Upload Failed";
				}
			}
			else {
				$this->response->statusCode(415);
				$message = "Invalid File Type";
			}
			if($this->request->is('ajax')) {
				$this->layout = false;
				$this->set('media', $this->FileStorage->find('all'));
				$this->view = 'media-list';
			}else {
				$this->Session->setFlash($message);
			}
			
			
		}
	}
	
}