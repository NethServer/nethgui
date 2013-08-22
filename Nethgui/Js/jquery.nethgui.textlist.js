/*
 * TextList
 *
 * Copyright (C) 2011 Nethesis S.r.l.
 */
(function( $ ) {
    var SUPER = $.nethgui.Component;
    $.widget('nethgui.TextList', SUPER, {
        _deep: false,
        options: {
            wrap: {
                listTag: 'ul',
                listClass: null,
                elementTag: 'li',
                elementClass: null
            },
            separator: false
        },
        _create: function() {
            SUPER.prototype._create.apply(this);

            var options = this.element.attr('data-options') ? $.parseJSON(this.element.attr('data-options')) : {};

            if(typeof options === 'object') {
                this.option(options);
            }

            
        },
        _updateView: function(value) {
            if( ! $.isArray(value)) {
                value = [value];
            }

            this.element.empty();

            if(value.length === 0) {
                return;
            }

            var wrap = this.option('wrap');
            var separator = this.option('separator');


            var listNode = this.element;

            if(wrap.listTag) {
                listNode = $('<' + wrap.listTag + '/>', {
                    'class': wrap.listClass
                });
                listNode.appendTo(this.element);
            }

            $.each(value, function(i, text) {
                // add separator between elements, if defined
                if(i > 0 && separator) {
                    listNode.append(separator);
                }

                // list element: to wrap or not to wrap?
                if(wrap.elementTag) {
                    $('<' + wrap.elementTag + '>',  {
                        'class': wrap.elementClass
                    }).text("" + text).appendTo(listNode);
                } else {
                    listNode.append(text);
                }
            });
        }
    });
})( jQuery );
