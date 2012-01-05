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
            this.expanded = false;

            if(!this.element.hasClass('expandable')) {
                return;
            }

            var anchor = $('<button type="button" />').bind('click.' + this.namespace, function () {
                if(self.expanded === true) {
                    self.collapse();
                } else {
                    self.expand();
                }
            });

            this.element.children().not('legend').wrapAll('<div></div>');
            this.expandable = true;
            this.element.children('div').hide();
            this.element.addClass('collapsed');
            this.element.children('legend').children('.ui-icon').attr('class', 'ui-icon ui-icon-triangle-1-e');
            this.element.children('legend').wrapInner(anchor);
         
        },
        expand: function() {
            if(!this.expandable) {
                return;
            }
            this.element.removeClass('collapsed');
            this.element.children('div').slideDown();
            this.element.find('legend .ui-icon').attr('class', 'ui-icon ui-icon-triangle-1-s');
            this.expanded = true;
        },
        collapse: function() {
            if(!this.expandable) {
                return;
            }
            this.element.addClass('collapsed');
            this.element.children('div').slideUp();
            this.element.find('legend .ui-icon').attr('class', 'ui-icon ui-icon-triangle-1-e');
            this.expanded = false;
        }
    });

}( jQuery ));
