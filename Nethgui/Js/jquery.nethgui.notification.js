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
                    type: 'TextNotification',
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
                notification.TextNotification({
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
 * TextNotification
 *
 * Copyright (C) 2011 Nethesis S.r.l.
 */
(function( $ ) {
    var SUPER = $.nethgui.Component;
    $.widget('nethgui.TextNotification', SUPER, {
        _deep: true,
        options: {
            data: {},
            template: "<span class='Notification_title'><span class='ui-icon ${severityIcon}'></span>${title}</span><span class='Notification_close ui-icon ui-icon-close'></span>"
        },
        _create: function() {           
            var self = this;

            if(!$.isEmptyObject(this.options.data)) {
                this._createContent(this.options.data);
            }

            this.element.append(this.option('template').replacePlaceholders(this.option('data')));

            SUPER.prototype._create.apply(this);

            // Destroy on next ajax call if has transient class:
            if(this.element.hasClass('transient')) {
                this.element.one('ajaxStart.' + this.namespace, function(e) {
                    self.element.fadeOut(function() {
                        $(this).remove()
                    });
                });
            }

            // Add close button
            this.element.append($("<span class='Notification_close ui-icon ui-icon-close'></span>").click(function() {
                self.element.remove()
            }).button());
        },
        _createContent: function(v) {
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
        }
    });
}) ( jQuery );

/*
 * ValidationErrors
 *
 * Copyright (C) 2011 Nethesis S.r.l.
 */
(function( $ ) {
    var SUPER = $.nethgui.TextNotification;
    $.widget('nethgui.ValidationErrors', SUPER, {
        _deep: true,
        options: {
            template: "<span class='Notification_title'><span class='ui-icon ${severityIcon}'></span>${title}</span> ${fieldLinks}<span class='Notification_close ui-icon ui-icon-close'></span>"
        },
        _createContent: function(v) {            
            SUPER.prototype._createContent.call(this, v);
            var self = this;

            v.fieldLinks = '';
            // Render v.fieldLinks placeholder content:
            $.isArray(v.errors) && $.each(v.errors, function (index, err) {

                if($('#' + err.widgetId).length > 0) {
                    v.fieldLinks += '<a class="Button givefocus" href="#${widgetId}">${label}</a> '.replacePlaceholders(err);
                    self._addTooltip(err);
                } else {
                    v.fieldLinks += '<span>${label} (${message})</span> '.replacePlaceholders(err);
                }
            });            
        },
        _addTooltip: function(err) {
            $('#' + err.widgetId).triggerHandler('nethguitooltip', [{
                text: err.message,
                style: 2,
                show: true,
                sticky: true
            }]);
        }
    });
}) ( jQuery );
