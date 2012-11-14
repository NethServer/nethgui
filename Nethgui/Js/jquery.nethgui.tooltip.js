/*
 * Tooltip
 *
 * Copyright (C) 2011 Nethesis S.r.l.
 */
(function( $ ) {
    var SUPER = $.nethgui.Component;
    $.widget('nethgui.Tooltip', SUPER, {
        _deep: false,
        options: {
            sticky: false,
            show: false,
            color: 'blue',
            style: 0,
            text: '',
            destroyOn: 'ajaxStart',
            target: false
        },
        _create: function() {
            SUPER.prototype._create.apply(this);
            var self = this;

            // error-state forces color to "red"
            if(this.options.style & 2) {
                this.element.addClass('ui-state-error');
                this.options.color = 'red';
            }

            this.element.qtip({
                position: {
                    my: 'left center',
                    at: 'right center',
                    container: this.element.parents('.ui-tabs-panel, .Action, #CurrentModule, .Inset').first(),
                    target: this.option('target')
                },
                style: {
                    classes: 'ui-tooltip-${color} ui-tooltip-shadow'.replacePlaceholders({
                        color: this.options.color
                    })
                },
                content: {
                    text: this.options.text
                },
                events: {
                    hide: this.options.sticky ? function (e, api) {
                        e.preventDefault()
                    } : undefined
                }
            });

            if(this.options.show === true) {
                this.show();
            }

            if(typeof this.options.destroyOn === 'string') {
                this.element.bind(this.options.destroyOn.split(' ').join('.' + this.namespace + ' ').trim(), function (e) {
                    self.destroy();
                } );
            }
        },
        show: function() {
            this.element.qtip('show');
        },
        hide: function() {
            this.element.qtip('hide');
        },
        repaint: function() {
            this.element.qtip('redraw').qtip('reposition', undefined, false);
        },
        destroy: function () {
            SUPER.prototype.destroy.apply(this);
            this.element.qtip('destroy');
            if(this.options.style & 2) {
                this.element.removeClass('ui-state-error');
            }
        }
    });
}( jQuery ) );
