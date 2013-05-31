<?php
App::uses('ModelBehavior', 'Model');
/**
 * File Upload Behavior
 *
 * This behavior will store uploaded files in a hasMany or hasOne association
 * it builds on the fly to the FileStorage model.
 *
 * The files itself will be stored in the configured storage backend.
 *
 * @author Florian Krämer
 * @copyright 2012 Florian Krämer
 * @license MIT
 */
class FileUploadBehavior extends ModelBehavior {

/**
 * Default settings
 *
 * @var array
 */
	protected $_defaults = array(
		'fileField' => 'file',
		'pathField' => 'path',
		'adapterConfig' => 'Local',
		'storageDeleteCallback' => 'afterDelete',
		'storageModelAssociation' => true,
		'storageModelAssocName' => '',
		'storageKeyCallback' => 'computeStorageKey',
	);

/**
 * Behavior Setup
 *
 * @param Model $Model
 * @param array $settings
 * @throws InvalidArgumentException
 * @return void
 */
	public function setup(Model $Model, $settings = array()) {
		if (!is_array($settings)) {
			throw new InvalidArgumentException(__d('file_storage', 'Settings must be passed as array!'));
		}

		$this->settings[$Model->alias] = array_merge($this->_defaults, $settings);
		extract($this->settings[$Model->alias]);

		if ($storageModelAssociation === true) {
			$Model->bindModel(array(
				'hasMany' => array(
					'File' => array(
						'className' => 'FileStorage.FileStorage',
						'foreignKey' => 'foreign_key',
						'conditions' => array('File.model' => $Model->name),
						'depends' => false))));
			$this->settings[$Model->alias]['storageModelAssocName'] = 'File';
		} elseif(is_array($storageModelAssociation)) {
			$Model->bindModel($storageModelAssociation);
			if (isset($storageModelAssociation['hasOne'])) {
				$keys = array_keys($storageModelAssociation['hasOne']);
				$assocKeys[0];
			}
			if (isset($storageModelAssociation['hasMany'])) {
				$keys = array_keys($storageModelAssociation['hasMany']);
				$assocKeys[0];
			}
			if (isset($key)) {
				$this->settings[$Model->alias]['storageModelAssocName'] = $assocKeys[0];
			}
		} elseif (is_string($storageModelAssociation)) {
			$this->settings[$Model->alias]['storageModelAssocName'] = $storageModelAssociation;
		}
	}

/**
 * Saves an uploaded file
 *
 * @param Model $Model
 * @return boolean|array
 */
	public function saveUploadedFile(Model $Model) {
		extract($this->settings[$Model->alias]);

		$Model->data[$storageModelAssocName]['adapter'] = $adapterConfig;
		$Model->data[$storageModelAssocName]['foreign_key'] = $Model->getLastInsertId();

		if (empty($Model->data[$storageModelAssocName]['model'])) {
			$Model->data[$storageModelAssocName]['model'] = get_class($Model);
		}

		if ($Model->{$storageModelAssocName}->save($Model->data)) {
			$Model->data[$storageModelAssocName]['id'] = $Model->{$storageModelAssocName}->getLastInsertId();
			return $Model->data;
		}

		return false;
	}

/**
 * beforeSave callback
 *
 * @param Model $Model
 * @return boolean
 */
	public function beforeSave(Model $Model) {
		extract($this->settings[$Model->alias]);
		return true;
	}

/**
 * afterSave
 *
 * @param Model $Model
 * @param boolean $created
 * @return boolean
 */
	public function afterSave(Model $Model, $created) {
		extract($this->settings[$Model->alias]);
		$this->saveUploadedFile($Model);
		return true;
	}

/**
 * Deletes all associated files for this record
 *
 * @param Model $Model
 * @param null $id
 * @throws RuntimeException
 * @return boolean
 */
	protected function deleteFiles(Model $Model, $id = null) {
		if (empty($id) && !empty($Model->id)) {
			$id = $Model->id;
		} else {
			throw new RuntimeException(__d('file_storage', 'No id given!'));
		}

		extract($this->settings[$Model->alias]);
		return $Model->{$storageModelAssocName}->deleteAll(array(
			$storageModelAssocName . '.model' => get_class($Model),
			$storageModelAssocName . '.foreign_key' => $id));
	}

/**
 * computeKey
 *
 * @return string
 */
	public function computeKey() {
		return String::uuid();
	}

}