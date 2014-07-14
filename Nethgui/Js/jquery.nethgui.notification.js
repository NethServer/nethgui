/*
 * Notification
 *
 * Copyright (C) 2011 Nethesis S.r.l.
 */
(function( $ ) {
    var SUPER = $.nethgui.Component;
    $.widget('nethgui.Notification', SUPER, {
        _deep: false,
        _create: function() {
            var self = this;
            SUPER.prototype._create.apply(this);
            if( ! this.element.children().get(0)) {
                $('<ul />').appendTo(this.element);
            }
            $(document).on('ajaxStart.' + this.namespace, function () {
               $(self.element).find('li.notification').fadeOut(function () { $(this).remove() });
            });
         },
        _updateView: function(value, selector) {
            var self = this;
            if( ! $.isArray(value) || value.length === 0) {
                return;
            }
            
            var ul = $(this.element.children().get(0)).empty();
            $.each(value, function(index, notification) {
                var tmpl = $.nethgui.Notification.templates[notification.template] ? $.nethgui.Notification.templates[notification.template] : $.nethgui.Notification.templates['default'];
                $('<li />', {'class': 'notification ' + notification.template}).appendTo(ul)
                        .append($('<span />', {'class': 'pre fa ' + notification.icon}))
                        .append($('<span />', {'class': 'content'}).html(Mustache.render(tmpl, notification)))
                ;
                if($.nethgui.Notification.callbacks[notification.template]) {
                     $.nethgui.Notification.callbacks[notification.template].call(self, notification)
                }
            });
            ul.appendTo(this.element).slideDown();
        },
    });

    $(document).ready(function() {
        $('#Notification').Notification();
    });

    $.nethgui.Notification.templates = {};
    $.nethgui.Notification.callbacks = {
        validationError: function(n) {
            $.each(n.data.fields, function(index, field) {
                $('.' + field.name).trigger('nethguitooltip', [{
                        text: field.reason,
                        style: 2,
                        show: true,
                        sticky: true
                    }])
            });
        }
    };

}( jQuery ));
