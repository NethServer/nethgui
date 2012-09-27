/*
 * CollectionEditor
 *
 * Copyright (C) 2012 Nethesis S.r.l.
 */
(function( $ ) {
    var SUPER = $.nethgui.Component;
    
    $.widget('nethgui.CollectionEditor', SUPER, {        
        _deep: false,
        options: {            
            elementActions: [],
            collectionActions: [],
            serialize: null,
            unserialize: function(e, ctx) {
                ctx.object = ctx.line;
            },
            elementView: {
                template: $('<div></div>'),
                build: function(ctx) {
                    this.append('<code/>');
                },
                write: function(ctx) {
                    this.children('code').text(ctx.line);
                }
            }
        },
        _create: function() {
            SUPER.prototype._create.apply(this);
            
            this.configChanged = true;
            this.current = false;

            // hide TEXTAREA:
            this.formControl = this.element.children('textarea').hide();

            // bind to submit event to update TEXTAREA before POSTing data:
            $(this.formControl[0].form).on('submit.' + this.namespace, $.proxy(this._transferToControl, this));
                       
            // get the elements container:
            this.elements = this.element.children('.elements');

            // element action template dock. Template node is moved to
            // the elements collection, when action begins:
            this.templateDock = $('<div class="templateDock"></div>').appendTo(this.element).hide();            
        },
        _init: function() {
            if(this.configChanged === true) {
                this._transferFromControl();
                this.configChanged = false;
            }
        },
        _updateView: function(value, selector) {
            SUPER.prototype._create.call(this, value, selector);
            this.formControl.val(value);
            this._transferFromControl();
        },
        /**
         * Build collection elements from formControl value
         */
        _transferFromControl: function() {
            if(this._isActionActive()) {
                this.endElementAction(false);
            }
            this.elements.empty();
            var lines = this.formControl.val().split("\n");
            for(var i = 0; i < lines.length; i++) {
                this._addElement(lines[i], true, false);
            }
        },
        /**
         * Transfer element data into formControl
         */
        _transferToControl: function(e) {
            if(this._isActionActive()) {
                this.endElementAction(false);
            }
            var self = this;
            var lines = [];
            this.elements.children().each(function(index, node) {
                var ctx = $(node).data('CollectionEditorContext');
                self._trigger('serialize', undefined, ctx);
                lines.push(ctx.line);
            });
            this.formControl.val(lines.join("\r\n"));
        },
        _setOption: function(key, value) {            
            if(key === 'collectionActions' && value !== undefined) {
                this._setCollectionActionsOption(value);
            }
            if(key === 'unserialize' || key === 'elementView') {
                this.configChanged = true;
            }
            if(key === 'elementActions') {
                this._initElementActions(value);
            }
            SUPER.prototype._setOption.apply( this, arguments );
        },
        _initElementActions: function(value) {
            var self = this;
            this.templateDock.empty();
            $.each(value, function(index, action) {
                if($.isFunction(action.view)) {
                    return true;
                }
                action.view.template.appendTo(self.templateDock);
                action.view.build.call(action.view.template, action);
                action.view.template.Component();
            });
        },
        _setCollectionActionsOption: function(value) {
            var self = this;

            this.element.children('.Buttonset').remove();

            var buttonset = $('<div class="Buttonset v1" />');

            buttonset.prependTo(this.element);

            $.each(value, function(index, action) {
                var button = $('<button class="Button" type="button">' + action.label + '</button>');
                if($.isFunction(action.click)) {
                    button.on('click.' + self.widgetName, $.proxy(action.click, this.elements));
                }
                buttonset.append(button);
            });

            buttonset.Buttonset();
        },
        addElement: function(line, prepend, actionName) {
            var ctx = this._addElement(line, false, prepend);

            if(ctx && actionName) {
                this.beginElementAction(actionName, ctx);
            }
        },
        _addElement: function(line, persistent, prepend) {
            var ctx = {
                line: line,
                object: null,
                element: null,
                persistent: persistent
            };

            var view = this.options.elementView;

            this._trigger('unserialize', undefined, ctx);

            if( ! ctx.object ) {
                // Skip element creation if unserialize fails:
                return undefined;
            }

            ctx.element = this.options.elementView.template.clone(true);
            ctx.element[prepend === true ? 'prependTo' : 'appendTo'](this.elements);
            ctx.element.data('CollectionEditorContext', ctx);
            
            view.build.call(ctx.element, ctx);

            // transfer object data into element view:
            view.write.call(ctx.element, ctx);

            ctx.element.Component();

            return ctx;

        },
        _getAction: function(actionName) {
            for(var i = 0; i < this.options.elementActions.length; i++) {
                var action = this.options.elementActions[i];
                if(action.name === actionName) {
                    return action;
                }
            }
            return null;
        },
        _isActionActive: function() {
            return typeof this.current === 'object';
        },
        beginElementAction: function(actionName, ctx) {

            if(this._isActionActive()) {
                this.endElementAction(false);
            }

            var action = this._getAction(actionName);
            if(action === null) {
                return undefined;
            }
            var view = action.view;

            // Case 1: view is a function
            if($.isFunction(view)) {
                return view.call(action, ctx);
            }

            // Case 2: view is an object with template, read, write
            // properties
            if($.isFunction(view.write)
                && view.template instanceof jQuery) {
                ctx.element.after(view.template);
                ctx.element.hide();
                this.current = {
                    action: action,
                    context: ctx
                };
                // Transfer data into template
                view.write.call(view.template, ctx);
            }

            return undefined;
        },
        endElementAction: function(save) {
            var ctx = this.current.context;
            var view = this.current.action.view;

            if(save === true || save === undefined) {
                ctx.persistent = true;
                view.read.call(view.template, ctx);
                this.options.elementView.write.call(ctx.element, ctx);
            }

            if(ctx.persistent) {
                ctx.element.show();
            } else {
                ctx.element.remove();
            }
            view.template.detach();
        }
    });
            
}( jQuery ));
