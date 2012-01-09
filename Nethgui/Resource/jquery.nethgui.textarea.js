/*
 * Text Area
 *
 * Copyright (C) 2011 Nethesis S.r.l.
 */
(function( $ ) {
    var SUPER = $.nethgui.InputControl;
    $.widget('nethgui.TextArea', SUPER, {
        _updateView: function (value, selector) {
            var control = this.element;

            if(control.hasClass('console')) {
                if (value === false) {
                    control.val('');
                } else {
                    control.val(control.val() + value);
                }
                   control.scrollTop(control[0].scrollHeight - control.height());
            } else {
                control.val(value);
            }
        }
    });
}( jQuery ));
