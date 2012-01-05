/*
 * Notification
 *
 * Copyright (C) 2011 Nethesis S.r.l.
 */
(function( $ ) {
    var SUPER = $.nethgui.Component;
    $.widget('nethgui.Notification', SUPER, {
        _deep: true,
        _create: function() {
            SUPER.prototype._create.apply(this);
            var self = this;
            this.counter = 0;
            this.element.bind('nethguishownotification.' + this.namespace, $.proxy(this.showNotification, this));
            this.element.bind('nethguishowmessage.' + this.namespace, function(e, text, style) {
                self.counter ++;
                self.showNotification(e, {
                    identifier: 'MessageInstance' + self.counter,
                    title: text,
                    message: '',
                    style: style,
                    type: 'Message',
                    'transient': true
                });
            });
        },
        showNotification: function(e, v) {

            if(v === undefined || v.identifier === undefined) {
                return;
            }

            var notification = $('<div></div>', {
                id: v.identifier
            });
            
            this.element.append(notification);

            if(v.style & 0x4) {
                // FIXME: consider this call as a placeholder!
                notification.dialog({
                    title: v.title
                });
            }

            if($.isFunction($.fn[v.type])) {
                $.fn[v.type].call(notification, {
                    data: v
                });
            } else {
                notification.Message({
                    data: v
                });
            }
        }
    });

    $(document).ready(function() {
        $('#Notification').Notification();
    });
}( jQuery ));

/*
 * Message
 *
 * Copyright (C) 2011 Nethesis S.r.l.
 */
(function( $ ) {
    var SUPER = $.nethgui.Component;
    $.widget('nethgui.Message', SUPER, {
        _deep: true,
        options: {
            data: {}
        },
        _create: function() {           
            var self = this;

            if(!$.isEmptyObject(this.options.data)) {
                this._createContent(this.options.data);
            }
            SUPER.prototype._create.apply(this);

            // Destroy on next ajax call if has transient class:
            if(this.element.hasClass('transient')) {
                this.element.bind('ajaxStart.' + this.namespace, function(e) {
                    self.element.fadeOut(function(){
                        $(this).remove()
                    })
                });
            }

            // Add close button
            this.element.append($("<span class='Notification_close ui-icon ui-icon-close'></span>").click(function() {
                self.element.remove()
            }).button());
        },
        _createContent: function(v) {
            var template = "<span class='TextLabel Notification_title'><span class='ui-icon ${severityIcon}'></span>${title}</span> ${message}";

            this.element.addClass(v.type);

            if(v.style & 0x1) {
                this.element.addClass('ui-state-error');
                v.severityIcon = 'ui-icon-alert';
            } else if(v.style & 0x2) {
                this.element.addClass('ui-state-error');
                v.severityIcon = 'ui-icon-alert';
            } else {
                this.element.addClass('ui-state-success');
                v.severityIcon = 'ui-icon-info';
            }

            if(v['transient'] === true) {
                this.element.addClass('transient');
            }

            this.element.append(template.replacePlaceholders(v));
        }
    });
}) ( jQuery );

/*
 * ValidationErrors
 *
 * Copyright (C) 2011 Nethesis S.r.l.
 */
(function( $ ) {
    var SUPER = $.nethgui.Message;
    $.widget('nethgui.ValidationErrors', SUPER, {
        _deep: true,
        _create: function() {
            var self = this;
            var v = this.options.data;
            var errors = '';

            $.each(v.errors, function (undefined, err) {
                errors += '<a class="Button givefocus" href="#${widgetId}">${label}</a> '.replacePlaceholders(err);
                self._addTooltip(err);
            });

            v.message = errors;

            this.element.bind('ajaxStart.' + this.namespace, $.proxy(this._clearTooltips, this));
            
            SUPER.prototype._create.apply(this);           
        },
        _addTooltip: function(err) {
            $('#' + err.widgetId).trigger('nethguitooltip', {
                text: err.message,
                style: 2
            });
        },
        _clearTooltips: function() {
            var self = this;
            $.each(this.options.data.errors, function(index, err) {
                $('#' + err.widgetId).trigger('nethguitooltip');
            });
        }
    });
}) ( jQuery );
