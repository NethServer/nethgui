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
                    if(self.element.is(':visible')) {
                        self.element.tabs('select', index);
                        if(e.takeMeVisible === true) {
                            e.target.focus();
                        }
                    }
                });
            });

            this.element.tabs();

            // on tabsshow reposition and redraw tracked tooltips:
            this.element.bind('tabsshow.' + this.namespace, function (e, ui) {
                $(ui.panel).find('[aria-describedby]').filter(':nethgui-Tooltip').Tooltip('repaint');
                $(ui.panel).find('.Action:eq(0)').first().trigger('nethguishow');
            });

            this.element.tabs('show', 0);
        }
    });


}( jQuery ));
