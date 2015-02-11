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
                $('<ul />', {'class': 'fa-ul'}).appendTo(this.element);
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

            var N = $.nethgui.Notification;
            var ul = $(this.element.children().get(0)).empty();
            $.each(value, function(index, n) {
                var t = N.templates[n.t] ? N.templates[n.t][0] : N.templates['__default__'][0];
                var c = N.templates[n.t] ? N.templates[n.t][1] : N.templates['__default__'][1];
                $('<li />', {'class': 'notification ' + c}).appendTo(ul)
                        .append(Mustache.render(t, n.a))
                        .Component()
                ;
                if(N.callbacks[n.t]) {
                     N.callbacks[n.t].call(self, n)
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
        validationError: function (n) {
            /* FIXME delay tooltip creation until all widgets are rendered */
            window.setTimeout(function () {
                $.each(n.data.fields, function (index, field) {

                    var targets = $('.' + field.name).toArray();
                    var extras = $('#' + field.parameter).toArray();

                    if ($.inArray(extras[0], targets) === -1) {
                        targets.push(extras[0]);
                    }

                    $(targets).trigger('nethguitooltip', [{
                            text: field.reason,
                            style: 2,
                            show: true,
                            sticky: true
                        }])
                });
            }, 20);
        }
    };

}( jQuery ));
