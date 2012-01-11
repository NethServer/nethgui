/*
 * Help area
 *
 * Copyright (C) 2011 Nethesis S.r.l.
 */
(function( $ ) {
    var SUPER = $.nethgui.Component;
    $.widget('nethgui.HelpArea', SUPER, {
        options: {
            _deep: true
        },
        _create: function() {
            var self = this;
            SUPER.prototype._create.apply(this);
            this._proxyHelpHandler = function(e, url) {
                self.open(url);
            };
            this.element.hide();
            this.element.bind('nethguicancel.' + this.namespace, $.proxy(this.close, this));
            $(document).bind('nethguihelp.' + this.namespace, this._proxyHelpHandler);
            $(window).bind('resize.' + this.widgetName, $.proxy(this._fixBoxHeight, this));
            this._helpDoc = $('<div class="HelpDocument"></div>');
            this.element.children('.wrap').append(this._helpDoc);
        },
        destroy: function() {
            SUPER.prototype.destroy.call( this );
            $(document).unbind(this._proxyHelpHandler);
            $(window).unbind(this.widgetName);
        },
        close: function() {
            this.element.hide();
        },
        _onHelpDocumentResponse: function(responseData) {
            var responseDocument = $($.parseXML(responseData));
            var helpNode = responseDocument.find('#HelpDocument').detach();
            this._helpDoc.empty().append(helpNode.children());
            
            // loop on field descriptions to find targets:
            this._helpDoc.find('dt').each(function (index, element) {
                var $dt = $(element);
                var description = $(element).next('dd');
            // for each class check if a LABEL tag exists and try to attach a click handler to DT.
            //                $.each($dt.attr('class').split('/ +/'), function(index, target) {
            //                    var foundItems = $('#CurrentModule label.' + target);
            //                    if(foundItems.size() == 0) {
            //                        $dt.wrapInner($('<u />'));
            //                        return;
            //                    }
            //                    $dt.wrapInner($('<a />', {
            //                        href:'#'
            //                    }).click(function(e) {
            //                        foundItems.each(function() {
            //                            $('#' + $(this).attr('for') + ':visible').Nethgui('tooltipSet', description.clone(), LEVEL_INFO).Nethgui('tooltipShow');
            //                        });
            //                        return false;
            //                    }));
            //                });
            });

            this.element.show();
            this._fixBoxHeight();
        },
        /*
         * Calculate window height and set help wievport
         */
        _fixBoxHeight: function() {            
            var w = window,
            d = document,
            e = d.documentElement,
            g = d.getElementsByTagName('body')[0],
            //x = w.innerWidth||e.clientWidth||g.clientWidth,
            y = w.innerHeight || e.clientHeight || g.clientHeight;
            this._helpDoc.css('height', y - 58);
        },
        /**
         * Load help contents and display the help area.
         */
        open: function (url) {
            var self = this;
            this.element.trigger('nethguifreezeui');

            $.ajax({
                type: 'GET',
                url: url,
                cache: true,
                success: $.proxy(this._onHelpDocumentResponse, this),
                error: function(jqXHR, textStatus, errorThrown) {
                    if(jqXHR.status == 404) {                        
                        self._helpDoc.empty().append('<h1>Not found</h1><p>Help document is not available!</p>');
                    } else {
                        self._helpDoc.empty().append($('<h1 />').text(errorThrown));
                    }
                }
            });            
        }
    });
    
}( jQuery ));
