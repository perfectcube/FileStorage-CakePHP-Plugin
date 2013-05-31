<?php
/**
 * File Upload Behavior Test
 *
 * @author Florian Krämer
 * @copyright 2012 Florian Krämer
 * @license MIT
 */
App::uses('Model', 'Model');
App::uses('FileUploadBehavior', 'FileStorage.Model\Behavior');

class Item extends CakeTestModel {

}

/**
 * UploadValidatorBehaviorTest class
 *
 * @package       Cake.Test.Case.Model.Behavior
 */
class FileUploadBehaviorTest extends CakeTestCase {

/**
 * Holds the instance of the model
 *
 * @var mixed
 */
	public $Item = null;

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.FileStorage.Item',
		'plugin.FileStorage.FileStorage');

/**
 * startTest
 *
 * @return void
 */
	public function setUp() {
		$this->testFilePath = CakePlugin::path('FileStorage') . 'Test' . DS . 'Fixture' . DS . 'File' . DS;
	}

/**
 * endTest
 *
 * @return void
 */
	public function tearDown() {
		ClassRegistry::flush();
	}

/**
 * testSetup
 *
 * @return void
 */
	public function testSetup() {
		$Item = ClassRegistry::init('Item');
		$Item->Behaviors->load('FileStorage.FileUpload', array());
		$this->assertEqual($Item->hasMany, array(
			'File' => array(
				'className' => 'FileStorage.FileStorage',
				'foreignKey' => 'foreign_key',
				'conditions' => array(
					'File.model' => 'Item'),
				'depends' => false,
				'fields' => '',
				'order' => '',
				'limit' => '',
				'offset' => '',
				'dependent' => '',
				'exclusive' => '',
				'finderQuery' => '',
				'counterQuery' => '')));

		$this->assertEqual($Item->Behaviors->FileUpload->settings['Item'], array(
			'fileField' => 'file',
			'pathField' => 'path',
			'adapterConfig' => 'Local',
			'storageDeleteCallback' => 'afterDelete',
			'storageModelAssociation' => true,
			'storageModelAssocName' => 'File',
			'storageKeyCallback' => 'computeStorageKey'
		));
	}

/**
 * testSaveUploadedFile
 *
 * @return void
 */
	public function testSaveUploadedFile() {
		$Item = ClassRegistry::init('Item');
		$Item->Behaviors->load('FileStorage.FileUpload', array());
		$Item->create();
		$result = $Item->save(array(
			'Item' => array(
				'name' => 'File Upload'),
			'File' => array(
				'file' => array(
					'name' => 'cake.power.gif',
					'type' => 'image/gif',
					'tmp_name' => $this->testFilePath . 'cake.icon.png',
					'error' => 0,
					'size' => 1212))));
		debug($Item->File->find('all'));
	}

}
