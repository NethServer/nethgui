/*
 * TextLabel
 *
 * Copyright (C) 2011 Nethesis S.r.l.
 */
(function( $ ) {
    var SUPER = $.nethgui.Component;
    $.widget('nethgui.TextLabel', SUPER, {
        _deep: false,
        _create: function() {
            SUPER.prototype._create.apply(this);
            this._template = this.element.attr('data-template') === undefined ? this.element.text() : this.element.attr('data-template');
        },
        _updateView: function(value) {
            if($.isArray(value)) {
                this.setLabel(String.prototype.replacePlaceholders.apply(this._template, value));
            } else {
                this.setLabel(this._template.replacePlaceholders(value));
            }
        },
        setLabel: function(text) {
            this.element.text(text);
            this._trigger('changed', undefined, text);
        }
    });
    
}( jQuery ));
