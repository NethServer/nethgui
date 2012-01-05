/*
 * Controller
 *
 * Copyright (C) 2011 Nethesis S.r.l.
 */
(function( $ ) {
    var SUPER = $.nethgui.Component;
    $.widget('nethgui.Controller', SUPER, {
        _create: function () {
            SUPER.prototype._create.apply(this);
            this.actionHistory = [];
            this.element.children('ul.ActionList').remove();
            this.element.bind('nethguishow.' + this.namespace, $.proxy(this._onShow, this));
            this.element.bind('nethguicancel.' + this.namespace, $.proxy(this._onCancel, this));
            this.element.children('.Action:eq(0)').trigger('nethguishow');
        },
        _onShow: function (e, cancel) {
            if(cancel !== true) {
                this.actionHistory.push(e.target.id);
            }
            this.getChildren().not('#' + e.target.id).trigger('nethguihide');
        },
        _onCancel: function () {
            this.actionHistory.pop(); // pops the current action
            var id = this.actionHistory[this.actionHistory.length - 1];
            if(id === undefined) {
                return;
            }                        
            this.getChildren().filter('#' + id).trigger('nethguishow', true);
        }
    });
}( jQuery ));
/*
 * Action
 *
 * Copyright (C) 2011 Nethesis S.r.l.
 */
(function( $ ) {
    var SUPER = $.nethgui.Component;
    $.widget('nethgui.Action', SUPER, {
        _create: function () {
            SUPER.prototype._create.apply(this);

            var behaviour = this.element.hasClass('Dialog') ? 'Dialog' : 'Default';

            this.element.bind('nethguishow.' + this.namespace, $.proxy(this['_onShow' + behaviour], this));
            this.element.bind('nethguihide.' + this.namespace, $.proxy(this['_onHide' + behaviour], this));
            this.element.children('form').bind('submit.' + this.namespace, $.proxy(this._onSubmit, this));
        },
        _onSubmit: function (e) {
            e.preventDefault();
            e.stopPropagation();            
            var form = $(e.target);
            this._sendMutation(form.attr('action'), form.attr('method'), form.serialize());
            return false;
        },
        _onHideDefault: function (e) {
            this.element.hide();
        },
        _onShowDefault: function (e) {
            this.element.show();
        },
        _onHideDialog: function (e) {
            if(this._dialog === undefined) {
                this._initDialog();
            }
            if(this._dialog.dialog('isOpen')) {
                this._dialog.dialog("close");                
            }
            e.stopPropagation();
        },
        _onShowDialog: function (e) {
            if(this._dialog === undefined) {
                this._initDialog();
            }
            if(!this._dialog.dialog('isOpen')) {
                this._dialog.dialog("open");             
            }
            e.stopPropagation();
        },
        _initDialog: function() {
            var self = this;
            
            var titleNode = this.element.find('h1, h2, h3').first().bind('nethguichanged.' + this.namespace, function(e, value) {
                self._dialog.dialog('option', 'title', value);
                $.debug('Dialog Title', value);
            }).hide();

            var content = this.element.children().detach();

            this._dialog = $('<div class="Dialog"></div>');
            this.element.append(this._dialog).hide();
            this._dialog.append(content).dialog({
                modal:true,
                autoOpen: false,
                position: ['center', 50],
                title: titleNode.text()
            }).bind('nethguicancel.' + this.namespace, $.proxy(this._onHideDialog, this));
        }
    });
}( jQuery ));
/*
 * Form
 *
 * Copyright (C) 2011 Nethesis S.r.l.
 */
(function( $ ) {
    var SUPER = $.nethgui.Component;
    $.widget('nethgui.Form', SUPER, {
        _updateView: function (value) {
            this.element.attr('action', value);
        }
    });
}( jQuery ));
