<?php
	class InlineForm2Component extends Component {
		private function getDefaultModel($controller) {
			$defaultModelName = Inflector::singularize($controller->name);
			return $controller->{$defaultModelName};
		}
		private function getModel($controller, $modelName) {
			$model = $controller->{$modelName};

			if ($model == null) {
				$model = $this->getDefaultModel($controller)->{$modelName};
			}

			if ($model == null) {
				throw new BadRequestException('Model Not Found');
			}

			return $model;
		}

		private function setResult($controller, $model, $id) {
			$result = [
				'status' => 'OK',
				'data' => $model->find('first', [
					'conditions' => ["{$model->name}.id" => $id,],
				]),
			];
			$controller->set('data', $result);
			$controller->set('_serialize', 'data');
		}

		private function auth($controller, $model, &$defaultId = null) {
			$user_id = $controller->Auth->user('id');
			$defaultModel = $this->getDefaultModel($controller);
			if ($model == $defaultModel) {
				$defaultModelId = $model->id;
				if ($model->field('user_id') != $user_id) {
					throw new ForbiddenException('Permission Denied');
				}
			} else {
				$underscore = Inflector::underscore($defaultModel->name);
				$defaultModelId = $model->field("{$underscore}_id");
				if ($defaultModel->field('user_id', ["{$defaultModel->name}.id" => $defaultModelId]) != $user_id) {
					throw new ForbiddenException('Permission Denied');
				}
			}
			return $user_id;
		}

		public function update($controller) {
			$controller->layout = 'default';

			$data = $controller->data;
			$action = Hash::get($data, 'action');
			if ($action != 'update') {
				throw new MethodNotAllowedException('Invalid Action');
			}

			$model = $this->getModel($controller, Hash::get($data, 'model'));

			$id = Hash::get($data, 'id');
			$model->id = $id;

			$user_id = $this->auth($controller, $model);

			$model->set('user_id', $user_id);

			$field = Hash::get($data, 'field');
			$value = Hash::get($data, 'value');
			$model->set($field, $value);
			$model->save();

			$this->setResult($controller, $model, $id);
		}

		public function delete($controller) {
			$controller->layout = 'default';

			$data = $controller->data;
			$action = Hash::get($data, 'action');
			if ($action != 'delete') {
				throw new MethodNotAllowedException('Invalid Action');
			}

			$model = $this->getModel($controller, Hash::get($data, 'model'));

			$id = Hash::get($data, 'id');
			$model->id = $id;

			$this->auth($controller, $model, $defaultModelId);

			$model->delete($id, false);

			$this->setResult($controller, $this->getDefaultModel($controller), 25); //$defaultModelId);
		}

		public function add($controller) {
			$controller->layout = 'default';

			$data = $controller->data;
			$action = Hash::get($data, 'action');
			if ($action != 'add') {
				throw new MethodNotAllowedException('Invalid Action');
			}

			$model = $this->getModel($controller, Hash::get($data, 'model'));

			$user_id = $controller->Auth->user('id');
			$defaultModel = $this->getDefaultModel($controller);
			$underscore = Inflector::underscore($defaultModel->name);
			if ($defaultModel->field("user_id", ['id' => Hash::get($data, "init.{$underscore}_id")]) != $user_id) {
				throw new ForbiddenException('Permission Denied');
			}

			$model->create();
			$model->set(Hash::extract($data, 'init'));
			$model->save();

			$this->setResult($controller, $model, $model->getLastInsertId());
		}

		public function inlineform2($controller) {
			$data = $controller->data;
			$action = Hash::get($data, 'action');

			if ($action == 'update') {
				return $this->update($controller);
			} else if ($action == 'add') {
				return $this->add($controller);
			} else if ($action == 'delete') {
				return $this->delete($controller);
			} else {
				throw MethodNotAllowedException('Invalid Action');
			}
		}

	}
