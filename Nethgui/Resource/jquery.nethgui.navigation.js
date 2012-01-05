/*
 * Navigation menu
 *
 * Copyright (C) 2011 Nethesis S.r.l.
 */
(function( $ ) {
    var SUPER = $.nethgui.Action;
    $.widget('nethgui.Navigation', SUPER, {
        _updateView: function(event, value) {
            if(!$.isArray(value) || value.length === 0) {
                this.element.find('.category, li').show();
                return;
            }
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
