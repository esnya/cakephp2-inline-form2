"use strict";
QUnit.test("can focus", function (assert) {
	var target = $("#Form1 .if-control:not(.if-readonly)")
	assert.ok(target.length > 0, "Get Target");
	target.each(function (index, element) {
		element.focus();
		assert.equal(document.activeElement, element, "Focused");
	});
});

QUnit.test("readonly cannot focus", function (assert) {
	var target = $("#Form1 .if-control.if-readonly")
	assert.ok(target.length > 0, "Get Target");
	target.each(function (index, element) {
		element.focus();
		assert.notEqual(element, document.activeElement, "UnFocused(" + index + ")");
	});
});

QUnit.test("has value", function (assert) {
	var target = $("#Form1 .if-control")
	assert.ok(target.length > 0, "Get Target");
	target.each(function (index, element) {
		assert.equal($(element).find(".if-value").length, 1, "HasValue(" + index + ")");
	});
});

QUnit.test("has input", function (assert) {
	var target = $("#Form1 .if-control:not(.if-readonly)")
	assert.ok(target.length > 0, "Get Target");
	target.each(function (index, element) {
		assert.equal($(element).find(".if-input").length, 1, "HasInput(" + index + ")");
	});
});

QUnit.test("readonly has no input", function (assert) {
	var target = $(".if-control.if-readonly")
	assert.ok(target.length > 0, "Get Target");
	target.each(function (index, element) {
		assert.equal($(element).find(".if-input").length, 0, "NoInput(" + index + ")");
	});
});

QUnit.test("focused input value", function (assert) {
	var target = $("#Control1")
	assert.equal(target.length, 1, "Get Target");
	var value = target.find(".if-value");
	assert.equal(value.length, 1, "Get Value");
	var input = target.find(".if-input");
	assert.equal(input.length, 1, "Get Input");

	target[0].focus();
	assert.equal(input.val(), value.text());

	target[0].blur();
	value.text("foobar_name");
	target[0].focus();
	assert.equal(input.val(), value.text());
	target[0].blur();
});

QUnit.test("if.type", function (assert) {
	assert.equal($("#Control1").if("type"), "text");
	assert.equal($("#Control3").if("type"), "number");
});

QUnit.test("checkbox", function (assert) {
	var target = $("#Control4")
	assert.equal(target.length, 1, "Get Target");
	var value = target.find(".if-value");
	assert.equal(value.length, 1, "Get Value");
	var input = target.find(".if-input");
	assert.equal(input.length, 1, "Get Input");

	target[0].focus();
	assert.equal(input.prop("checked"), target.data("value"));
	target[0].blur();

	target.data("value", !target.data("value"));
	value.find("input[type=checkbox]").prop("checked", false);

	target[0].focus();
	assert.equal(input.prop("checked"), target.data("value"));
	target[0].blur();
});

QUnit.test("checkbox_custom", function (assert) {
	var target = $("#Control9")
	assert.equal(target.length, 1, "Get Target");
	var value = target.find(".if-value");
	assert.equal(value.length, 1, "Get Value");

	target.if("val", false);

	target.if("val", true);
	assert.equal(value.text(), "TRUE");
	assert.equal(target.attr('data-value'), true);

	target.if("val", false);
	assert.equal(value.text(), "FALSE");
	assert.equal(target.attr('data-value'), false);
});


QUnit.asyncTest("update", function (assert) {
	var target = $("#Control1")
	var value = target.find(".if-value");
	var input = target.find(".if-input");

    target[0].focus();
    $("#Control1").if("val", "hogehogehoge");
    $("#Control2").if("val", 12);
    $("#Control3").if("val", 12);
    $("#Control4").if("val", true);
    target[0].blur();

    expect(4);

    var callback;
   
    callback = function () {
        $(".if-form").unbind("update.if", callback);
        assert.equal($("#Control1").if("val"), "name_of_model_hogehoge");
        assert.equal($("#Control2").if("val"), 223);
        assert.equal($("#Control3").if("val"), 223);
        assert.equal($("#Control4").if("val"), false);
        QUnit.start();
    };

    $(".if-form").bind("update.if", callback);
});

QUnit.asyncTest("update from input", function (assert) {
	var target = $("#Control1")
	var value = target.find(".if-value");
	var input = target.find(".if-input");

    target[0].focus();
    $("#Control1").if("val", "hogehogehoge");
    $("#Control2").if("val", 12);
    $("#Control3").if("val", 12);
    $("#Control4").if("val", true);
    input[0].focus();
    input[0].blur();

    expect(5);

    var callback;
   
    callback = function () {
        $(".if-form").unbind("update.if", callback);
        assert.equal($("#Control1").if("val"), "name_of_model_hogehoge");
        assert.equal($("#Control2").if("val"), 223);
        assert.equal($("#Control3").if("val"), 223);
        assert.equal($("#Control4").if("val"), false);
        assert.equal($("#Control7").if("val"), "name_of_other_model_hogehoge");
        QUnit.start();
    };

    $(".if-form").bind("update.if", callback);
});

