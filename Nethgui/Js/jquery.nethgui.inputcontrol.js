/*
 * InputControl
 *
 * Copyright (C) 2011 Nethesis S.r.l.
 */
(function( $ ) {
    var SUPER = $.nethgui.Component;
    $.widget('nethgui.InputControl', SUPER, {
        _deep: false,
        _create: function() {
            SUPER.prototype._create.apply(this);
            this.element.bind('focus.' + this.namespace, $.proxy(this._onFocus, this));
        },
        _updateView: function(value) {
            this.element.val(value ? value : '');
        },
        _setOption: function( key, value ) {
            SUPER.prototype._setOption.apply( this, arguments );
            if(key === 'disabled' && ! this.element.hasClass('keepdisabled')) {
                this.element.prop('disabled', value);
            }
        },
        _onFocus: function (e) {
            e.takeMeVisible = false;
            
            if(!this.element.is(':visible')) {
                e.preventDefault();
                e.takeMeVisible = true;
            }            
        }
    });
    $.widget('nethgui.Hidden', $.nethgui.InputControl, {});
    $.widget('nethgui.CheckBox', $.nethgui.InputControl, {
        _updateView: function(value) {
            if(this.element.val() === value) {
                this.element.prop('checked', true);
            } else {
                this.element.prop('checked', false);
            }                        
            this.element.trigger('change');
        } 
    });
    $.widget('nethgui.RadioButton', $.nethgui.CheckBox, {
        _create: function() {
            SUPER.prototype._create.apply(this);
            this._radioGroup = this._findGroup(this.element.get(0));
            this.element.bind('change.' + this.widgetName, $.proxy(this._change, this));
        },        
        _findGroup: function (radio) {
            return $(radio.form).find('input[name="' + radio.name + '"]').not(radio);
        },
        _change: function () {        
            if(this.element.is(':checked')) {
                this._radioGroup.trigger(this.namespace + 'unselect');            
            }
        }
    });
    $.widget('nethgui.HiddenConst', $.nethgui.InputControl, {
        _updateView: function(value) {}        
    });    
    
}( jQuery ) );
