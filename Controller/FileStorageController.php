<?php
/**
 * FileStorage
 *
 * @author Florian Kr�mer
 * @copyright 2012 Florian Kr�mer
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

		$params = array();
		if(isset($this->request->query['type'])) {
			switch($this->request->query['type']) {
				case "all":
					$params['conditions'] = array();
					break;
				case "Image":
				case "Video":
				case "File":
					$params['conditions'] = array('model' => $this->request->query['type']."Storage");
					break;
			}
		}
		$userId = CakeSession::read('Auth.User.id');
		$params['conditions'][] = array('FileStorage.creator_id' => $userId);

		if($this->request->is('ajax')) {
			$this->view = 'media-list';
		}
		$this->set('media', $this->FileStorage->find('all', $params));
	}

	public function delete($id) {
		if(!$this->request->is('get')) {
			$media = $this->FileStorage->find('first', array('conditions' => array('FileStorage.id' => $id)));
			if($media) {
				//Checks the model saved with the record.
				//falls back to base model if not a real object
				$model = $media[$this->FileStorage->alias]['model'];
				if(!is_object($this->$model)) {
					$model = $this->_detectModelByFileType($media[$this->FileStorage->alias]['mime_type']);
				}
				$this->$model->id = $id;
				if($this->$model->delete()) {
					$message = "File Deleted!";
				}else {
					$this->response->statusCode(500);
					$message = "File could not be deleted";
				}
			}else {
				$this->response->statusCode(404);
				$message = "File could not be found";
			}
		}else {
			$message = "Bad Request";
			$this->response->statusCode(400);
		}

		if($this->request->is('ajax')) {
			$this->layout = false;
			$this->set('media', $this->FileStorage->find('all'));
			$this->view = 'media-list';
		}else {
			$this->Session->setFlash($message);
		}
	}

	public function upload() {

		if (!$this->request->is('get')) {
			$data = $this->request->data;
			$data[$this->ImageStorage->alias]['adapter'] = 'S3Storage';
			$model = $this->_detectModelByFileType($data['File']['file']['type']);
			if($model) {
				$data['File']['model'] = $this->$model->alias;
				$data['File']['adapter'] = 'S3Storage';
				try{
					if ($data = $this->$model->save(array($this->$model->alias => $data['File']))) {
						$this->response->statusCode(200);
						$message = "Upload Successful";
					}else {
						$this->response->statusCode(500);
						$message = "Upload Failed";
					}
				}catch (Exception $e) {
					debug($e->getMessage());exit;
				}
			}
			else {
				$this->response->statusCode(415);
				$message = "Invalid File Type";
			}
			if($this->request->is('ajax')) {
//				$this->layout = false;
//				$this->set('media', $this->FileStorage->find('all'));
//				$this->view = 'media-list';
				$this->browser();
			}else {
				$this->Session->setFlash($message);
			}


		}
	}

}