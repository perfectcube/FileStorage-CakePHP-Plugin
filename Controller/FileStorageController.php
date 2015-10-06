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
	
	
	/**
	 * Custom function for migrating from old file browser (kcfinder) to new one
	 * 
	 * 1. upload all files inside of the webroot/upload folder (including the upload folder) to s3
	 * 2. run this migrate function
	 * 3. check the /file_storage/file_storage/browser page (thumbnails should be showing up correctly now)
	 * 4. delete the files from the server
	 */
	public function migrate() {
		
		$replacement = $directory = ROOT . DS . SITE_DIR . DS. 'Locale' . DS . 'View' . DS . 'webroot';
		$directory = $replacement . DS . 'upload';
		App::uses('Folder', 'Utility');
		App::uses('File', 'Utility');
		$dir = new Folder($directory);
		$files = $dir->findRecursive();
		foreach ($files as $file) {
			$file = new File($file);
			$info = $file->info();
			$model = $this->_detectModelByFileType($info['mime']);
			if (!empty($model)) {
				$data['FileStorage']['model'] = $model;
				$data['FileStorage']['filename'] = $info['basename'];
				$data['FileStorage']['filesize'] = $info['filesize'];
				$data['FileStorage']['mime_type'] = $info['mime'];
				$data['FileStorage']['extension'] = $info['extension'];
				$data['FileStorage']['path'] = '/' . str_replace('sites/', '', SITE_DIR) . str_replace($replacement, '', $info['dirname']) . '/';
				$data['FileStorage']['adapter'] = 'S3Storage';
				$data['FileStorage']['creator_id'] = $int = intval(filter_var($data['FileStorage']['path'], FILTER_SANITIZE_NUMBER_INT));
				$data['FileStorage']['modifier_id'] = $int;
				$data['FileStorage']['created'] = date('Y-m-d H:i:s', $file->lastChange());
				$data['FileStorage']['modified'] = date('Y-m-d H:i:s');
				
				if ($duplicate = $this->FileStorage->find('first', array('conditions' => array('FileStorage.filename' => $data['FileStorage']['filename'])))) {
					debug('Duplicate : ' . $data['FileStorage']['filename']);
					unset($data);
					continue;
				}
				$this->FileStorage->create();
				if ($this->FileStorage->save($data, array('callbacks' => false))) {
					debug('Saved: ' . $data['FileStorage']['filename']);
					unset($data);
					continue;
				} else {
					debug($data);
					exit;
				}
			}			
			$file->close();
		}
		$this->render(false);
	}

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
		$params['order'] = array('FileStorage.filename' => 'ASC');

		if($this->request->is('ajax')) {
			$this->view = 'media-list';
		}
		$this->set('media', $this->FileStorage->find('all', $params));
	}

	public function delete($id) {
		// if (!$this->request->is('get')) { why do we care if it's a get request
			$media = $this->FileStorage->find('first', array('conditions' => array('FileStorage.id' => $id)));
			if ($media) {
				//Checks the model saved with the record.
				//falls back to base model if not a real object
				$model = $media[$this->FileStorage->alias]['model'];
				if(!is_object($this->$model)) {
					$model = $this->_detectModelByFileType($media[$this->FileStorage->alias]['mime_type']);
				}
				$this->$model->id = $id;
				if ($this->$model->delete()) {
					$message = "File Deleted!";	
				} else {
					$this->response->statusCode(500);
					$message = "File could not be deleted";
				}
			} else {
				$this->response->statusCode(404);
				$message = "File could not be found";
			}
		// } else {
			// $message = "Bad Request";
			// $this->response->statusCode(400);
		// }

		if ($this->request->is('ajax')) {
			$this->layout = false;
			$this->set('media', $this->FileStorage->find('all', array('order' => array('FileStorage.filename' => 'ASC'))));
			$this->view = 'media-list';
		} else {
			$this->Session->setFlash($message);
			$this->redirect($this->referer()); // not sure this won't cause a problem (needed it for delete links)
		}
	}

	public function upload() {
		if (!$this->request->is('get')) {
			$data = $this->request->data;
//			debug($this->ImageStorage->alias);
			$data[$this->ImageStorage->alias]['adapter'] = 'S3Storage';
//			$data['ImageStorage']['adapter'] = 'S3Storage';
//			$data['VideoStorage']['adapter'] = 'S3Storage';
//			debug($data);
			$model = $this->_detectModelByFileType($data['File']['file']['type']);
			if ($model) {
				$data['File']['model'] = $this->$model->alias;
				$data['File']['adapter'] = 'S3Storage';
				try {
					if ($data = $this->$model->save(array($this->$model->alias => $data['File']))) {
						$this->response->statusCode(200);
						$message = "Upload Successful";
					} else {
						$this->response->statusCode(500);
						$message = "Upload Failed";
					}
				} catch (Exception $e) {
					debug($e->getMessage());exit;
				}
			} else {
				$this->response->statusCode(415);
				$message = "Invalid File Type";
			}
			if($this->request->is('ajax')) {
				$this->layout = false;
				$this->set('media', $this->FileStorage->find('all', array('order' => array('FileStorage.filename' => 'ASC'))));
				$this->view = 'media-list';
				$this->browser();
			} else {
				$this->Session->setFlash($message);
			}
		}
	}

}