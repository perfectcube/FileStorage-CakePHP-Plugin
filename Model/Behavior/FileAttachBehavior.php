<?php
App::uses('FileStorage', 'FileStorage.Model');
App::uses('FileAttach', 'FileStorage.Model');
App::uses('AudioStorage', 'FileStorage.Model');
App::uses('ImageStorage', 'FileStorage.Model');
App::uses('VideoStorage', 'FileStorage.Model');
//App::uses('Media', 'Media.Model');

class FileAttachBehavior extends ModelBehavior {

	public $settings = array();

/**
 * Setup method
 */
	public function setup(Model $Model, $settings = array()) {
		$this->FileStorage = new FileStorage();
		$this->FileAttach = new FileAttach();
		$this->AudioStorage = new AudioStorage();
		$this->ImageStorage = new ImageStorage();
		$this->VideoStorage = new VideoStorage();
		//Add the HasMany Relationship to the $Model
		$Model->bindModel($this->_bindModel($Model),false);
	}

/**
 *
 * This strips the Media from the request and places it in a variable
 * Uses the afterSave() Method to save the attchement
 *
 * @param Model $Model Model using this behavior
 * @return mixed False if the operation should abort. Any other result will continue.
 */
	public function beforeSave(Model $Model, $options = array()) {
		//doing it this way to protect against saveAll
		if (isset($Model->data['File'])) {
			$this->data['File'] = $Model->data['File'];
			unset($Model->data['File']);
		}
		return true;
	}

/**
 * afterSave is called after a model is saved.
 * We use this to save the attachement after the $Model is saved
 *
 * @param Model $Model Model using this behavior
 * @param boolean $created True if this save created a new record
 * @return boolean
 */
	public function afterSave(Model $Model, $created, $options = array()) {
		if (!empty($this->data['File'])) {
			$files = $this->upload($Model, $this->data);
		}
		return true;
	}

/**
 * Before delete is called before any delete occurs on the attached model, but after the model's
 * beforeDelete is called.  Returning false from a beforeDelete will abort the delete.
 *
 * We are unbinding the association model, so we can handle the delete ourselves
 *
 * @todo Might be a better way to do this with model associations
 *
 * @param Model $Model Model using this behavior
 * @param boolean $cascade If true records that depend on this record will also be deleted
 * @return mixed False if the operation should abort. Any other result will continue.
 */
	public function beforeDelete(Model $Model, $cascade = true) {
		//unbinds the model, so we can handle the delete
		$Model->unbindModel(array('hasMany' => array('File')));
		return true;
	}

/**
 * After delete is called after any delete occurs on the attached model.
 *
 * Deletes all attachment records, but keeps the attached data
 *
 * @todo The deleteAll() here seems to not even be necessary, due to the attachments in _bindModel()
 *
 * @param Model $Model Model using this behavior
 * @return void
 */
	public function afterDelete(Model $Model) {
		// delete all Media links
		$this->FileStorage->deleteAll(array('model' => $Model->alias, 'foreign_key' => $Model->data[$Model->alias]['id']), false);
	}

/**
 * Before find callback.
 * 
 * @param Model $Model Model using this behavior
 * @param mixed $query The query being run
 * @return mixed An array change the query being run
 */
	public function beforeFind(Model $Model, $query) {
		//Allows us to pass $query['media'] = false to not contain media
		if(isset($query['media']) && !$query['media']) {
			return $query;
		}
		if(empty($Model->hasAndBelongsToMany['FileStorage'])){
			$Model->bindModel($this->_bindModel($Model),false);
		}

		$query['contain'][] = 'FileStorage';
		return $query;
	}

/**
 * After find callback
 * 
 * Unserialize the response from Google Maps
 * 
 * @param Model $Model
 * @param array $results
 * @param boolean $primary
 * @return array
 */
	public function afterFind(Model $Model, $results, $primary = false) {
		// handles many
		for ($i=0; $i < count($results); $i++) {
			if (!empty($results[$i]['FileStorage'])) {
				$results[$i]['_FileStorage'] = Set::combine($results[$i], 'FileStorage.{n}.FileAttachment.code', 'FileStorage.{n}'); 
			}
		}
		// handles one
		if (!empty($results['FileStorage'])) {
			$results['_FileStorage'] = Set::combine($results, 'FileStorage.{n}.code', 'FileStorage.{n}'); 
		}
		return $results;
	}

/**
 * Bind Model method
 *
 * @param object $Model
 */
	protected function _bindModel($Model){
    	return array('hasAndBelongsToMany' => array(
        	'FileStorage' =>
            	array(
                	'className' => 'FileStorage.FileStorage',
                	'joinTable' => 'file_attachments',
                	'foreignKey' => 'foreign_key',
                	'associationForeignKey' => 'file_storage_id',
                	// 'conditions' => array(
                		// 'FileAttach.model' => $Model->alias
                		// ),
            		'order' => array('ISNULL(FileAttachment.order)', 'FileAttachment.order')
            	)
        	)
		);
	}

/**
 * Upload method
 * 
 * @param object $Model
 * @param array $data
 */
	public function upload(Model $Model, $data) {
		$data = !empty($data) ? $data : $this->data;

		if (!empty($data['File'])) {
			ini_set('memory_limit', '750M');
			foreach ($data['File'] as $attachment) {	
				if (!empty($attachment['file']['tmp_name'])) { // make sure there is actually a file to upload
					$data[$this->ImageStorage->alias]['adapter'] = 'S3Storage';
					$model = $this->_detectModelByFileType($attachment['file']['type']);
					
					if ($model) {
						$data['File']['model'] = $model;
						$data['File']['adapter'] = 'S3Storage';
						App::uses('File', 'Utility');
						$file = new File($attachment['file']['tmp_name']);
						$info = $file->info();
						// fill in empty data that should have something (if possible)
						$attachment['user_id'] = !empty($attachment['user_id']) ? $attachment['user_id'] : CakeSession::read('Auth.User.id');
						$attachment['title'] = !empty($attachment['title']) ? $attachment['title'] : $attachment['file']['name'];
						$attachment['alt'] = !empty($attachment['alt']) ? $attachment['alt'] : ZuhaInflector::asciify($attachment['file']['name']);

						$saveData = array($this->$model->alias => array_merge($attachment, array(
							'filename' => uniqid('', true) . '.' . substr(strrchr($attachment['file']['name'],'.'),1),
							'filesize' => $info['filesize'],
							'file' => $attachment['file'],
							'mime_type' => $info['mime'],
							'extension' => substr(strrchr($attachment['file']['name'],'.'),1),
							//'path' => '/' . str_replace('sites/', '', SITE_DIR) . str_replace($replacement, '', $info['dirname']) . '/',
							'adapter' => 'S3Storage',
							))
						);
						$saveData[$this->$model->alias]['file']['name'] = uniqid('', true) . '.' . substr(strrchr($attachment['file']['name'],'.'),1);
							
						$saveData['FileAttach'] = array_merge($attachment, array(
							'model' => $Model->name,
							'foreign_key' => $Model->id
							));
						try {
							$this->$model->create();	
							if ($this->$model->save($saveData)) {
								$saveData['FileAttach']['file_storage_id'] = $this->$model->getLastInsertID();
								$this->FileAttach->create();
								$this->FileAttach->save($saveData);
							}
						} catch (Exception $e) {
							debug('Please report this error, with the information below.');
							debug($e->getMessage());
							debug($this->$model->invalidFields());
							exit;
						}
					}
				}
			}
		}
		return true;
	}

	protected function _detectModelByFileType ($mime_type) {
		if (empty($mime_type)) {
			return false;
		}
		return FileStorageUtils::detectModelByFileType($mime_type);
	}

}
