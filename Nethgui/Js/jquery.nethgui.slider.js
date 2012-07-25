/*
 * Slider
 *
 * Relies on a hidden INPUT type text, or a SELECT tag to keep the actual value
 *
 * Copyright (C) 2012 Nethesis S.r.l.
 */
(function( $ ) {
    var SUPER = $.nethgui.InputControl;
    $.widget('nethgui.Slider', SUPER, {
        _deep: false,
        _create: function() {
            SUPER.prototype._create.apply(this);
            
            this._theSlider = $('<div />');
            var self = this;
            var settings;
            var options;

            this._label = this.element.siblings('label');
            this._labelTemplate = this._label.text();
            this._isEnumerative = this.element.hasClass('Enumerative');
                                  
            this.element
            .wrap('<div class="Slider"/>')            
            .removeClass("Slider")
            .hide()
            ;                        

            if(this._isEnumerative) {
                options = this.element.children();                
                settings = {
                    min: 0,
                    max: options.length > 0 ? options.length - 1 : 0,
                    step: 1,
                    value: this.element.children('[selected]').index()
                }
                this._repaintLabel(this.element.children('[selected]').attr('value'))
            } else {
                settings = {
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
            }
                                 
            this._theSlider.slider(settings)
            .insertAfter(this.element)            
            .on('slide', function(event, ui) {
                
                var current;
                
                if(self._isEnumerative) {
                    self.element.children('[selected]').removeAttr('selected');
                    current = self.element.children(':eq(' + ui.value + ')');
                    current.attr('selected', 'selected');
                    self._repaintLabel(current.attr('value'));
                } else {
                    self._repaintLabel(ui.value);
                    self.element.val(ui.value);
                }
            });           
                                    
        },
        _repaintLabel: function(value) {
            if(this._isEnumerative) {
                this._label.text(this._labelTemplate.replacePlaceholders(this.element.children('[value="' + value + '"]').text()));
            } else if($.isNumeric(value)) {
                this._label.text(this._labelTemplate.replacePlaceholders(value));
            }
        },
        _updateView: function(value) {
            SUPER.prototype._updateView.apply(this, [value]);
            if(this._isEnumerative) {
                this._theSlider.slider('option', 'value', this.element.children('[value="' + value + '"]').index());
            } else {
                this._theSlider.slider('option', 'value', value);
            }
            this._repaintLabel(value);
        }
    });
}( jQuery ));
