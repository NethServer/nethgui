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
            this.element.attr('value', value ? value : '');
        },
        _setOption: function( key, value ) {
            SUPER.prototype._setOption.apply( this, arguments );
            if(key === 'disabled' && ! this.element.hasClass('keepdisabled')) {
                value === true ? this.element.attr('disabled', 'disabled') : this.element.removeAttr('disabled');
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

/*
 * Tooltip
 *
 * Copyright (C) 2011 Nethesis S.r.l.
 */
(function( $ ) {
    var SUPER = $.nethgui.Component;
    $.widget('nethgui.Tooltip', SUPER, {
        _deep: false,
        options: {
            sticky: false,
            show: false,
            color: 'blue',
            style: 0,
            text: '',
            destroyOn: 'ajaxStart'
        },
        _create: function() {
            SUPER.prototype._create.apply(this);

            var self = this;
            var qtipTarget;
            
            // error-state forces color to "red"
            if(this.options.style & 2) {
                this.element.addClass('ui-state-error');
                this.options.color = 'red';
            }

            if(this.element.get(0).tagName.toLowerCase() === 'input' 
                && this.element.parent().hasClass('label-right')) {
                qtipTarget = this.element.siblings('label[for=' + this.element.attr('id') + ']').first();
            } else {
                qtipTarget = false;
            }

            this.element.qtip({
                position: {
                    my: 'left center',
                    at: 'right center',
                    container: this.element.parents('.ui-tabs-panel, .Action, #CurrentModule, .Inset').first(),
                    target: qtipTarget
                },
                style: {
                    classes: 'ui-tooltip-${color} ui-tooltip-shadow'.replacePlaceholders({
                        color: this.options.color
                    })
                },
                content: {
                    text: this.options.text
                },
                events: {
                    hide: this.options.sticky ? function (e, api) {
                        e.preventDefault()
                    } : undefined
                }
            });

            if(this.options.show) {
                this.show();
            }

            if(typeof this.options.destroyOn === 'string') {
                this.element.bind(this.options.destroyOn.split(' ').join('.' + this.namespace + ' ').trim(), function (e) {
                    self.destroy();
                } );
            }
        },
        show: function() {
            this.element.qtip('show');
        },
        hide: function() {
            this.element.qtip('hide');
        },
        repaint: function() {
            this.element.qtip('redraw').qtip('reposition', undefined, false);
        },
        destroy: function () {
            SUPER.prototype.destroy.apply(this);
            this.element.qtip('destroy');
            if(this.options.style & 2) {
                this.element.removeClass('ui-state-error');
            }
        }
    });
}( jQuery ) );
