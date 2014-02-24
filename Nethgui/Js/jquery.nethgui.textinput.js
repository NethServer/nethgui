/*
 * TextInput
 */
(function( $ ) {
    var SUPER = $.nethgui.InputControl;
    $.widget('nethgui.TextInput', SUPER, {
        _deep: false,
        _create: function() {
            SUPER.prototype._create.apply(this);
            // Attach datepicker to Date input fields:
            if(this.element.hasClass('Date')) {
                if(this.element.hasClass('le')) {
                    this.element.datepicker({
                        dateFormat:'dd/mm/yy'
                    });
                } else if(this.element.hasClass('me')) {
                    this.element.datepicker({
                        dateFormat:'mm-dd-yy'
                    });
                } else {
                    this.element.datepicker({
                        dateFormat:'yy-mm-dd'
                    });
                }
            }
            this._onContentChange();
            this.element.on('nethguimandatory.' + this.namespace + ' keyup.' + this.namespace, $.proxy(this._onContentChange, this));
        },
        _onContentChange: function() {
            if(this.element.hasClass('mandatory') && ! this.element.val()) {
                this.element.addClass('active');
            } else {
                this.element.removeClass('active');
            }
        }
    });
}( jQuery ) );
