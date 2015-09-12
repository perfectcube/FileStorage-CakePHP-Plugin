<?php
App::uses('AbstractStorageEventListener', 'FileStorage.Event');

/**
 * S3StorageListener
 *
 * @author Florian Krämer
 * @copy 2013 - 2014 Florian Krämer
 * @license MIT
 */
class S3StorageListener extends AbstractStorageEventListener {

/**
 * Adapter classes this listener can work with
 *
 * @var array
 */
	protected $_adapterClasses = array(
		'\Gaufrette\Adapter\AmazonS3',
		'\Gaufrette\Adapter\AwsS3',
	);

/**
 * Implemented Events
 *
 * @return array
 */
	public function implementedEvents() {
		return array(
			'FileStorage.afterSave' => 'afterSave',
			'FileStorage.afterDelete' => 'afterDelete',
			'ImageStorage.afterSave' => 'afterSave',
			'ImageStorage.afterDelete' => 'afterDelete',
			'VideoStorage.afterSave' => 'afterSave',
			'VideoStorage.afterDelete' => 'afterDelete',
			'AudioStorage.afterSave' => 'afterSave',
			'AudioStorage.afterDelete' => 'afterDelete',
		);
	}

/**
 * afterDelete
 *
 * @param CakeEvent $Event
 * @return void
 */
	public function afterDelete(CakeEvent $Event) {
		if ($this->_checkEvent($Event)) {
			$Model = $Event->subject();
			$record = $Event->data['record'][$Model->alias];
			$path = $this->_buildPath($Event);
			
			try {
				$Storage = $this->getAdapter($record['adapter']);

				if (!$Storage->has($path['combined'])) {
					return false;
				}
				$Storage->delete($path['combined']);
			} catch (Exception $e) {
				$this->log($e->getMessage(), 'file_storage');
				return false;
			}
			return true;
		}
	}

/**
 * afterSave
 *
 * @param CakeEvent $Event
 * @return void
 */
	public function afterSave(CakeEvent $Event) {
		if ($this->_checkEvent($Event)) {
			$Model = $Event->subject();
			$record = $Model->data[$Model->alias];
			$Storage = $this->getAdapter($record['adapter']);
			try {
				$path = $this->_buildPath($Event);
				$record['path'] = $path['path'];
				$result = $Storage->write($path['combined'], file_get_contents($record['file']['tmp_name']), true);
				$Model->save(array($Model->alias => $record), array(
					'validate' => false,
					'callbacks' => false)
				);
			} catch (Exception $e) {
				debug($e->getMessage());exit;
				$this->log($e->getMessage(), 'file_storage');
			}
		}
	}

/**
  * _buildPath
  *
  * @param CakeEvent $Event
  * @return array
  */
	protected function _buildPath(CakeEvent $Event) {
		$Model = $Event->subject();
		$record = $Model->data[$Model->alias];
		$adapterConfig = $this->getAdapterconfig($record['adapter']);
		$id = $record[$Model->primaryKey];

		$path = $Model->fsPath();
		$path = '/' . str_replace('sites/', '', SITE_DIR) . '/' . str_replace('\\', '/', $path);
		$path = str_replace('//', '/', $path);
		if ($this->options['preserveFilename'] === false) {
			$filename = $Model->stripUuid($id);
			if ($this->options['preserveExtension'] === true && !empty($record['extension'])) {
				$filename .= '.' . $record['extension'];
			}
		} else {
			$filename = $record['filename'];
		}
		$combined = $path . $filename;
		// not sure why it was this, and working on mathnasium??? rk
		$url = 'https://' . $adapterConfig['adapterOptions'][1] . '.s3.amazonaws.com' . $combined;
		//$url = 'https://s3.amazonaws.com' . $adapterConfig['adapterOptions'][1] . $combined;
		
		return array(
			'filename' => $filename,
			'path' => $path,
			'combined' => $path . $filename,
			'url' => $url
		);
	}

}