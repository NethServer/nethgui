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

                settings.value = this.element.prop('value') ? this.element.prop('value') : settings.min;
            }
            
            if(this.element.hasClass('keepdisabled')) {
                settings.disabled = true;
            }
                                 
            this._theSlider.slider(settings)
            .insertAfter(this.element)            
            .on('slide slidechange', function(event, ui) {
                                
                var valueLabel = '';
                
                if(self._isEnumerative) {
                    self.element.children('[selected]').prop('selected', false);
                    var current = self.element.children(':eq(' + ui.value + ')');
                    current.prop('selected', true);
                    valueLabel = current.text();
                } else {
                    self.element.val(ui.value);
                    valueLabel = ui.value;
                }

                self._label.triggerHandler('nethguiupdateview', [valueLabel]);
            });           

            // update the label
            self._label.triggerHandler('nethguiupdateview', [this._getValueLabel(settings.value)]);
        },
        _setOption: function( key, value ) {
            SUPER.prototype._setOption.apply( this, arguments );
            if(key === 'disabled' && ! this.element.hasClass('keepdisabled')) {
                this._theSlider.slider('option', 'disabled', value);
            }
        },        
        _getValueLabel: function(value) {
            var label;
            if(this._isEnumerative) {
                // value is the child ordinal number:
                label = this.element.children(':eq(' + value + ')').text();
            } else if($.isNumeric(value)) {
                label = value;
            }
            return label;
        },
        _updateView: function(value) {
            SUPER.prototype._updateView.apply(this, [value]);
            if(this._isEnumerative) {
                $.debug(value, this.element.children('[value="' + value + '"]').index());
                this._theSlider.slider('option', 'value', this.element.children('[value="' + value + '"]').index());
            } else {
                this._theSlider.slider('option', 'value', value);
            }
        },
        _createTooltip: function(e, options) {
            options.target = this.element.next('.ui-slider');
            SUPER.prototype._createTooltip.call(this, e, options);
        }
    });
}( jQuery ));
