'use strict';
var Inflector;
(function (module) {
	module.pluralize = function(singular) {
		return singular + 's';
	};
	module.singularize = function(plural) {
		return plural.replace('/s$/', '');
	};
	module.camelize = function(underscored) {
		return underscored.replace(/(^[a-z]|_+[a-z])/g, function (match) {
			if (match.length > 1) match = match.charAt(match.length - 1);
			return match.toUpperCase();
		});
	};
	module.underscore = function(camelCase) {
		return camelCase.replace(/^[A-Z]/, function (match) {
			return match.toLowerCase(match);
		}).replace(/[A-Z]/g, function (match) {
			return '_' + match.toLowerCase(match);
		});
	}
})(Inflector || (Inflector = {}));

(function($) {
	var focus = function () {
		var control = $(this);
		var input = control.find(".if-input");
		var val = control.if("val");
		var type = control.if("type");

		control.if("ival", val);
	}; 
    var update = function (data) {
        if (data.status == "OK") {
            $(".if-control").each(function (index, element) {
                var control = $(element);
                var model = control.data("model");
                if (model in data.data) {
                    var id = control.data("id");
                    if (id === undefined || ("id" in data.data[model]) && data.data[model].id == id) {
                        var field = control.data("field");
                        if (field in data.data[model]) {
                            var val = data.data[model][field];
                            if (val != control.if("val")) {
                                control.if("val", val);
                                control.addClass("if-updated");
                            }
                        }
                    }
                }
            });
            $(".if-hasmanychild").each(function (index, element) {
                var hasmanychild = $(element);
                var model = hasmanychild.data("model");
                if ((model in data.data) && ("id" in data.data[model])) {
                    var id = data.data[model].id;
                    if (hasmanychild.find(".if-control[data-id=" + id + "]").length == 0) {
                        var template = hasmanychild.find(".if-template");
						if (template.length > 0) {
							var element = template.clone().removeClass("if-template");
							element.each(function (index, element) {
								var attributes = element.attributes;
								for (var i = 0; i < attributes.length; ++i) {
									var attribute = attributes[i];
									if (attribute.value.match(/##id##/)) {
										element.setAttribute(attribute.name, attribute.value.replace(/##id##/g, id));
									}
								}
							});
							element.html(element.html().replace(/##id##/g, id)).if();
							element.find(".if-control").each(function (index, element) {
								var control = $(element);
								control.if("val", data.data[model][control.data("field")]);
							});
							element.insertBefore(hasmanychild.find('.if-insert'));
						}
                    }
                }
            });
            $(".if-form").trigger("update.if", data);
        }
    };
	var blur = function () {
		var control = $(this).closest(".if-control");
		if (control.is(":not(.if-readonly)")) {
			var ival = control.if("ival");
            if (ival === true) {
                ival = 1;
            } else if (ival === false) {
                ival = 0;
            }
			if (ival != control.if("val")) {
				var url = control.closest(".if-form").data("url");
				var data = {
					action: "update",
					model: control.data("model"),
					field: control.data("field"),
					id: control.data("id"),
					value: ival
				};
				$.ajax({
					url: url,
					data: data,
					dataType: "JSON",
					type: "POST"
				}).done(update);
			}
		}
	};
    var add = function () {
        var button = $(this);
        var data = {
            action: "add",
            model: button.data("model"),
            init: button.data("data")
        };
        $.ajax({
            url: button.closest(".if-form").data("url"),
            data: data,
            dataType: "JSON",
            type: "POST"
        }).done(update);
    };
	var delete_ = function () {
		var button = $(this);
		var model = button.data("model");
		var id = button.data("id");
		var data = {
			action: "delete",
			model: model,
			id: id
		};
        if (button.data("confirm") !== undefined && !button.data("confirm") || confirm("Are you sure to delete?" )) {
            $.ajax({
                url: button.closest(".if-form").data("url"),
                data: data,
                dataType: "JSON",
                type: "POST"
            }).done(function (data) {
                if (data.status == "OK") {
                    $(".if-group[data-model=\"" + model + "\"][data-id=\"" + id + "\"]").remove();
                    update(data);
                }
            });
        }
	};
	var animationend = function () {
		$(this).removeClass("if-updated");
	};
    var methods = {
        type: function () {
            return this.data("type");
        },
		val: function (data) {
			var value = this.find(".if-value");
			var type = this.if("type");


			if (data === undefined) {
				if (type == "select" || type == "checkbox") {
					return this.data("value");
				} else {
					return value.text();
				}
			} else {
				if (type == "select") {
					this.data("value", data);

                    if (!data) {
                        data = "";
                    }
                    this.find(".if-value").text(text = this.find(".if-input option[value=\"" + data + "\"]").text());
				} else if (type == "checkbox") {
					data = data != "0";

					this.data("value", data);

					var text;
					if (data) {
						text = this.data("true");
					} else {
						text = this.data("false");
					}

					if (text === undefined) {
						var checkbox = this.find(".if-value input[type=checkbox]");
						if (checkbox.length > 0) {
							checkbox.prop("checked", data);
						}
					} else {
						this.find(".if-value").html(text);
					}

					this.attr("data-value", data ? 1 : 0);
                } else if (type == "multiline") {
                    if (data === null) {
                        data = "";
                    }
                    value.text(data);
                    value.html(value.text().replace(/(\r\n|\n|\r)/g, "<br>\n"));
				} else {
                    if (data === null) {
                        data = "";
                    }
					value.text(data);
				}
				return this;
			}
		},
		ival: function (data) {
			var input = this.find(".if-input");
			var type = this.if("type");

			if (data === undefined) {
				if (type == "checkbox") {
					return input.prop("checked");
				} else {
					return input.val();
				}
			} else {
				if (type == "checkbox") {
					input.prop("checked", data);
				} else {
					input.val(data);
				}
				return this;
			}
		},
        init: function () {
            var control = this.hasClass("if-control") ? this : this.find(".if-control");
			control.bind({
				"focus": focus,
				"blur": blur,
				"animationend": animationend
			});

			control.find(".if-input").bind({
				"blur": blur
			});

            this.find(".if-add").bind({
                "click": add
            });

			this.find(".if-delete").bind({
				"click": delete_
			});

            return this;
        }
    };

    $.fn.if = function(method, data) {
        if (method === undefined) {
            method = "init";
        }
        return methods[method].bind(this)(data);
    };
})(jQuery);

$(function() {
	$(document.body).if();
});
