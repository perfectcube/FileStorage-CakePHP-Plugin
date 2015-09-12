<?php
App::uses('FileStorage', 'FileStorage.Model');
App::uses('FileAttach', 'FileStorage.Model');
App::uses('Folder', 'Utility');
/**
 * Image
 *
 * @author Florian Kr�mer
 * @copyright 2012 Florian Kr�mer
 * @license MIT
 */
class VideoStorage extends FileStorage {

/**
 * Name
 *
 * @var string
 */
	public $name = 'VideoStorage';

/**
 * Table to use
 *
 * @var mixed
 */
	public $useTable = 'file_storage';
	
	public $pathPrefix = "videos";

/**
 * Has many
 * 
 * @var array
 */
 	public $hasMany = array(
		'FileAttach' => array(
			'className' => 'FileStorage.FileAttach',
			'foreignKey' => 'file_storage_id',
			'dependent' => true
			)
		);

	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
		$this->actsAs = array(
			'FileStorage.UploadValidator' => array(
				'localFile' => true,
				'validate' => false,
				'allowedExtensions' => array(
					'mpg',
					'mov',
					'wmv',
					'avi',
					'f4v',
					'flv',
					'h264',
					'm4v',
					'mkv',
					'mp4',
					'mp4v',
					'wav',
					'mpe',
					'mpeg',
					'mpeg4',
					'mpg',
					'nsv',
					'qt',
					'swf',
					'xvid',
					)
				)
		);
	}


/**
 * Getter
 *
 * @param string $name
 * @throws RuntimeException
 * @return void
 */
	public function __get($name) {
		if ($name === 'createVersions') {
			throw new \RuntimeException(__d('file_storage', 'createVersions was removed, see the change log'));
		}
		parent::__get($name);
	}

/**
 * beforeSave callback
 *
 * @param array $options
 * @return boolean true on success
 */
	public function beforeSave($options = array()) {
		
		if (!parent::beforeSave($options)) {
			return false;
		}
		
		$Event = new CakeEvent('VideoStorage.beforeSave', $this, array(
			'record' => $this->data));
		$this->getEventManager()->dispatch($Event);

		if ($Event->isStopped()) {
			return false;
		}

		return true;
	}


/**
 * Get a copy of the actual record before we delete it to have it present in afterDelete
 *
 * @param boolean $cascade
 * @return boolean
 */
	public function beforeDelete($cascade = true) {
		if (!parent::beforeDelete($cascade)) {
			return false;
		}

		$Event = new CakeEvent('VideoStorage.beforeDelete', $this, array(
			'record' => $this->record,
			'storage' => $this->getStorageAdapter($this->record[$this->alias]['adapter'])));
		$this->getEventManager()->dispatch($Event);

		if ($Event->isStopped()) {
			return false;
		}

		return true;
	}

}
