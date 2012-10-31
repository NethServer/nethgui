/*
 * Help area
 *
 * Copyright (C) 2011 Nethesis S.r.l.
 */
(function( $ ) {
    var SUPER = $.nethgui.Component;
    $.widget('nethgui.HelpArea', SUPER, {
        _create: function() {
            var self = this;
            SUPER.prototype._create.apply(this);
            this._tooltipControls = [];
            this._proxyHelpHandler = function(e, url) {
                self.open(url);
            };
            this.element.hide();
            this.element.bind('nethguicancel.' + this.namespace, $.proxy(this.close, this));
            $(document).bind('nethguihelp.' + this.namespace, this._proxyHelpHandler);
            $(window).bind('resize.' + this.widgetName, $.proxy(this._fixBoxHeight, this));
            this._helpDoc = $('<div class="HelpDocument"></div>');
            this.element.children('.wrap').append(this._helpDoc);

            this.element.bind('ajaxStart.' + this.widgetName, $.proxy(this.close, this));
        },
        destroy: function() {
            SUPER.prototype.destroy.call( this );
            $(document).unbind(this._proxyHelpHandler);
            $(window).unbind(this.widgetName);
        },
        close: function() {
            this.element.hide();
            if($.fn.Tooltip !== undefined) {
                $.each(this._tooltipControls,function (index, element) {
                    $(element).Tooltip('destroy');
                });
            }
            this._tooltipControls = [];
        },
        _onHelpDocumentResponse: function(responseData) {
            var responseDocument = $($.parseXML(responseData));
            var helpNode = responseDocument.find('#HelpDocument').detach();
            this._helpDoc.empty().append(helpNode.children());
            
            // loop on field descriptions to find targets:
            this._helpDoc.find('dt').each($.proxy(this._initializeDtNode, this));
            $.unique(this._tooltipControls);

            this.element.show();
            this._fixBoxHeight();
        },
        _initializeDtNode: function(index, element) {
            var $dt = $(element);
            var description = $(element).next('dd');
            
            var controlList = [];

            // for each class check if a LABEL tag exists and try to attach a click handler to DT.
            $.each($dt.attr('class').split('/ +/'), function(index, helpId) {
                $('label.' + helpId).each(function (index, labelElement) {
                    var controlElement = $('#' + $(labelElement).attr('for'))[0];
                    if($.inArray(controlElement, controlList) === -1) {
                        controlList.push(controlElement);
                    }
                });
            });

            if(controlList.length === 0 || $.fn.Tooltip === undefined) {
                $dt.wrapInner('<u />');
                return;
            }
            
            this._tooltipControls = this._tooltipControls.concat(controlList);

            $dt.wrapInner($('<a href="#" />').click( function (e) {
                $(controlList).Tooltip('show').focus();
                return false;
            } ));
           
            $(controlList).Tooltip( {
                text: description.text(),
                sticky: true,
                show: false
            } );
        },
        /**
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
                    self.element.show();
                    self._fixBoxHeight();
                }
            });            
        }
    });
    
}( jQuery ));
