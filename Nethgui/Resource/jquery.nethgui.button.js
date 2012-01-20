/*
 * Button
 *
 * Copyright (C) 2012 Nethesis S.r.l.
 */
(function( $ ) {
    var SUPER = $.nethgui.InputControl;
    $.widget('nethgui.Button', SUPER, {
        _create: function () {
            SUPER.prototype._create.apply(this);

            var self = this;

            if(!this.element.hasClass('givefocus')) {
                // apply jQueryUi "button" widget
                this.element.button({
                    disabled: this.element.hasClass('disabled')
                });
            }
            this.element.click($.proxy(this._onClick, this));
            this.element.bind('nethguisetlabel.' + this.namespace, function(e, value) {
                self.element.button("option", "label", value);
                e.stopPropagation();
            });
        },
        _updateView: function(value, selector) {
            if(this._server.isLocalUrl(value) && this.element[0].tagName.toLowerCase() === 'a') {
                this.element.attr('href', value);
            }
        },
        _setOption: function( key, value ) {
            if(key === 'disabled') {
                this.element.button('option', 'disabled', value);
            } else {
                SUPER.prototype._setOption.apply( this, arguments );
            }
        },
        _onClick: function(e) {
            var tagName = this.element[0].tagName.toLowerCase();

            if(tagName === 'a') {
                var href = this.element.attr('href');
                if(this.element.hasClass('cancel')) {
                    this._trigger('cancel');
                } else if(this.element.hasClass('givefocus') && href[0] === '#') {
                    $(href).trigger('focus');
                } else if(this.element.hasClass('Help')) {
                    this.element.trigger('nethguihelp', href);
                } else {
                    this._sendQuery(href, undefined, true);
                }
                e.stopPropagation();
                e.preventDefault();
            }                       
        }

    });
}( jQuery ));
