<?php
	App::uses('Controller', 'Controller');
	App::uses('View', 'View');
	App::uses('InlineForm2Helper', 'InlineForm2.View/Helper');

	class InlineForm2HelperTest extends CakeTestCase {

		public function setUp() {
			parent::setUp();
			$Controller = new Controller();
			$this->View = new View($Controller);
			$this->InlineForm2 = new InlineForm2Helper($this->View);

			$this->data = [
				'Model' => [
					'id' => 1,
					'name' => 'inline_form2_test_name',
					'age' => '123',
					'flag1' => 1,
					'model_id' => 1,
					'text' => "text\nwith\nnl",
				],
				'OtherModel' => [
					'id' => 3,
					'name'=> 'othe_model_name',
				],
                'ChildModel' => [
                    [
                        'id' => 2,
                        'model_id' => 1,
                        'name' => 'inline_form2_test_child_2_name',
                    ],
                ],
			];
		}

		public function testInlineForm2Create() {
			$html = $this->InlineForm2->create($this->data, 'Model');

			$script = $this->View->fetch('script');
			$css = $this->View->fetch('css');
			$this->assertContains('script', $script);
			$this->assertContains('inline_form2/js/inlineform.js', $script);
			$this->assertContains('link', $css);
			$this->assertContains('inline_form2/css/inlineform.css', $css);

			$this->assertContains('div', $html);
			$this->assertContains('class="if-form"', $html);
			$this->assertContains('data-url="/models/inlineform2"', $html);

			$html = $this->InlineForm2->end();

			$this->assertContains('/div', $html);
		}

		public function testControl() {
			$this->InlineForm2->create($this->data, 'Model');
			$html = $this->InlineForm2->control('name');
			$this->InlineForm2->end();

			$this->assertContains('div', $html);
			$this->assertContains('tabindex="0"', $html);
			$this->assertContains('class="if-control', $html);
			$this->assertContains('data-model="Model"', $html);
			$this->assertContains('data-field="name"', $html);
			$this->assertContains('data-id="' . $this->data['Model']['id'] . '"', $html);
			$this->assertContains('class="if-value', $html);
			$this->assertContains($this->data['Model']['name'], $html);
			$this->assertContains('class="if-input', $html);
		}

		public function testControlOptions() {
			$html = $this->InlineForm2->create($this->data, 'Model', ['data-debug' => 1,], ['data-debug' => 2,], ['data-debug' => 3,], ['data-debug' => 4,]);
			$this->assertContains('data-debug="1"', $html);

			$html = $this->InlineForm2->control('name');
			$this->assertContains('data-debug="2"', $html);
			$this->assertContains('data-debug="3"', $html);
			$this->assertContains('data-debug="4"', $html);
			$this->InlineForm2->end();
		}

		public function testReadonlyControl() {
			$this->InlineForm2->create($this->data, 'Model');
			$html = $this->InlineForm2->control('id', ['readonly' => true]);
			$this->InlineForm2->end();

			$this->assertContains('if-readonly', $html);
			$this->assertContains(">{$this->data['Model']['id']}<", $html);
			$this->assertNotContains('input', $html);
		}

		public function testTextControl() {
			$this->InlineForm2->create($this->data, 'Model');
			$html = $this->InlineForm2->control('name');
			$this->InlineForm2->end();

			$this->assertContains('input type="text"', $html);
			$this->assertContains('data-type="text"', $html);
		}

		public function testMultilineControl() {
			$this->InlineForm2->create($this->data, 'Model');
			$html = $this->InlineForm2->control('text', ['type' => 'multiline']);
			$this->InlineForm2->end();

			$this->assertContains('textarea', $html);
			$this->assertContains('data-type="multiline"', $html);
			$this->assertContains('<br', $html);
		}

		public function testNumberControl() {
			$this->InlineForm2->create($this->data, 'Model');
			$html = $this->InlineForm2->control('age', ['type' => 'number']);
			$this->InlineForm2->end();

			$this->assertContains(">{$this->data['Model']['age']}<", $html);
			$this->assertContains('input type="number"', $html);
			$this->assertContains('data-type="number"', $html);
		}

		public function testSelectControl() {
			$this->InlineForm2->create($this->data, 'Model');
			$html = $this->InlineForm2->control('model_id', ['options' => 
				[
					1 => 'Model1',
					2 => 'Model2',
					3 => 'Model3',
				]
			]);
			$this->InlineForm2->end();

			$this->assertContains('data-value="1"', $html);
			$this->assertContains('>Model1<', $html);
			$this->assertContains('select', $html);
			$this->assertContains('value="1"', $html);
			$this->assertContains('value="2"', $html);
			$this->assertContains('value="3"', $html);
			$this->assertContains('Model1', $html);
			$this->assertContains('Model2', $html);
			$this->assertContains('Model3', $html);
			$this->assertContains('data-type="select"', $html);
		}

		public function testSelectControlWithEmpty() {
			$this->InlineForm2->create($this->data, 'Model');
			$html = $this->InlineForm2->control('model_id', ['options' => 
				[
					1 => 'Model1',
					2 => 'Model2',
					3 => 'Model3',
				],
				'empty' => true
			]);
			$this->InlineForm2->end();

			$this->assertContains('>Model1<', $html);
			$this->assertContains('select', $html);
			$this->assertContains('value=""', $html);
			$this->assertContains('value="1"', $html);
			$this->assertContains('value="2"', $html);
			$this->assertContains('value="3"', $html);
			$this->assertContains(__('(No Select)'), $html);
			$this->assertContains('Model1', $html);
			$this->assertContains('Model2', $html);
			$this->assertContains('Model3', $html);
		}

		public function testCheckBoxControl() {
			$this->InlineForm2->create($this->data, 'Model');
			$html = $this->InlineForm2->control('flag1', ['type' => 'checkbox', 'true' => 'TRUE', 'false' => 'FALSE']);
			$this->InlineForm2->end();

			$this->assertContains('>TRUE<', $html);
			$this->assertContains('input type="checkbox"', $html);
			$this->assertContains('data-type="checkbox"', $html);
			$this->assertContains('data-true="TRUE"', $html);
			$this->assertContains('data-false="FALSE"', $html);
			$this->assertContains('checked', $html);
		}

		public function testControlCallback() {
			$this->InlineForm2->create($this->data, 'Model');
			$html = $this->InlineForm2->control('name', ['callback' => function (&$options, &$data) {
				$options['data-debug'] = $data['Model']['id'];
			}]);
			$this->assertContains("data-debug=\"{$this->data['Model']['id']}\"", $html);
			$this->InlineForm2->end();
		}

		public function testHasManyChild() {
			$this->InlineForm2->create($this->data, 'Model');
			$html = $this->InlineForm2->createHasManyChild('ChildModel');
			$this->assertContains('div', $html);
			$this->assertContains('class="if-hasmanychild', $html);
			$this->assertContains('data-model="ChildModel', $html);
			
			$html = $this->InlineForm2->endHasManyChild();
			$this->InlineForm2->end();
		}

		public function testTemplateBuffering() {
			$this->InlineForm2->create($this->data, 'Model');
			$this->InlineForm2->createHasManyChild('ChildModel');

			$this->InlineForm2->startTemplate();
		?>
		Debug Debug Foobar<div>Tag</div>
		<?php
			$html = $this->InlineForm2->endTemplate();
			$this->assertContains('Debug Debug Foobar<div>Tag</div>', $html);
			$this->assertContains('class="if-group"', $html);
			$this->assertContains('data-model="ChildModel"', $html);
			$this->assertContains('data-id="2"', $html);
			$this->assertContains('if-insert if-hidden', $html);
			$this->InlineForm2->endHasManyChild();

			$this->InlineForm2->end();
		}

		public function testHasManyChildTemplate() {
			$this->InlineForm2->create($this->data, 'Model');
			$this->InlineForm2->createHasManyChild('ChildModel');

			$this->InlineForm2->startTemplate();
		?>
		<dt>Name</dt>
		<dd><?php echo $this->InlineForm2->control('name', ['type' => 'text']); ?></dd>
		<?php
			$this->InlineForm2->endTemplate();
			$html = $this->InlineForm2->endHasManyChild();
			$this->assertContains('<dt>Name</dt>', $html);
			$this->assertContains('dd', $html);
			$this->assertContains('class="if-control', $html);
			$this->assertContains('class="if-template if-group', $html);

			$this->InlineForm2->end();
		}

		public function testHasManyChildChildren() {
			$this->InlineForm2->create($this->data, 'Model');
			$this->InlineForm2->createHasManyChild('ChildModel');

			$this->InlineForm2->startTemplate();
		?>
		<dt>Name</dt>
		<dd><?php echo $this->InlineForm2->control('name', ['type' => 'text']); ?></dd>
		<?php
			$html = $this->InlineForm2->endTemplate();
			$this->assertContains('data-id="2"', $html);
			$this->assertContains('>inline_form2_test_child_2_name<', $html);
			$this->InlineForm2->endHasManyChild();
			$this->InlineForm2->end();
		}

		public function testTemplateCallback() {
			$this->InlineForm2->create($this->data, 'Model');
			$this->InlineForm2->createHasManyChild('ChildModel');

			$this->InlineForm2->startTemplate();
		?>
		<dt>Name</dt>
		<dd><?php echo $this->InlineForm2->control('name', ['type' => 'text']); ?></dd>
		<dd>
		<?php
			echo $this->InlineForm2->templateCallback(function ($options, $data) {
				$name = Hash::get($data, 'ChildModel.name');
				return "debug_$name";
				});
		?>
		</dd>
		<?php
			$html = $this->InlineForm2->endTemplate();
			$this->assertContains('debug_inline_form2_test_child_2_name', $html);
			$this->InlineForm2->endHasManyChild();
			$this->InlineForm2->end();
		}

		public function testTemplateContainerTag() {
			$this->InlineForm2->create($this->data, 'Model');
			$this->InlineForm2->createHasManyChild('ChildModel');

			$this->InlineForm2->startTemplate(['tag' => 'article']);
		?>
		<div>Name</div>
		<div><?php echo $this->InlineForm2->control('name', ['type' => 'text']); ?></div>
		<?php
			$html = $this->InlineForm2->endTemplate();
			$this->assertContains('article', $html);
			$html = $this->InlineForm2->endHasManyChild();
			$this->assertContains('article', $html);
			$this->InlineForm2->end();
		}

		public function testOtherModelControl() {
			$this->InlineForm2->create($this->data, 'Model');
			$html = $this->InlineForm2->control('OtherModel.name', ['readonly' => true]);
			$this->InlineForm2->end();

			$this->assertContains('data-model="OtherModel"', $html);
			$this->assertContains('data-field="name"', $html);
			$this->assertContains($this->data['OtherModel']['name'], $html);
		}

		public function testAddButton() {
			$this->InlineForm2->create($this->data, 'Model');
			$this->InlineForm2->createHasManyChild('ChildModel');
			$this->InlineForm2->startTemplate();
			echo "<div>template</div>";
			$this->InlineForm2->endTemplate();
			$html = $this->InlineForm2->addButton('Add Button');
			$this->InlineForm2->endHasManyChild();

			$this->assertContains('button', $html);
			$this->assertContains('if-add', $html);
			$this->assertContains('data-model="ChildModel"', $html);
			$json = h(json_encode([
				'model_id' => 1,
			]));
			$this->assertContains("data-data=\"$json\"", $html);
			$this->assertContains('Add Button', $html);
		}

		public function testAddButtonOptions() {
			$this->InlineForm2->create($this->data, 'Model');
			$this->InlineForm2->createHasManyChild('ChildModel');
			$this->InlineForm2->startTemplate();
			echo "<div>template</div>";
			$this->InlineForm2->endTemplate();
			$html = $this->InlineForm2->addButton('Add Button', [
				'tag' => 'a',
				'href' => '#',
				'class' => 'btn btn-default',
			]);
			$this->InlineForm2->endHasManyChild();

			$this->assertContains('<a', $html);
			$this->assertContains('a>', $html);
			$this->assertContains('href="#"', $html);
			$this->assertContains('btn btn-default', $html);
		}

		public function testStandAloneAddButton() {
			$this->InlineForm2->create($this->data, 'Model');
			$html = $this->InlineForm2->addButton('Add Button', [
				'model' => 'ChildModel',
			]);

			$this->assertContains('button', $html);
			$this->assertContains('if-add', $html);
			$this->assertContains('data-model="ChildModel"', $html);
			$json = h(json_encode([
				'model_id' => 1,
			]));
			$this->assertContains("data-data=\"$json\"", $html);
			$this->assertContains('Add Button', $html);
		}

		public function testDeleteButton() {
			$this->InlineForm2->create($this->data, 'Model');
			$this->InlineForm2->createHasManyChild('ChildModel');
			$this->InlineForm2->startTemplate();
			echo "<div>template</div>";
			echo $this->InlineForm2->deleteButton('Delete Button');
			$html = $this->InlineForm2->endTemplate();

			$this->assertContains('button', $html);
			$this->assertContains('if-delete', $html);
			$this->assertContains('data-model="ChildModel"', $html);
			$this->assertContains("data-id=\"{$this->data['ChildModel'][0]['id']}\"", $html);
			$this->assertContains('Delete Button', $html);

			$html = $this->InlineForm2->endHasManyChild();
			$this->assertContains('button', $html);
			$this->assertContains('if-delete', $html);
			$this->assertContains('data-model="ChildModel"', $html);
			$this->assertContains("data-id=\"##id##\"", $html);
			$this->assertContains('Delete Button', $html);


		}

		public function testStandAloneDeleteButton() {
			$this->InlineForm2->create($this->data, 'Model');
			$html = $this->InlineForm2->deleteButton("delete button test", ['model' => 'ChildModel', 'id' => 1]);
			$this->InlineForm2->end();

			$this->assertContains('button', $html);
			$this->assertContains('data-id="1"', $html);
			$this->assertContains('data-model="ChildModel"', $html);
			$this->assertContains('delete button test', $html);
		}

		public function testTemplateCallbackOption() {
			$this->InlineForm2->create($this->data, 'Model');
			$this->InlineForm2->createHasManyChild('ChildModel');
			$this->InlineForm2->startTemplate(['callback' => function (&$options, &$data) {
				$options['data-debug'] = $data['ChildModel']['id'];
			}]);
			$html = $this->InlineForm2->endTemplate();
			$this->assertContains('if-group', $html);
			$this->assertContains("data-debug=\"{$this->data['ChildModel'][0]['id']}\"", $html);
			$html = $this->InlineForm2->endHasManyChild();
			$this->assertContains("data-debug=\"##id##\"", $html);
			$this->InlineForm2->end();
		}

		public function tearDown() {
			parent::tearDown();
		}
	}
