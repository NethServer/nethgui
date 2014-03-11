/*
 * ObjectsCollection
 *
 * Copyright (C) 2013 Nethesis S.r.l.
 */
(function($) {
    var SUPER = $.nethgui.Component;

    $.widget('nethgui.ObjectsCollection', SUPER, {
        _deep: true,
        _create: function() {
            this.state = $.extend({rendered: false, template: '', ifEmpty: ''}, this.element.attr('data-state') ? $.parseJSON(this.element.attr('data-state')) : {});
            SUPER.prototype._create.apply(this);
        },
        _updateView: function(value, selector) {
            SUPER.prototype._updateView.call(this, value, selector);
            var self = this;
            self.element.empty();

	    if( ! $.isArray(value)) {
		value = [];
	    }

            $.each(value, function(index, record) {
                var node = $(self.state.template.replacePlaceholders({'key': record[self.state.key]}));
                node.appendTo(self.element);
                SUPER.prototype._initializeDeep.call(self, node.toArray());
                $.each(record, function(rkey, rval) {                    
                    if (node.hasClass(rkey)) {
                        node.triggerHandler('nethguiupdateview', [rval, rkey, null]);
                    }
                    node.find('.' + rkey).each(function(index, tgt) {                        
                        $(tgt).triggerHandler('nethguiupdateview', [rval, rkey, null]);
                    });
                    
                });
            });

            if(self.element.children().length === 0) {
                self.element.html(self.state.ifEmpty);
            }

        }
    });

}(jQuery));
