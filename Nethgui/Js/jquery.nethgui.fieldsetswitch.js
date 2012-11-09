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
            this._panel.FieldsetSwitchPanel('setPropagateDisabledState', true);
            this._panel.trigger('nethguienable');
            if(this.element.hasClass('expandable')) {
                this._panel.show();
            }
        },
        _unselect: function () {            
            if(this.element.hasClass('expandable')) {
                this._panel.hide();
            }
            this._panel.trigger('nethguidisable');
            this._panel.FieldsetSwitchPanel('setPropagateDisabledState', false);
        },
        enable: function() {
            this._panel.FieldsetSwitchPanel('setPropagateDisabledState', this._switch.prop('checked'));
            SUPER.prototype.enable.call(this);
        }
    });
    $.widget('nethgui.FieldsetSwitchPanel', SUPER, {
        _deep: true,
        _showDisabledState: false,
        _create: function() {
            SUPER.prototype._create.apply(this);
        },
        setPropagateDisabledState: function(value) {
            this._propagateDisabledState = value;
        }
    });
}( jQuery ) );
