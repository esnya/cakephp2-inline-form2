<?php
App::uses('Model', 'Model');
App::uses('ComponentCollection', 'Controller');
App::uses('Controller', 'Controller');
App::uses('Component', 'Controller');
App::uses('InlineForm2Component', 'InlineForm2.Controller/Component');

/**
 * InlineForm2Component Test Case
 *
 */
class InlineForm2ComponentTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Controller = new TestModelsController();
		$this->Controller->TestModel = new TestModel;
		$this->Controller->TestModel->TestChildModel = new TestChildModel;
		$Collection = new ComponentCollection();
		$this->InlineForm2 = new InlineForm2Component($Collection);
		$this->Controller->Auth = new TestAuth();
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->InlineForm2);

		parent::tearDown();
	}

	public function testUpdate() {
		$data = [
			"action" => "update",
			"model" => "TestModel",
			"field" => "name",
			"id" => "1",
			"value" => "changed_name",
		];
		$this->Controller->data = $data;
		$this->InlineForm2->update($this->Controller);

		$vars = $this->Controller->viewVars;
		$this->assertEqual(Hash::get($vars, '_serialize'), 'data');
		$this->assertEqual(Hash::get($vars, 'data.status'), 'OK');
		$this->assertEqual(Hash::get($vars, 'data.data.TestModel.id'), '1');
		$this->assertEqual(Hash::get($vars, 'data.data.TestModel.name'), 'changed_name');
		$this->assertEqual(count(Hash::extract($vars, 'data.data.TestChildModel')), 1);
	}

	public function testUpdateChild() {
		$data = [
			"action" => "update",
			"model" => "TestChildModel",
			"field" => "name",
			"id" => "1",
			"value" => "changed_child_name",
		];
		$this->Controller->data = $data;
		$this->InlineForm2->update($this->Controller);

		$vars = $this->Controller->viewVars;
		$this->assertEqual(Hash::get($vars, '_serialize'), 'data');
		$this->assertEqual(Hash::get($vars, 'data.status'), 'OK');
		$this->assertEqual(Hash::get($vars, 'data.data.TestModel.id'), '1');
		$this->assertEqual(Hash::get($vars, 'data.data.TestChildModel.id'), '10');
		$this->assertEqual(Hash::get($vars, 'data.data.TestChildModel.name'), 'changed_child_name');
		$this->assertEqual(Hash::get($vars, 'data.data.TestChildModel.test_model_id'), '1');
	}

	public function testAddChild() {
		$data = [
			"action" => "add",
			"model" => "TestChildModel",
			"init" => [
				"test_model_id" => 1,
			]
		];
		$this->Controller->data = $data;
		$this->InlineForm2->add($this->Controller);
		$vars = $this->Controller->viewVars;
		$this->assertEqual(Hash::get($vars, '_serialize'), 'data');
		$this->assertEqual(Hash::get($vars, 'data.status'), 'OK');
		$this->assertEqual(Hash::get($vars, 'data.data.TestModel.id'), '1');
		$this->assertEqual(Hash::get($vars, 'data.data.TestChildModel.id'), '11');
		$this->assertEqual(Hash::get($vars, 'data.data.TestChildModel.test_model_id'), 1);
	}

	public function testIF2Update() {
		$data = [
			"action" => "update",
			"model" => "TestModel",
			"field" => "name",
			"id" => "1",
			"value" => "changed_name",
		];
		$this->Controller->data = $data;
		$this->InlineForm2->inlineform2($this->Controller);

		$vars = $this->Controller->viewVars;
		$this->assertEqual(Hash::get($vars, '_serialize'), 'data');
		$this->assertEqual(Hash::get($vars, 'data.status'), 'OK');
		$this->assertEqual(Hash::get($vars, 'data.data.TestModel.id'), '1');
		$this->assertEqual(Hash::get($vars, 'data.data.TestModel.name'), 'changed_name');
		$this->assertEqual(count(Hash::extract($vars, 'data.data.TestChildModel')), 1);
	}

	public function testIF2AddChild() {
		$data = [
			"action" => "add",
			"model" => "TestChildModel",
			"init" => [
				"test_model_id" => 1,
			]
		];
		$this->Controller->data = $data;
		$this->InlineForm2->inlineform2($this->Controller);
		$vars = $this->Controller->viewVars;
		$this->assertEqual(Hash::get($vars, '_serialize'), 'data');
		$this->assertEqual(Hash::get($vars, 'data.status'), 'OK');
		$this->assertEqual(Hash::get($vars, 'data.data.TestModel.id'), '1');
		$this->assertEqual(Hash::get($vars, 'data.data.TestChildModel.id'), '11');
		$this->assertEqual(Hash::get($vars, 'data.data.TestChildModel.test_model_id'), 1);
	}

	/**
	* @expectedException ForbiddenException
	*/
	public function testUpdate403() {
		$this->Controller->Auth->user('id', 'test_other_user');
		$data = [
			"action" => "update",
			"model" => "TestModel",
			"field" => "name",
			"id" => "1",
			"value" => "changed_name",
		];
		$this->Controller->data = $data;
		$this->InlineForm2->update($this->Controller);
	}

	/**
	* @expectedException ForbiddenException
	*/
	public function testUpdateChild403() {
		$this->Controller->Auth->user('id', 'test_other_user');
		$data = [
			"action" => "update",
			"model" => "TestChildModel",
			"field" => "name",
			"id" => "1",
			"value" => "changed_child_name",
		];
		$this->Controller->data = $data;
		$this->InlineForm2->update($this->Controller);
	}

	/**
	* @expectedException ForbiddenException
	*/
	public function testAddChild403() {
		$this->Controller->Auth->user('id', 'test_other_user');
		$data = [
			"action" => "add",
			"model" => "TestChildModel",
			"init" => [
				"test_model_id" => 1,
			]
		];
		$this->Controller->data = $data;
		$this->InlineForm2->add($this->Controller);
	}

	public function testDeleteChild() {
		$data = [
			'action' => 'delete',
			'model' => 'TestChildModel',
			'id' => 1,
		];

		$this->Controller->data = $data;

		$this->InlineForm2->delete($this->Controller);

		$vars = $this->Controller->viewVars;
		$this->assertEqual(Hash::get($vars, '_serialize'), 'data');
		$this->assertEqual(Hash::get($vars, 'data.status'), 'OK');
		$this->assertNotNull(Hash::extract($vars, 'data.data'));
		$this->assertEqual($this->Controller->TestModel->TestChildModel->deleted, 1);
		$this->assertEqual($this->Controller->TestModel->TestChildModel->deleteCascade, false);
	}

	public function testIF2DeleteChild() {
		$data = [
			'action' => 'delete',
			'model' => 'TestChildModel',
			'id' => 1,
		];

		$this->Controller->data = $data;

		$this->InlineForm2->inlineform2($this->Controller);

		$vars = $this->Controller->viewVars;
		$this->assertEqual(Hash::get($vars, '_serialize'), 'data');
		$this->assertEqual(Hash::get($vars, 'data.status'), 'OK');
		$this->assertNotNull(Hash::extract($vars, 'data.data'));
		$this->assertEqual($this->Controller->TestModel->TestChildModel->deleted, 1);
		$this->assertEqual($this->Controller->TestModel->TestChildModel->deleteCascade, false);
	}

	/**
	* @expectedException ForbiddenException
	*/
	public function testDeleteChild403() {
		$this->Controller->Auth->user('id', 'test_other_user');

		$data = [
			'action' => 'delete',
			'model' => 'TestChildModel',
			'id' => 1,
		];

		$this->Controller->data = $data;

		$this->InlineForm2->delete($this->Controller);
	}

}

