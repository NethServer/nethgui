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
        _create: function() {
            SUPER.prototype._create.apply(this);
                                   
            var mySwitch = this.element.find('input:radio').first();

            this._selected = false;
            this._otherSwitches = this._findGroup(mySwitch[0]);
            this._panel = this.element.children('fieldset.FieldsetSwitchPanel').first();

            SUPER.prototype._initializeDeep.call(this, [this._panel.get(0), mySwitch.get(0)]);

            if(mySwitch.is(':checked'))
                this.select()
            else
                this._unselect()

            mySwitch.bind('change.' + this.widgetName, $.proxy(this.select, this));
            mySwitch.bind('unselect.' + this.widgetName, $.proxy(this._unselect, this));
        },
        _findGroup: function (radio) {
            return $(radio.form).find('input[name="' + radio.name + '"]').not(radio);
        },
        select: function () {
            this._selected = true;
            $.each(this._otherSwitches, function(index, checkbox) {
                $(checkbox).trigger('unselect');
            });
            this._panel.trigger('nethguienable');
            this._panel.show();
        },
        _unselect: function () {
            this._selected = false;
            this._panel.hide();
            this._panel.trigger('nethguidisable');
        },
        _updateView: function(value) {
            if(value == this.element.val()) {
                this.select();
            }
        }
    });
    $.widget('nethgui.FieldsetSwitchPanel', SUPER, {
        _deep: true,
        _create: function() {
            SUPER.prototype._create.apply(this);
        }
    });
}( jQuery ) );
