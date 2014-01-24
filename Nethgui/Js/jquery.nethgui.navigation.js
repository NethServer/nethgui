/*
 * Navigation menu
 *
 * Copyright (C) 2011 Nethesis S.r.l.
 */
(function($) {
    var SUPER = $.nethgui.Action;
    $.widget('nethgui.Navigation', SUPER, {
        _create: function() {
            SUPER.prototype._create.call(this);
            this.element.find('.Button.search')
                    .button({
                        icons: {
                            primary: 'ui-icon-search'
                        },
                        text: false
                    })
                    .removeClass('ui-corner-all');

            this._pending = null;
            this._timer = false;
            this._input = this.element.find('.TextInput')
                    .removeClass('ui-corner-all')
                    .bind("keyup paste", $.proxy(this._submitCheck, this));

            this._form.bind('submit', $.proxy(this._clearTimer, this));
        },
        _clearTimer: function() {
            if (this._timer !== false) {
                window.clearTimeout(this._timer);
                this._timer = false;
            }
        },
        _submitCheck: function(event) {
            var self = this;

            this._clearTimer();

            if (event.keyCode === $.ui.keyCode.ENTER) {
                return true;
            } else if (event.keyCode === $.ui.keyCode.ESCAPE) {
                self._input.val('');
                self._updateView('');
                return true;
            } else if ( ! String.fromCharCode(event.keyCode).match(/[a-z0-9]/i)) {
                return true;
            }

            if (self._input.val().length > 1) {
                this._timer = window.setTimeout(function() {
                    self._form.submit();
                }, 600);
            } else if (self._input.val().length === 0) {
                this._timer = window.setTimeout(function() {
                    self._updateView('');
                }, 300);
            }

        },
        _onSubmit: function(e, restart) {
            e.preventDefault();
            e.stopPropagation();
            var form = $(e.target);

            if (this._pending === null || this._pending.state() === "rejected" || this._pending.state() === "resolved") {
                // start a new request
                this._pending = this._server.ajaxMessage({
                    isMutation: form.attr('method').toUpperCase() === 'POST',
                    url: form.attr('action'),
                    data: form.serialize(),
                    freezeElement: false
                });
            } else if (this._pending.state() === "pending") {
                // cancel pending request
                this._pending.abort();
                // when cancel completes restart this event handler
                this._pending.fail($.proxy(this._onSubmit, this, e, true));
            }

            return false;
        },
        _updateView: function(value) {
            // if the response is empty show all items:
            if (!$.isArray(value) || value.length === 0) {
                this.element.find('.category, li').show();
                return;
            }

            // hide any item that is not member of the `value` array:
            this.element.find('.category, li').each(function(index, element) {
                var href = $(element).children('a:first').attr('href');
                if ($.inArray(href, value) >= 0) {
                    $(element).show();
                } else {
                    $(element).hide();
                }
            });
        }
    });
}(jQuery));
