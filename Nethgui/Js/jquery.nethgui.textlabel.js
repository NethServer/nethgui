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
            'hsc': false,
            'template': '${0}',
            'static': true
        },
        _create: function() {
            SUPER.prototype._create.apply(this);

            var options = this.element.attr('data-options') ? $.parseJSON(this.element.attr('data-options')) : {};

            if(typeof options === 'object') {
                this.option(options);
            }
            
            if(this.option('static')) {
                this.renderLabel([]);
            }

        },
        _updateView: function(value) {
            if($.isArray(value)) {
                this.renderLabel(value);
            } else {
                this.renderLabel([value]);
            }
        },
        renderLabel: function(args) {
            this.setLabel(String.prototype.replacePlaceholders.apply(this.option('template'), args));
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
