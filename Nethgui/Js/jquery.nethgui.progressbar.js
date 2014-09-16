/*
 * Progress bar
 *
 * Copyright (C) 2011 Nethesis S.r.l.
 */
(function( $ ) {
    var SUPER = $.nethgui.Component;
    $.widget('nethgui.Progressbar', SUPER, {
        _deep: true,
        _create: function() {
            SUPER.prototype._create.apply(this);
            this._text = this.element.children('span');
            this.element.progressbar();
        },
        _updateView: function(value) {
            var percent = parseInt(value);
            if(percent > 100) {
                percent = 100;
            } else if(percent < 0) {
                percent = 0;
            }
            this.element.progressbar('value', percent);
        }
    });
}( jQuery ));
