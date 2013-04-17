/*
 * ObjectPicker [Refs #617]
 *
 * Copyright (C) 2011 Nethesis S.r.l.
 */
(function( $ ) {
    var SUPER = $.nethgui.Component;
    $.widget('nethgui.ObjectPicker', SUPER, {
        _deep: false,
        _create: function() {
            SUPER.prototype._create.apply(this);

            var self = this;

            var objectsContainer = this.element.children('.Objects').first();
            var addButton = this.element.find('.searchbox button').first().Button().Button('enable');
            var inputField = this.element.find('.searchbox input.TextInput').first().TextInput().TextInput('enable');

            this._initializeDeep([addButton, inputField]);

            this._metadata = $.parseJSON(this.element.children('input.metadata').attr('value'));
            this._selection = {};
            this._schema = this.element.children('.schema').first().children();
            this._inputField = inputField;
            this._objectsContainer = objectsContainer;

            inputField.autocomplete({
                source: [],
                minLength: 0,
                select: function (event, ui) {
                    self.showObject(ui.item.value);
                }
            }).bind('keydown.' + this.namespace, function(event) {
                if(event.keyCode == $.ui.keyCode.ENTER) {
                    self.showObject(inputField.val());
                    inputField.autocomplete('close');
                    return false;
                }
                return true;
            });

            objectsContainer.bind('nethguiupdateview.' + this.namespace, function(event, value, source) {   
                self._objects = value;
                self.refresh();
            });

            addButton.click(function () {
                self.showObject(inputField.val());
            });

        },
        _updateView: function(value, source) {
            if($.isArray(value)) {
                this._selection[source] = value;
            } else {
                this._selection[source] = [];
            }
            this.refresh();
        },
        refresh: function() {
            var objects = this._objects;
            var selection = this._selection;
            var schema = this._schema;
            var metadata = this._metadata;
            var self = this;

            var objectsNode = this.element.children('.Objects');

            var autocompleteChoices = [];

            // An object is in "selected" state if any of its properties is selected:
            var isSelected = function (id) {
                var found = [];
                for(var r in selection) {
                    if(!$.isArray(selection[r])) {
                        continue;
                    }
                    if($.inArray(objects[id][metadata.value], selection[r]) >= 0) {
                        found.push(r);
                    }
                }

                if(found.length > 0) {
                    return found;
                }

                return false;
            };

            if(objectsNode.children('ul').length == 0) {
                objectsNode.append('<ul />');
            } else {
                objectsNode.children('ul').empty();
            }

            var ul = objectsNode.children('ul');

            var initializeObjectWidgets = function (id, labelText, widgets) {
                var labelElement;
                var selected = isSelected(id);
                var closeButton = $('<button type="button" />').text('Remove').button({
                    text: false,
                    icons:{
                        primary: 'ui-icon-close'
                    }
                }).bind('click.' + this.namespace, function () {
                    self.hideObject(objects[id][metadata.value]);
                });

                if(metadata.url !== false) {
                    labelElement = $('<a />', {
                        'class':'label',
                        'href': objects[id][metadata.url]
                    });
                } else {
                    labelElement = $('<span />', {
                        'class': 'label'
                    });
                }

                labelElement.text(labelText);

                widgets.find(':checkbox').each(function(index, element) {
                    // update id, name, for attributes, appending the proper element ID suffix:
                    var $element = $(element);

                    var elementId = $element.attr('id');
                    var elementName = $element.attr('name');
                    var eventTarget = $element.attr('class').split(' ').pop();

                    if(elementId !== undefined) {
                        $element.attr('id', elementId + '_' + id);
                    }
                    if(elementName !== undefined) {
                        $element.attr('name', elementName + '[' + id + ']');
                    }

                    $element.attr('value', objects[id][metadata.value]);
                    $element.removeAttr('class');

                    if(self._isSelector($element)) {
                        $element.prop('checked', selected !== false);
                        $element.hide();
                        $element.next('label').remove();
                    } else {
                        $element.prop('checked', $.isArray(selected) ? $.inArray(eventTarget, selected) >= 0 : false);
                        //$element.prop('checked', elementName !== undefined && selected === elementName.substring(elementName.lastIndexOf('[') + 1, elementName.lastIndexOf(']')))
                        $element.next('label').attr('for', $element.attr('id'));

			// FIXME: this may cause a memory leak!
			self._initializeDeep([$element.get(0), $element.next('label').get(0)]);
                    }

                    // disable the checkbox, if not in "selected" state
                    if(selected === false) {
                        $element.prop('disabled', 'disabled');
                    } else {
                        $element.prop('disabled', false);
                    }

                });

                return $('<li />', {
                    'style': selected ? '' : 'display: none'
                }).append(labelElement).append($('<div class="checkboxset"></div>').append(widgets).append(closeButton));
            }

            // cycle through all objects, cloning the widget schema and preparing the
            // auto-complete values:
            for(var i in objects) {
                var fragment = initializeObjectWidgets(i, objects[i][metadata.label], schema.clone());
                ul.append(fragment);
                //fragment.find(':checkbox').button();
                //fragment.find('.checkboxset').buttonset();
                autocompleteChoices.push({
                    value: objects[i][metadata.value],
                    label: objects[i][metadata.label]
                });
            }

            this._inputField.autocomplete('option', 'source', autocompleteChoices);
        },
        showObject: function(value) {
            //debug('showObject', value);
            var self = this;
            this._objectsContainer.find('[value="' + value + '"]').each(function () {
                $(this).prop('disabled', false)
                if(self._isSelector($(this))) {
                    $(this).prop('checked', 'checked');
                }
                $(this).parents('li').fadeIn();
                self._inputField.val('');
            });
        },
        hideObject: function(value) {
            //debug('hideObject', value);
            var self = this;
            this._objectsContainer.find('[value="' + value + '"]').each(function () {
                $(this).prop('disabled', 'disabled');
                if(self._isSelector($(this))) {
                    $(this).prop('checked', false);
                }
                $(this).parents('li').hide();
            });
        },
        _isSelector: function (node, checked) {
            var selector = this._metadata.selector;

            if(this._selectorRegExp === undefined && typeof selector == 'string' && selector) {
                this._selectorRegExp = new RegExp('\\[' + selector + '\\]');
            }

            if(this._selectorRegExp !== undefined)  {
                return this._selectorRegExp.test(node.attr('name'));
            }

            return false;
        }
    });
}( jQuery ));
