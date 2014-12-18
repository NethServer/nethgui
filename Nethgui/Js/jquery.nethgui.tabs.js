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

            this.element
                .on('nethguishow.' + this.namespace, $.proxy(this._onShow, this))
                .on('nethguicancel.' + this.namespace, $.proxy(this._onCancel, this))
            ;
            
            // replace href attribute values with tab IDs.
            this.element.children('ul:eq(0)').find('li a').each(function(index, anchor) {
                var childPanel = self.element.children().get(index + 1);
                $(anchor).attr('href', '#' + childPanel.id);
                $(anchor).on('click.' + self.namespace, null, childPanel.id, $.proxy(self._onTabClick, self));
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
        },
        _onTabClick: function (e) {
            var fragment = window.location.href.split(/#!?/, 2)[1];

            if( ! fragment ||  ! fragment.match('^' + e.data) ) {
                history.pushState({target: e.data}, '', '#!' + e.data);
            }
        },
        _onShow: function (e) {
            if(this.element.get(0) === e.target) {
                // redirect to first Action:
                e.stopPropagation();
                this.element.find('.Action:eq(0)').trigger('nethguishow');
            } else {
                var idx = -1;
                var a = null;
                // find the tab index and change selection
                this.element.children('.Action').each(function (index, action) {
                    if (action === e.target) {
                        a = action;
                        idx = index;
                        return false;
                    }
                    if ($(action).find(e.target).length > 0) {
                        idx = index;
                        return false;
                    }
                });
                if(a) {
                    e.stopPropagation();
                    $(a).find('.Action:eq(0)').trigger('nethguishow');
                }
                if(idx >= 0) {
                    this.element.tabs('select', idx);
                }
             }
            
        },
        _onCancel: function (e) {
            $.debug('oncancel tabs');
        }  
    });


}( jQuery ));
