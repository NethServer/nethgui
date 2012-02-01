/*
 * Navigation menu
 *
 * Copyright (C) 2011 Nethesis S.r.l.
 */
(function( $ ) {
    var SUPER = $.nethgui.Action;
    $.widget('nethgui.Navigation', SUPER, {
        _create: function() {
            SUPER.prototype._create.call( this );
            this.element.find('.Button.search')
                .button({icons: {primary: 'ui-icon-search'}, text: false})
                .removeClass('ui-corner-all');
                

            this.element.find('.TextInput')
                .removeClass('ui-corner-all');
                
        },
        _updateView: function(value) {
            // if the response is empty show all items:
            if(!$.isArray(value) || value.length === 0) {
                this.element.find('.category, li').show();
                return;
            }

            // hide any item that is not member of the `value` array:
            this.element.find('.category, li').each(function(index, element) {
                var href = $(element).children('a:first').attr('href');
                if($.inArray(href, value) >= 0)  {
                    $(element).show();
                } else {
                    $(element).hide();
                }
            });
        }
    });
}( jQuery ));
