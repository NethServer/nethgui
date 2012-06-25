/*
 * Slider
 *
 * Copyright (C) 2012 Nethesis S.r.l.
 */
(function( $ ) {
    var SUPER = $.nethgui.InputControl;
    $.widget('nethgui.Slider', SUPER, {
        _deep: false,
        _create: function() {
            SUPER.prototype._create.apply(this);
            
            var theSlider = $('<div />');
            var self = this;
            var settings = {
                min: 0,
                max: 100,
                step: 1                
            };
            
            try {
                $.extend(settings, $.parseJSON(this.element.attr('data-settings')));
            } catch (e) {
                $.debug('Slider: malformed "data-settings" attribute; got ' + this.element.attr('data-settings'), e);
            }          
            
            settings.value = settings.min;
            
            this._label = this.element.siblings('label');
            this._labelTemplate = this._label.text();
                                   
            this.element
            .wrap('<div class="Slider"/>')            
            .removeClass("Slider")
            .hide()
            ;                        
                        
            theSlider.slider(settings)
            .insertAfter(this.element)            
            .on('slide', function(event, ui) {
                self._repaintLabel(ui.value);
                self.element.val(ui.value);
            });           
            
            this._repaintLabel(settings.value);
        },
        _repaintLabel: function(value) {
            if($.isNumeric(value)) {
                this._label.text(this._labelTemplate.replacePlaceholders(value));
            }
        },
        _updateView: function(value) {
            SUPER.prototype._updateView.apply(this, [value]);
            this._repaintLabel(value);
        }
    });
}( jQuery ));
