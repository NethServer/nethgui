/*
 * Fieldset
 *
 * Copyright (C) 2011 Nethesis S.r.l.
 */
(function( $ ) {
    var SUPER = $.nethgui.Component;
    $.widget('nethgui.Fieldset', SUPER, {
        options: {
            _deep: true
        },
        _create: function() {
            SUPER.prototype._create.apply(this);

            var self = this;

            if( ! this.element.hasClass('expandable')) {
                this._expandable = false;
                return;
            }

            this._expandable = true;           
            this.element.children().not('legend').wrap('<div></div>');
            this._content = this.element.children().not('legend');
            
            this.element.find('legend .TextLabel')
            .addClass('clickable')
            .bind('click', function(e) {
                self._setOption('collapsed', ! self.element.hasClass('collapsed'))
            });

            this._content.hide();
            this.element.addClass('collapsed');
            this.element.find('legend .ui-icon').attr('class', 'ui-icon ui-icon-triangle-1-e');
        },
        _setOption: function (key, value) {
            SUPER.prototype._setOption.apply( this, [key, value] );
            if(key === 'collapsed') {
                if( ! this._expandable) {
                    return this;
                }
                
                var o = value ? {
                    fx: 'slideUp',
                    icon: 'e'
                } : {
                    fx: 'slideDown',
                    icon: 's'
                };

                this.element.trigger('nethguiresizestart', {
                    height: this.element.height(),
                    width: this.element.width()
                });
                                
                this._content[o.fx]($.proxy(this._onResizeEnd, this));
                this.element.find('legend .ui-icon').attr('class', 'ui-icon ui-icon-triangle-1-' + o.icon);
            }
            return this;
        },

        _onResizeEnd: function () {
            this.element.toggleClass('collapsed')
            this.element.trigger('nethguiresizeend', {
                height: this.element.height(),
                width: this.element.width()
            });
        }
    });

}( jQuery ));