class TestModelsController extends Controller { 
	public $layout;
	public $data = [];

	public $TestModel = null;
}

class TestModel extends Model {
	public $database = 'test';
	public $hasMany = [
		'TestChildModel',
	];
	public $data = [
		'id' => 1,
		'name'=> 'name_of_test_model',
		'user_id' => 'test_user',
		'age' => 12,
	];
	public $useTable = false;

	public function save($data = NULL, $validate = true, $fieldList = []) {
	}

	public function set($field, $value = null) {
		$this->data[$field] = $value;
	}

	public function find($type = 'first', $query = []) {
		$childData = [$this->TestChildModel->data];
		if ($this->TestChildModel->addedData) {
			$childData += $this->TestChildModel->addedData;
		}
		return [
			'TestModel' => $this->data,
			'TestChildModel' => $childData,
		];
	}
}

class TestChildModel extends Model {
	public $database = 'test';
	public $belongsTo = [
		'TestModel',
	];
	public $data = [
		'id' => 10,
		'name'=> 'name_of_test_child_model',
		'test_model_id' => 1,
	];
	public $addedData = null;
	public $useTable = false;
	public $insertedId = 11;

	private $created = false;

	public $deleted;
	public $deleteCascade;

	public function create($data = [], $filterKey = false) {
		$this->created = true;
		$this->addedData = [
		'id' => 11,
		'name' => null,
		'test_model_id' => null,
		];
	}

	public function save($data = NULL, $validate = true, $fieldList = []) {
		if ($this->created) {
		}
	}

	public function getLastInsertId() {
		if ($this->created) {
			return 11;
		}
	}

	public function set($field, $value = null) {
		if ($this->created) {
			if (is_array($field)) {
				foreach ($field as $key => $value) {
					$this->addedData[$key] = $value;
				}
			} else {
				$this->addedData[$field] = $value;
			}
		} else {
			$this->data[$field] = $value;
		}
	}

	public function find($type = 'first', $query = []) {
		if ($query && array_key_exists('conditions', $query) && is_array($query['conditions']) && array_key_exists('TestChildModel.id', $query['conditions']) && $query['conditions']['TestChildModel.id'] == 11) {
			return ['TestModel' => $this->TestModel->data, 'TestChildModel' => $this->addedData];
		}
		return [
		'TestModel' => $this->TestModel->data,
			'TestChildModel' => $this->data,
		];
	}

	public function delete($id = null, $cascade = true) {
		$this->deleted = $id;
		$this->deleteCascade = false;
	}
}

class TestAuth {
	public $id = 'test_user';

	public function user($field, $value = null) {
		if ($value === null) {
			return $this->id;
		} else {
			return ($this->id = $value);
		}
	}
}
