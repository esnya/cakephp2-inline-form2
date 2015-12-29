<!DOCTYPE html>
<html lang="ja">
	<head>
		<meta charset="UTF-8">
		<title>InlineForm2 QUnit Tests</title>
		<link rel="stylesheet" href="//code.jquery.com/qunit/qunit-1.15.0.css">
		<link rel="stylesheet" href="css/inlineform.css">
		<link rel="stylesheet" href="../css/bootstrap.min.css">
	</head>
	<body>

	</body>
</html>
<div id="qunit"></div>
<div id="qunit-fixture"></div>

<div id="TestBlock">
	<h1>Test Form</h1>
	<div id="Form1" class="if-form" data-url="test.json">
		<div id="Control1" class="if-control" data-model="Model" data-type="text" data-field="name" data-id="1" tabindex="0">
			<input class="if-input form-control" type="text" value="name of model">
			<div class="if-value">name of model</div>
		</div>
		<div id="Control2" class="if-control if-readonly" data-type="number" data-model="Model" data-field="age" data-id="1">
			<div class="if-value">12</div>
		</div>
		<div id="Control3" class="if-control" data-model="Model" data-type="number" data-field="age" data-id="1" tabindex="0">
			<input class="if-input form-control" type="number" value="12">
			<div class="if-value">12</div>
		</div>
		<div id="Control4" class="if-control" data-model="Model" data-type="checkbox" data-field="flag1" data-id="1" data-value="1" tabindex="0">
			<input class="if-input form-control" type="checkbox" checked>
			<div class="if-value"><input type="checkbox" onclick="return false" checked></div>
		</div>
		<div id="Control5" class="if-control" data-model="Model" data-type="checkbox" data-field="flag2" data-id="1" data-value="1" tabindex="0" data-true="True" data-false="False">
			<input class="if-input form-control" type="checkbox" checked>
			<div class="if-value">True</div>
		</div>
		<div id="Control6" class="if-control" data-model="Model" data-type="select" data-field="model_parent_id" data-id="1" data-value="1" tabindex="0">
			<select class="if-input form-control">
				<option value="">Empty</option>
				<option value="1">Parent1</option>
				<option value="2">Parent2</option>
				<option value="3">Parent3</option>
				<option value="4">Parent4</option>
			</select>
			<div class="if-value">Parent1</div>
		</div>
		<div id="Control7" class="if-control if-readonly" data-type="text" data-model="OtherModel" data-field="name">
			<div class="if-value">name of model</div>
		</div>
		<div id="Control8" class="if-control" data-model="Model" data-type="multiline" data-field="memo" data-id="1" tabindex="0">
			<textarea class="if-input form-control">memo
				memo</textarea>
			<div class="if-value">memo<br>
				memo</div>
		</div>
		<div id="Control9" class="if-control" data-model="Model" data-type="checkbox" data-field="flag3" data-id="1" data-value="1" tabindex="0" data-true="TRUE" data-false="FALSE">
			<input class="if-input form-control" type="checkbox" checked>
			<div class="if-value">TRUE</div>
		</div>

	</div>

	<h1>Test Form For Add</h1>
	<div id="Form2" class="if-form" data-url="test_add.json">
		<div class="if-hasmanychild" data-model="ChildModel">
			<div class="if-insert if-hidden"></div>
			<div class="if-template if-group" data-model="ChildModel" data-id="##id##">
				<div class="if-control if-readonly" data-id="##id##" data-type="text" data-field="name" data-model="ChildModel">
					<div class="if-value">##hasmanychild-data##</div>
				</div>
			</div>
			<button id="AddButton1" class="if-add" data-model="ChildModel" data-data="{&quot;model_id&quot;: 1}">Add Child Model</button>
		</div>
	</div>

	<h1>Test Form 3</h1>
	<div id="Form3" class="if-form" data-url="test.json">
		<a href="#" onclick="return false" class="if-control if-readonly" data-model="Model" data-type="text" data-field="name" data-id="1" tabindex="0">
			<div class="if-value">name of model</div>
		</a>
	</div>

	<h1>Test Form 4 For Delete</h1>
	<div id="Form4" class="if-form" data-url="test_delete.json">
		<div class="if-hasmanychild" data-model="ChildModel">
			<div class="if-group" data-model="ChildModel" data-id="5">
				<div class="if-control if-readonly" data-id="5" data-type="text" data-field="name" data-model="ChildModel">
					<div class="if-value">child model name 5</div>
				</div>
				<button id="DeleteButton1" class="if-delete" data-id="5" data-model="ChildModel" data-confirm="0">
					Delete Test
				</button>
			</div>
		</div>
	</div>
</div>

<script src="//code.jquery.com/jquery-2.1.1.min.js"></script>
<script src="../js/bootstrap.min.js"></script>
<script src="js/inlineform.js"></script>
<script src="//code.jquery.com/qunit/qunit-1.15.0.js"></script>
<script src="js/test.js"></script>
