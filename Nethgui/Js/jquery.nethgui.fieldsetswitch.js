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
        _deep: false,
        _showDisabledState: false,        
        _create: function() {
            SUPER.prototype._create.apply(this);
                                             
            this._switch = this.element.find('input:radio, input:checkbox').first();
            this._panel = this.element.children('fieldset.FieldsetSwitchPanel').first();

            SUPER.prototype._initializeDeep.call(this, [this._panel.get(0), this._switch.get(0)]);

            this._switch.bind('change.' + this.widgetName, $.proxy(this._toggle, this));
            this._switch.bind(this.namespace + 'unselect.' + this.widgetName, $.proxy(this._unselect, this));
            
            this._toggle();
        },
        _toggle: function() {
            if(this._switch.is(':checked'))
                this._select()
            else
                this._unselect()
        },
        _select: function () {
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
