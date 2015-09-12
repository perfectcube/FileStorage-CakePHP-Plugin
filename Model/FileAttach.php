<?php
App::uses('FileStorageAppModel', 'FileStorage.Model');
/**
 * FileAttach
 */
class FileAttach extends FileStorageAppModel {

/**
 * Name
 *
 * @var string
 */
	public $name = 'FileAttach';

/**
 * Table name
 *
 * @var string
 */
	public $useTable = 'file_attachments';

/**
 * Displayfield
 *
 * @var string
 */
	public $displayField = 'title';

/**
 * Belongs to
 * 
 * @var array
 */
 	public $belongsTo = array(
		'FileStorage' => array(
			'className' => 'FileStorage.FileStorage',
			'foreign_key' => 'file_storage_id'
			),
		'AudioStorage' => array(
			'className' => 'FileStorage.AudioStorage',
			'foreign_key' => 'file_storage_id'
			),
		'ImageStorage' => array(
			'className' => 'FileStorage.ImageStorage',
			'foreign_key' => 'file_storage_id'
			),
		'VideoStorage' => array(
			'className' => 'FileStorage.VideoStorage',
			'foreign_key' => 'file_storage_id'
			)
		);

}
