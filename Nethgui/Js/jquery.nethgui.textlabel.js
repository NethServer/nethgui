/*
 * TextLabel
 *
 * Copyright (C) 2011 Nethesis S.r.l.
 */
(function( $ ) {
    var SUPER = $.nethgui.Component;
    $.widget('nethgui.TextLabel', SUPER, {
        _deep: false,
        options: {
            hsc: false,
            template: '${0}'
        },
        _create: function() {
            SUPER.prototype._create.apply(this);

           var options = this.element.attr('data-options') ? $.parseJSON(this.element.attr('data-options')) : {};

            if(typeof options === 'object') {
                this.option(options);
            }
            
        },
        _updateView: function(value) {
            if($.isArray(value)) {
                this.setLabel(String.prototype.replacePlaceholders.apply(this.option('template'), value));
            } else {
                this.setLabel(this.option('template').replacePlaceholders(value));
            }
        },
        setLabel: function(text) {
            if(this.option('hsc')) {
                this.element.text(text);
            } else {
                this.element.empty();
                this.element.append(text);
            }
            
            this._trigger('changed', undefined, text);
        }
    });
    
}( jQuery ));
