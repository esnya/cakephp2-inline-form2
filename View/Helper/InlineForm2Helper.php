<?php
	function get_remove(&$array, $path, $default = null) {
		$value = Hash::check($array, $path) ? Hash::get($array, $path) : $default;
		$array = Hash::remove($array, $path);
		return $value;
	}

	function extract_remove(&$array, $path, $default = []) {
		$value = Hash::check($array, $path) ? Hash::extract($array, $path) : $default;
		$array = Hash::remove($array, $path);
		return $value;
	}

	function prepend_class(&$array, $className) {
		$array['class'] = array_merge([$className], Hash::extract($array, 'class'));
	}

	class ControlFactory {
		public $Html;
		public $data;
		public $model;
		public $options;
		public $vauleOptions;
		public $inputOptions;
		public $top_level;

		public function __construct($Html, $data, $model, $options = [], $valueOptions = [], $inputOptions = []) {
			$this->Html = $Html;
			$this->data = $data;
			$this->model = $model;
			$this->top_level = get_remove($options, 'top_level', false);
			$this->options = $options;
			$this->valueOptions = $valueOptions;
			$this->inputOptions = $inputOptions;
		}

		public function control($field, $options = [], $valueOptions = [], $inputOptions = []) {
			return '';
		}
	}

	class InlineForm2ControlFactory extends ControlFactory {
		private static $columnTypesCache = [];
		private static function getColumnType($model, $column) {
			$path = "$model.$column";
			if (Hash::check(InlineForm2ControlFactory::$columnTypesCache, $path)) {
				return Hash::get(InlineForm2ControlFactory::$columnTypesCache, $path);
			} else {
				try {
					$modelInstance = ClassRegistry::init($model);
					if ($modelInstance) {
						$columnTypes = $modelInstance->getColumnTypes();
						InlineForm2ControlFactory::$columnTypesCache[$model] = $columnTypes;
						return Hash::get($columnTypes, $column);
					}
				}catch (Exception $e) {
				} 
			}
		}

		public function control($field, $options = [], $valueOptions = [], $inputOptions = []) {
			$callback = get_remove($options, 'callback');
			if (is_callable($callback)) {
				$callback($options, $this->data);
			}

			$path = $field;

			$model = $this->model;
			if (strpos($path, '.') !== FALSE) {
				$tmp = explode('.', $path);
				$model = $tmp[0];
				$field = $tmp[1];
			}

			$value = Hash::get($this->data, $path);
			if ($value === null && strpos($path, '.') === FALSE) {
				$path = "{$this->model}.$path";
				$value = Hash::get($this->data, $path);
			}

			$options = array_merge($this->options, $options);

			$type = get_remove($options, 'type');
			if (!$type) {
				if (array_key_exists('options', $options)) {
					$type = 'select';
				} else {
					$columnType = InlineForm2ControlFactory::getColumnType($this->model, $field);
					$options['data-debug-columnType'] = $columnType;
					switch ($columnType) {
						case 'text': $type = 'multiline'; break;
						case 'integer': $type = 'number'; break;
						default: $type = 'text'; break;
					}
				}
			}

			$readonly = get_remove($options, 'readonly', false);
			$containerTag = get_remove($options, 'tag', 'div');
			$true_html = get_remove($options, 'true');
			$false_html = get_remove($options, 'false');
			if ($readonly) {
				prepend_class($options, 'if-readonly');
			}
			if ($type == 'select') {
				$empty = get_remove($options, 'empty', false);
				if ($empty && !is_string($empty)) {
					$empty = __('(No Select)');
				}

				$selectOptions = extract_remove($options, 'options');

				if (array_key_exists($value, $selectOptions)) {
					$valueText = $selectOptions[$value];
				} else if ($empty) {
					$valueText = $empty;
				} else {
					$valueText = $value;
				}
			} else if ($type == 'checkbox') {
				if ($value == TRUE) {
					$valueText = ($true_html) ? $true_html : $this->Html->tag('input', null, ['type' =>'checkbox', 'onclick' => 'return false', 'checked' => 'checked']);
				} else {
					$valueText = ($false_html) ? $false_html : $this->Html->tag('input', null, ['type' =>'checkbox', 'onclick' => 'return false']);
				}
			} else if ($type == 'multiline') {
				$valueText = nl2br(h($value));
			} else {
				$valueText = h($value);
			}
			prepend_class($options, 'if-control');

			if (!$readonly) {
				$options['tabindex'] = 0;
			}
			$options['data-model'] = $model;
			$options['data-field'] = $field;
			$options['data-type'] = $type;
			if (!$this->top_level || $this->model == $model) {
				$options['data-id'] = Hash::get($this->data, "{$this->model}.id");
			}

			if ($type == 'select') {
				$options['data-value'] = $value;
			} else if ($type == 'checkbox') {
				$options['data-value'] = $value;
			}

			if ($true_html) {
				$options['data-true'] = $true_html;
			}
			if ($false_html) {
				$options['data-false'] = $false_html;
			}


			ob_start();

			if (!$readonly) {
				$inputOptions = array_merge($this->inputOptions, $inputOptions);

				$InputTagTable = [
				'text' => 'input',
				'number' => 'input',
				'multiline' => 'textarea',
				'checkbox' => 'input',
				'select' => 'select',
				];
				$inputTag = get_remove($inputOptions, 'tag', $InputTagTable[$type]);
				if ($type == 'multiline') {
				} else if ($type == 'select') {
				} else {
					$inputOptions['type'] = $type;
				}

				if ($type != 'multiline') {
					$inputOptions['value'] = $value;
				}
				prepend_class($inputOptions, 'if-input');

				if ($type == 'checkbox') {
					if ($value == TRUE) {
						$inputOptions['checked'] = 'checked';
					}
				}

				if ($type == 'select') {
					echo $this->Html->tag($inputTag, null, $inputOptions);
					if ($empty) {
						echo $this->Html->tag('option', $empty, ['value' => '']);
					}
					foreach ($selectOptions as $key => $option) {
						echo $this->Html->tag('option', $option, ['value' => $key]);
					}
					echo $this->Html->tag("/$inputTag");
				} else if ($type == 'multiline') {
					echo $this->Html->tag($inputTag, $valueText, $inputOptions);
				} else {
					echo $this->Html->tag($inputTag, null, $inputOptions);
				}
			}

			$valueOptions = array_merge($this->valueOptions, $valueOptions);
			$valueTag = get_remove($valueOptions, 'tag', 'div');
			prepend_class($valueOptions, 'if-value');
			echo $this->Html->tag($valueTag, $valueText, $valueOptions);

			return $this->Html->tag($containerTag, ob_get_clean(), $options);
		}

		public function deleteButton($text, $options = []) {
			$tag = get_remove($options, 'tag', 'button');
			$model = get_remove($options, 'model', $this->model);
			$id = get_remove($options, 'id', null);
			if ($id === null) {
				$id = Hash::get($this->data, "{$model}.id");
			}

			$options['data-model'] = $model;
			$options['data-id'] = $id;
			prepend_class($options, 'if-delete');

			return $this->Html->tag($tag, $text, $options);
		}

	}

	class HasManyChildControlFactory extends ControlFactory {
		public $containerTag;
		public $containerOptions = [];

		public $callbacks = [];
		public function control($field, $options = [], $valueOptions = [], $inputOptions = []) {
			$callback = get_remove($options, 'callback');
			if (is_callable($callback)) {
				$options['callback'] = $this->append_callback($callback);
			}
			return '<hasmanychild>' . serialize([
				'field' => $field,
				'options' => array_merge($this->options, $options),
				'valueOptions' => array_merge($this->valueOptions, $valueOptions),
				'inputOptions' => array_merge($this->inputOptions, $inputOptions),
			]) . '</hasmanychild>';
		}

		public function deleteButton($text, $options = []) {
			return '<deletebutton>' . serialize([
				'text' => $text,
				'options' => $options,
			]) . '</deletebutton>';
		}

		public function append_callback($callback) {
			$index = count($this->callbacks);
			$this->callbacks[] = $callback;
			return $index;
		}

		public function execute_callbacks($html, $data) {
			return preg_replace_callback('/##callback:([0-9]+)##/', function ($matches) use($data) {
				return $this->callbacks[0+$matches[1]]($this->options, $data);
			}, $html);
		}

		public function restore_options(&$options) {
			$callback = Hash::get($options, 'callback');
			if ($callback !== null) {
				$options['callback'] = $this->callbacks[$callback];
			}
		}
	}

	class InlineForm2Helper extends AppHelper {
		public $helpers = array('Html');

		private $script_css_written = false;
		private $factory = null;
		private $factoryStack = [];

		private $tag;

		private $form;
		private $forms = [];

		private $table;
		private $tables = [];

		private $hasManyChildModel;
		private $template;
		private $templateOptions = [];
		private $templateModel;

		public function create($data, $model, $options = [], $controlOptions = [], $valueOptions = [], $inputOptions = []) {
			if (!$this->script_css_written) {
				$this->Html->script('InlineForm2.inlineform', ['inline' => false]);
				$this->Html->css('InlineForm2.inlineform', ['inline' => false]);
				$this->script_css_written = true;
			}

			$this->tag = get_remove($options, 'tag', 'div');
			$url = extract_remove($options, 'url');
			if (!$url) {
				$url = ['controller' => Inflector::underscore(Inflector::pluralize($model)),  'action' => 'inlineform2'];
			}
			if (is_array($url)) {
				$url = $this->Html->url($url);
			}

			$this->factoryStack[] = $this->factory;
			$this->factory = new InlineForm2ControlFactory($this->Html, $data, $model, Hash::insert($controlOptions, 'top_level', true), $valueOptions, $inputOptions);

			$options['data-url'] = $url;
			prepend_class($options, 'if-form');

			return $this->Html->tag($this->tag, null, $options);
		}

		public function control($field, $options = [], $valueOptions = [], $inputOptions = []) {
			return $this->factory->control($field, $options, $valueOptions, $inputOptions);//$this->form->control($field, $options, $valueOptions, $inputOptions);
		}

		public function end() {
			$html = $this->Html->tag('/' . $this->tag);
			$this->factory = array_pop($this->factoryStack);
			return $html;
		}

		public function createTable($model, $fields, $options = []) {
			if ($this->readonly) $options['readonly'] = true;
			$options = Hash::insert($options, 'url', Hash::get($this->form->options, 'data-url'));
			$options = Hash::insert($options, 'form', Hash::merge($this->form->originalOptions, Hash::extract($options, 'form')));
			$this->table = new Table2($this->Html, $this->form->data, $model, $fields, $options);
			array_push($this->tables, $this->table);
			return $this->table->create();
		}

		public function thead() {
			return $this->table->thead();
		}

		public function tbody() {
			return $this->table->tbody();
		}

		public function tfoot() {
			return $this->table->tfoot();
		}

		public function endTable() {
			$tmp = array_pop($this->tables);
			$this->table = end($this->tables);
			return $tmp->end();
		}

		public function createSimpleTable($model, $fields, $options = []) {
			return "{$this->createTable($model, $fields, $options)}<thead>{$this->thead()}</thead><tbody>{$this->tbody()}</tbody><tfoot>{$this->tfoot()}</tfoot>{$this->endTable()}";
		}

		public function createHasManyChild($model, $options = []) {
			$tag = get_remove($options, 'tag', 'div');
			prepend_class($options, 'if-hasmanychild');

			$data = $this->factory->data;
			$controlOptions = $this->factory->options;
			$valueOptions = $this->factory->valueOptions;
			$inputOptions = $this->factory->inputOptions;

			$this->factoryStack[] = $this->factory;
			$this->factory = new HasManyChildControlFactory($this->Html, $data, $model, $controlOptions, $valueOptions, $inputOptions);
			$this->factory->tag = $tag;

			$options['data-model'] = $model;

			return $this->Html->tag($tag, null, $options);
		}

		public function startTemplate($options = []) {
			$this->factory->containerOptions = $options;
			$this->factory->containerTag = get_remove($this->factory->containerOptions, 'tag', 'div');
			ob_start();
		}

		public function templateCallback($callback) {
			return "##callback:{$this->factory->append_callback($callback)}##";
		}

		public function endTemplate() {
			$this->template = ob_get_clean();

			ob_start();

			foreach(Hash::extract($this->factory->data, $this->factory->model) as $data) {
				$options = $this->factory->containerOptions;
				$options['data-model'] = $this->factory->model;
				$options['data-id'] = Hash::get($data, 'id');
				prepend_class($options, 'if-group');

				$data = [$this->factory->model => $data];

				$callback = get_remove($options, 'callback');
				if (is_callable($callback)) {
					$callback($options, $data);
				}

				echo $this->Html->tag($this->factory->containerTag, null, $options);

				$factory = new InlineForm2ControlFactory($this->Html, $data, $this->factory->model, $this->factory->options, $this->factory->valueOptions, $this->factory->inputOptions);

				$template = $this->template;
				$template = preg_replace_callback("|<hasmanychild>(.*)</hasmanychild>|", function ($matches) use($factory) {
					$args = unserialize($matches[1]);
					$field = $args['field'];
					$options = $args['options'];
					$this->factory->restore_options($options);
					$valueOptions = $args['valueOptions'];
					$inputOptions = $args['inputOptions'];
					return $factory->control($field, $options, $valueOptions, $inputOptions);
				}, $template);
				$template = preg_replace_callback("|<deletebutton>(.*)</deletebutton>|", function ($matches) use($factory) {
					$args = unserialize($matches[1]);
					return $factory->deleteButton($args['text'], $args['options']);
				}, $template);

				echo $this->factory->execute_callbacks($template, $factory->data);

				echo $this->Html->tag('/' . $this->factory->containerTag);
			}

			echo $this->Html->tag($this->factory->containerTag, '', ['class' => 'if-insert if-hidden']);

			return ob_get_clean();
		}

		public function addButton($text, $options = []) {
			$tag = get_remove($options, 'tag', 'button');

			prepend_class($options, 'if-add');

			$model = get_remove($options, 'model');
			if (!$model) {
				$model = $this->factory->model;

				$parentModel = end($this->factoryStack)->model;
				$field = Inflector::underscore($parentModel) . '_id';
				$id = Hash::get($this->factory->data, "$parentModel.id");
			} else {
				$field = Inflector::underscore($this->factory->model) . '_id';
				$id = Hash::get($this->factory->data, "{$this->factory->model}.id");
			}

			$options['data-model'] = $model;

			$data = [$field => $id];
			$options['data-data'] = json_encode($data);

			return $this->Html->tag($tag, $text, $options);
		}

		public function deleteButton($text, $options = []) {
			return $this->factory->deleteButton($text, $options);
		}

		public function endHasManyChild() {
			ob_start();

			$templateTag = ($this->factory->containerTag) ? $this->factory->containerTag : 'div';
			$templateOptions = $this->factory->containerOptions;
			prepend_class($templateOptions, 'if-group');
			prepend_class($templateOptions, 'if-template');
			$templateOptions['data-model'] = $this->factory->model;
			$templateOptions['data-id'] = '##id##';

			$dummyData = [$this->factory->model => ['id' => '##id##']];

			$callback = get_remove($templateOptions, 'callback');
			if (is_callable($callback)) {
				$callback($templateOptions, $dummyData);
			}
			echo $this->Html->tag($templateTag, null, $templateOptions);

			$factory = new InlineForm2ControlFactory($this->Html, $dummyData, $this->factory->model, $this->factory->options, $this->factory->valueOptions, $this->factory->inputOptions);

			$template = $this->template;
			$template = preg_replace_callback("|<hasmanychild>(.*)</hasmanychild>|", function ($matches) use($factory) {
				$args = unserialize($matches[1]);
				$field = $args['field'];
				$options = $args['options'];
				$this->factory->restore_options($options);
				$valueOptions = $args['valueOptions'];
				$inputOptions = $args['inputOptions'];
				$path = (strpos($args['field'], '.') === FALSE) ? "{$factory->model}.{$args['field']}" : $args['field'];
				$factory->data = Hash::insert($factory->data, $path, '##hasmanychild-data##');
				return $factory->control($field, $options, $valueOptions, $inputOptions);
			}, $template);
			$template = preg_replace_callback("|<deletebutton>(.*)</deletebutton>|", function ($matches) use($factory) {
				$args = unserialize($matches[1]);
				return $factory->deleteButton($args['text'], $args['options']);
			}, $template);

			echo $this->factory->execute_callbacks($template, $factory->data);

			echo "</$templateTag>";

			$tag = $this->factory->tag;
			$this->factory = array_pop($this->factoryStack);

			echo $this->Html->tag("/$tag");

			return ob_get_clean();
		}
	}
