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
            var self = this;
            this.element.bind('nethguitooltip.' + this.namespace, $.proxy(this._tooltip, this));
            this.element.bind('focus.' + this.namespace, function (e) {
                self.element.qtip('show', e)
                });
            this.element.bind('blur.' + this.namespace, function (e) {
                self.element.qtip('hide', e)
                });
        },
        _updateView: function(value) {
            // Clear the tooltip
            this._tooltip();
            this.element.attr('value', value ? value : '');
        },
        _setOption: function( key, value ) {
            SUPER.prototype._setOption.apply( this, arguments );
            if(key === 'disabled') {
                value === true ? this.element.attr('disabled', 'disabled') : this.element.removeAttr('disabled');
            }
        },
        _tooltip: function (e, tooltip) {            
            var color = 'blue';

            if(tooltip === undefined) {
                this.element.qtip('destroy');
                this.element.removeClass('ui-state-error');
                return;
            }

            if(tooltip.style & 2) {
                this.element.addClass('ui-state-error');
                color = 'red';
            }
           
            this.element.qtip({
                position: {
                    my: 'left center',
                    at: 'right center'
                },
                style: {
                    classes: 'ui-tooltip-${color} ui-tooltip-shadow'.replacePlaceholders({
                        color: color
                    })
                },
                content: {
                    text: tooltip.text
                }
            });
        }
    });
    $.widget('nethgui.Hidden', $.nethgui.InputControl, {});
    $.widget('nethgui.RadioButton', $.nethgui.InputControl, {
        _updateView: function(value) {
            if(this.element.val() == value) {
                this.element.prop('checked', true);
            } else {
                this.element.prop('checked', false);
            }
        }
    });
    $.widget('nethgui.CheckBox', $.nethgui.RadioButton, {});
}( jQuery ) );
