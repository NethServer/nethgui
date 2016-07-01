/*
 * Selector
 *
 * Copyright (C) 2011 Nethesis S.r.l.
 */
(function( $ ) {
    var SUPER = $.nethgui.Component;
    $.widget('nethgui.Selector', SUPER, {
        _deep: false,
        _create: function() {
            SUPER.prototype._create.apply(this);
            
            var cssClasses = this.element.attr('class').split(/\s+/);

            this._datasourceTarget = cssClasses.pop();
            this._valueTarget = cssClasses.pop();
            this._mode = (this.element.prop('tagName').toUpperCase() == 'SELECT') ? 'dropdown' : 'list';
            this._selection = [];
            this._multiple = this.element.hasClass('multiple');
            this._meta = this.element.children('input[type="hidden"]').first();
        },
        _setOption: function( key, value ) {
            SUPER.prototype._setOption.apply( this, arguments );
            if(key === 'disabled' && ! this.element.hasClass('keepdisabled')) {
                this.element.prop('disabled', value);
                this.element.find('input').prop('disabled', value);
            }
        },
        _renderDatasourceDropdown: function (value) {
            var self = this;
            if( ! $.isArray(value)) {
                return;
            }
            this.element.empty();
            
            this._renderOptgroup(this.element, value);           
        },
        _renderOptgroup: function(element, items) {
            var self = this;            
            $.each(items, function (index, item) {
                var optgroup;
                if($.isArray(item[0])) {
                    optgroup = $('<optgroup />', {label: item[1]});
                    element.append(optgroup);
                    self._renderOptgroup(optgroup, item[0]);
                } else {
                    element.append($('<option />', {
                        value: item[0],
                        selected: $.inArray(item[0], self._selection) >= 0 ? 'selected' : undefined
                    }).text(item[1]));
                }
            });            
        },
        _renderDatasourceWidgetList: function (value) {
            var self = this;
            var inputType = self._multiple ? 'checkbox' : 'radio';
            var ul = this.element.children('ul').first();
            var prefixId = this._valueTarget;
            var prefixName = this._meta.attr('name');

            if( ! ($.isArray(value) || $.isPlainObject(value))) {
                return;
            }

            if ( ul.size() > 0 ) {
                // clear all existing choices
                ul.empty();
            } else {
                // create a new UL tag and append it
                ul = $('<ul/>');
                this.element.append(ul);
            }


            // Fill the list of checkboxes
            for(var i in value) {
                var input = $('<input />');
                var li = $('<li />');
                var label = $('<label />');
                var inputId = prefixId + '_' + i;

                input.attr('type', inputType);
                input.attr('value', value[i][0]);
                input.attr('id', inputId);
                input.attr('name',  prefixName + (self._multiple ? '[' + i + ']' : ''));
                input.attr('class', 'choice');
                input.prop('disabled', self.element.prop('disabled'));

                if($.inArray(value[i][0], self._selection) >= 0) {
                    input.attr('checked', 'checked');
                }

                label.attr('for', inputId);
                label.text(value[i][1]);

                li.addClass('labeled-control label-right');
                li.append(input);
                li.append(label);

                ul.append(li);
            }
        },
        _updateView: function(value, control) {            
            if(control == this._valueTarget) {
                this.select(value);
            } else if(control == this._datasourceTarget) {
                if(this._mode == 'list') {
                    this._renderDatasourceWidgetList(value);
                } else if(this._mode == 'dropdown') {
                    this._renderDatasourceDropdown(value);
                }
            }
        },
        /*
         * Transfer UI selection into the object internal state.
         */
        select: function (value) {

            if($.isArray(value)) {
                this._selection = value;
            } else if($.isPlainObject(value)) {
                this._selection = [];
                for(var i in value) {
                    this._selection.push(value[i]);
                }
            } else {
                this._selection = [value];
            }

            this.refresh();
        },
        /*
         * Transfer the selection from the object internal state to UI
         */
        refresh: function () {
            var selectedProp, widgetSelector;
            var self = this;

            if(this._mode == 'list') {
                selectedProp = 'checked';
                widgetSelector = 'li input.choice';
            } else if(this._mode == 'dropdown') {
                selectedProp = 'selected';
                widgetSelector = 'option';
            }

            this.element.find(widgetSelector).each(function() {
                var option = $(this);

                if($.inArray(option.attr('value'), self._selection ) >= 0) {
                    option.prop(selectedProp, true);
                } else {
                    option.prop(selectedProp, false);
                }

            });
        }
    });
}( jQuery ));
