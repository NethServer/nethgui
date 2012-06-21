/*
 * Buttonset
 *
 * Copyright (C) 2011 Nethesis S.r.l.
 */
(function( $ ) {
    var SUPER = $.nethgui.Component;
    $.widget('nethgui.Buttonset', SUPER, {
        _create: function () {
            SUPER.prototype._create.apply(this);
            this._clearWhitespaceNodes();
            this._initializeExpandButton();
            this._expandButton = undefined;

	    // Register a global list of opened popups to close on ESCAPE keyup. Refs #1039
	    if( $(document).data('ngButtonsetPopupList') === undefined ) {
		$(document).data('ngButtonsetPopupList', []);
		$(document).bind('keyup', function (event) {
		    if(event.keyCode !== $.ui.keyCode.ESCAPE) {
			return;
		    }
		    $.each($(document).data('ngButtonsetPopupList'), function (index, popup) {
			$(popup).hide();
		    });
		});
	    }

        },
        enable: function () {
            if(this._expandButton !== undefined) {
                this._expandButton.button('enable');
            }
        },
        disable: function () {
            if(this._expandButton !== undefined) {
                this._expandButton.button('disable');
            }
        },
        _clearWhitespaceNodes: function() {
            this.element.contents().filter(function() { return this.nodeType === 3 }).remove();
        },
        /**
         * Add a button that pops up the a menu.
         *
         * The v<number> class sets the maximum number of shown buttons. Items
         * exceeding that limit fall into the menu.
         */
        _initializeExpandButton: function() {
            var matches = / v(\d+)/.exec(this.element.attr('class'));
            var limit = NaN;
            var expandButton;
            if(matches === null) {
                return false;
            }
            limit = parseInt(matches[1]);
            if(limit === NaN || limit === 0) {
                return false;
            }            
            if(this.getChildren().length <= limit) {
                return false;
            }
            var detached = this.element.children(':gt(' + (limit - 1) + ')').detach();
            var cloned = this.element.children(':lt(' + limit + ')').clone(true);
            expandButton = $('<button type="button">Expand</button>').button({
                icons: {
                    primary:'ui-icon-triangle-1-s'
                },
                text:false
            });
            expandButton.appendTo(this.element).wrap($('<span></span>', {
                'class': 'expander'
            }));
            var panel = $('<span></span>', {
                'class':'popup'
            });
            panel.insertAfter(expandButton);
            this.element.buttonset();
            cloned.appendTo(panel);
            detached.appendTo(panel); //.css('display', 'block');
            panel.hide();
            panel.find('.Button').removeClass('ui-corner-all').click(function(){
                panel.hide()
            });
            expandButton.click(function(e) {
                $('.popup').hide();
                $(document).one('click', function() {
                    panel.hide();
                });
                panel.show();
		// add panel to the global list of opened popups:
		$(document).data('ngButtonsetPopupList').push(panel.get(0));
                e.stopPropagation();
            });
            this._expandButton = expandButton;
            return true;
        }
    });
}( jQuery ));


