/*
 * FieldsetSwitch
 *
 * API:
 *
 * - select()
 */
(function( $ ) {
    var SUPER = $.nethgui.Component;
    $.widget('nethgui.FieldsetSwitch', SUPER, {
        _deep: true,
        _showDisabledState: false,
        _create: function() {
            SUPER.prototype._create.apply(this);
            
            this._switch = this.element.find('input:radio, input:checkbox').first();
            this._panel = this.element.children('fieldset.FieldsetSwitchPanel').first();

            this._switch.bind('change.' + this.widgetName, $.proxy(this._updatePanelState, this));
            this._switch.bind(this.namespace + 'unselect.' + this.widgetName, $.proxy(this._unselect, this));

            this._updatePanelState();
        },
        _updatePanelState: function() {
            if(this._switch.is(':checked')) {
                this._select();
            } else {
                this._unselect();
            }
        },
        _select: function () {            
            if(this._switch.prop('disabled') === false) {
                this._panel.trigger('nethguienable');
            }
            if(this.element.hasClass('expandable')) {
                this._panel.show();
            }
        },
        _unselect: function () {
            if(this.element.hasClass('expandable')) {
                this._panel.hide();
            }
            this._panel.trigger('nethguidisable');
        }
    });
    $.widget('nethgui.FieldsetSwitchPanel', SUPER, {
        _deep: true,
        _showDisabledState: false,
        _create: function() {
            SUPER.prototype._create.apply(this);
        }
    });
}( jQuery ) );