QUnit.test("if.val", function (assert) {
	var target = $("#Control1")
	var value = target.find(".if-value");
	var input = target.find(".if-input");

    value.text("test_if.val_name");
    assert.equal(target.if("val"), "test_if.val_name");

    target.if("val", "test_if.val_name2")
    assert.equal(target.if("val"), "test_if.val_name2");
});

QUnit.test("if.val checkbox", function (assert) {
	var target = $("#Control4")
	var value = target.find(".if-value");
	var input = target.find(".if-input");
    var checkbox = value.find("input[type=checkbox]");

    target.if("val", true);
    assert.equal(checkbox.prop("checked"), true);
    target.if("val", false);
    assert.equal(checkbox.prop("checked"), false);
    target.if("val", true);
    assert.equal(checkbox.prop("checked"), true);
});

QUnit.test("if.val custom-checkbox", function (assert) {
	var target = $("#Control5")
	var value = target.find(".if-value");
	var input = target.find(".if-input");

    target.if("val", true);
    assert.equal(value.text(), "True");
    target.if("val", false);
    assert.equal(value.text(), "False");
    target.if("val", true);
    assert.equal(value.text(), "True");
});

QUnit.test("if.val select", function (assert) {
	var target = $("#Control6")
	var value = target.find(".if-value");
	var input = target.find(".if-input");

    target.if("val", 1);
    assert.equal(value.text(), "Parent1");
    target.if("val", 2);
    assert.equal(value.text(), "Parent2");
    target.if("val", null);
    assert.equal(value.text(), "Empty");
});

QUnit.test("if.val belongsTo/hasOne readonly", function (assert) {
	var target = $("#Control7")
	var value = target.find(".if-value");
	var input = target.find(".if-input");

    target.if("val", "changed1");
    assert.equal(value.text(), "changed1");
    target.if("val", "changed2");
    assert.equal(value.text(), "changed2");
    target.if("val", null);
    assert.equal(value.text(), "");
});

QUnit.test("if.val null", function (assert) {
	var target = $("#Control1")
	var value = target.find(".if-value");
	var input = target.find(".if-input");

    target.if("val", null)
    assert.equal(value.text(), "");
});

QUnit.test("if.val multiline", function (assert) {
	var target = $("#Control8")
	var value = target.find(".if-value");
	var input = target.find(".if-input");

    value.html("hoge<br>\nhoge");
    assert.equal(target.if("val"), "hoge\nhoge");

    target.if("val", "hoge\nhoge")
    assert.equal(value.html(), "hoge<br>\nhoge");
});

QUnit.asyncTest("add", function (assert) {
    expect(6);

    var callback;
   
    callback = function (e, data) {
        $("#Form2").unbind("update.if", callback);

        assert.equal(data.data.ChildModel.id, 11);
        assert.equal(data.data.ChildModel.model_id, 1);

		var group = $("#Form2 .if-group:not(.if-template)[data-id=11]");
        assert.equal(group.length, 1);
        var control = group.find(".if-control[data-field=name]");
        assert.equal(control.length, 1);
        assert.equal(control.data("id"), 11);
        assert.equal(control.if("val"), "name_of_child_model_11_hogehoge");

        QUnit.start();
    };

    $("#Form2").bind("update.if", callback);
    $("#AddButton1").trigger("click");
});

QUnit.asyncTest("don't update with selectable readonly", function (assert) {
    expect(1);
   
	var flag = true;
    var callback = function (e, data) {
        $("#Form3").unbind("update.if", callback);
		flag = false;
    };

    $("#Form3").bind("update.if", callback);
    $("#Form3 .if-control")[0].focus();
    $("#Form3 .if-control")[0].blur();

	setTimeout(function() {
		assert.ok(flag);
		QUnit.start();
	}, 500);
});

QUnit.asyncTest("delete", function (assert) {
    expect(1);

    var callback;
   
    callback = function (e, data) {
        $("#Form4").unbind("update.if", callback);

        var group = $("#Form4").find(".if-group[data-id=5]");
        assert.equal(group.length, 0);

        QUnit.start();
    };

    $("#Form4").bind("update.if", callback);
    $("#DeleteButton1").trigger("click");
});
