/*
 * Tabs
 *
 * Copyright (C) 2011 Nethesis S.r.l.
 */
(function( $ ) {
    var SUPER = $.nethgui.Component;
    $.widget('nethgui.Tabs', SUPER, {
        _deep: true,
        _create: function() {
            SUPER.prototype._create.apply(this);
            var self = this;

            // replace href attribute values with tab IDs.
            this.element.children('ul:eq(0)').find('li a').each(function(index, anchor) {
                var childPanel = self.element.children().get(index + 1);
                $(anchor).attr('href', '#' + childPanel.id);
                $(childPanel).bind('focus.' + self.namespace, function(e) {                    
                    self.element.tabs('select', index);
                    $(e.target).qtip('reposition');
                });
            });

            this.element.tabs();            
        }
    });


}( jQuery ));
