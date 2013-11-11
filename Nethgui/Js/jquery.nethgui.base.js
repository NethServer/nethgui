/*
 * Nethgui Js Framework
 *
 * Copyright (C) 2011 Nethesis S.r.l.
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @author Giovanni Bezicheri <giovanni.bezicheri@nethesis.it>
 * @author Giacomo Sanchietti <giacomo.sanchietti@nethesis.it>
 */

(function($) {

    var counter = 0;

    /**
     * Replaces ${0} .. ${N} substrings with the corresponding
     * function argument. Returns the strings where the placeholders are
     * substituted.
     *
     *
     * Example:
     *   var s = "Hello ${0} ${1}!";
     *
     *   s = s.replacePlaceholders("John", "Doe");
     *
     *   document.write(s);
     *
     * Writes "Hello John Doe!".
     */
    if (!String.prototype.replacePlaceholders) {
        String.prototype.replacePlaceholders = function(o) {
            var s = this;

            if (o === undefined) {
                return s.toString();
            } else if (typeof (o) !== 'object') {
                o = [o].concat(Array.prototype.slice.call(arguments, 1));
            }

            for (var i in o) {
                s = s.replace(new RegExp('\\$\\{' + i + '\\}', 'g'), o[i]);
            }
            return s.toString();
        }
    }


    if ($.debug === undefined) {
        $.extend({
            debug: function() {
                typeof (console) == 'object' && console.log.apply(console, arguments);
            }
        });
    }


    var Server = function() {
    };

    /**
     * Check if url is in the same domain of the current page
     */
    Server.prototype.isLocalUrl = function(url) {
        // site-root relative urls are always accepted:
        if (url.charAt(0) === '/' || url === '') {
            return true;
        }

        var currentUrlParts = window.location.href.split('/');
        var urlParts = url.split('/');

        if (!$.isArray(currentUrlParts) || !$.isArray(urlParts)) {
            return false;
        }

        for (var i = 0; i < 3; i++)
        {
            if (currentUrlParts[i] != urlParts[i]) {
                alert('Url is not local: `' + url + '`.');
                return false;
            }
        }

        return true;
    };

    Server.prototype.processResponse = function(response, statusCode) {
        $.each(response, function(index, item) {
            if (item === null) {
                return;
            }

            var selector = item[0];
            var value = item[1];

            if (selector === '__COMMANDS__') {
                $.each(value, function(index, command) {
                    if (command.R === 'Main') {
                        $(document).trigger('nethgui' + command.M.toLowerCase(), command.A);
                    } else {
                        $('#' + command.R).trigger('nethgui' + command.M.toLowerCase(), command.A);
                    }
                });
            } else {
                $('.' + selector).each(function(index, element) {
                    $(element).triggerHandler('nethguiupdateview', [value, selector, statusCode]);
                });
            }
        });
    };

    /**
     * Perform an AJAX request on given URL
     */
    Server.prototype.ajaxMessage = function(params) {
        var isMutation, url, data, freezeElement, dispatchError, dispatchResponse, formatSuffix, isCacheEnabled;
        var self = this;

        isMutation = params.isMutation;
        url = params.url;
        data = params.data;
        freezeElement = params.freezeElement;
        formatSuffix = params.formatSuffix ? params.formatSuffix : 'json';
        isCacheEnabled = params.isCacheEnabled ? true : false;


        /**
         * Send the response containing the view data to controls
         */
        dispatchResponse = $.isFunction(params.dispatchResponse) ? params.dispatchResponse : function(response, textStatus, jqXHR) {
            if ($.isArray(response)) {
                self.processResponse(response, jqXHR.status);
            } else {
                $('<pre></pre>').text(jqXHR.responseText).dialog({
                    modal: true,
                    buttons: [
                        {
                            text: "Ok",
                            click: function() {
                                $(this).dialog("close").dialog("destroy").remove();
                            }
                        }
                    ],
                    title: 'Unexpected response entity! HTTP Status ' + jqXHR.status + ' - ' + jqXHR.statusText
                })
            }
        };

        waitAndRetry = function(delay, settings) {
            if (settings.type != 'GET') {
                return false;
            }

            window.setTimeout(function() {
                $.ajax(settings);
            }, delay);

            return true;
        };

        confirmReload = function(title, message, settings) {
            var buttons = [
                {
                    text: "Reload page",
                    click: function() {
                        window.location.reload(true);
                        $(this).dialog("close").dialog("destroy").remove();
                    }
                }
            ];
            if (settings.type === 'GET') {
                buttons.push({
                    text: "Try again",
                    click: function() {
                        waitAndRetry(100, settings);
                        $(this).dialog("close").dialog("destroy").remove();
                    }
                });
            }
            $('<pre></pre>').text(message).dialog({
                modal: true,
                buttons: buttons,
                title: title
            });
        }

        dispatchError = $.isFunction(params.dispatchError) ? params.dispatchError : function(jqXHR, textStatus, errorThrown) {
            this.failures += 1;
            if (jqXHR.status == 400 && (errorThrown == "Request validation error" || errorThrown == "Invalid credentials supplied")) {
                dispatchResponse($.parseJSON(jqXHR.responseText), errorThrown, jqXHR);
            } else if (jqXHR.status == 403 && errorThrown === 'Forbidden') {
                $('<pre></pre>').text(jqXHR.responseText).dialog({
                    modal: true,
                    buttons: [
                        {
                            text: "Ok",
                            click: function() {
                                window.location.reload(true);
                                $(this).dialog("close").dialog("destroy").remove();
                            }
                        }
                    ],
                    title: '403 - Forbidden'
                });
            } else if (jqXHR.status == 0) {
                if (this.failures > 10) {
                    this.failures = 0;
                    confirmReload("Connection ERROR", "The remote server is not reachable.", this);
                } else {
                    waitAndRetry(5000, this);
                }
            } else if (jqXHR.status == 404) {
                if (this.failures > 1) {
                    this.failures = 0;
                    confirmReload("ERROR 404", jqXHR.responseText, this);
                } else {
                    waitAndRetry(5000, this);
                }
            } else {
                confirmReload("Server error", jqXHR.responseText, this);
                $.debug(errorThrown);
                throw errorThrown;
            }
        };


        /**
         * Replace the path suffix on the given url with newSuffix
         * @return string the new url string
         */
        var replaceFormatSuffix = function(url, newSuffix) {

            var urlParts = url.split('?', 2);
            var pathParts = urlParts[0].split('/');
            var lastPart = pathParts.pop();

            if (/.+\.(x?html|json)$/.test(lastPart)) {
                lastPart = lastPart.substr(0, lastPart.lastIndexOf('.')) + '.' + newSuffix;
            } else {
                lastPart += '.' + newSuffix;
            }

            if (urlParts[1] !== undefined) {
                lastPart += '?' + urlParts[1];
            }

            pathParts.push(lastPart);

            return pathParts.join('/');
        };

        if (!this.isLocalUrl(url)) {
            return;
        }

        if (freezeElement instanceof jQuery) {
            freezeElement.trigger('nethguifreezeui');
        }

        var settings = {
            url: replaceFormatSuffix(url, formatSuffix),
            type: isMutation ? 'POST' : 'GET',
            cache: isCacheEnabled,
            data: data,
            success: dispatchResponse,
            error: dispatchError,
            crossDomain: false,
            failures: 0
        };
        return $.ajax(settings);
    };


    var Translator = function() {
        this.catalog = {};
    };

    Translator.prototype.translate = function(message) {
        if (typeof this.catalog[message] === "string") {
            message = this.catalog[message];
        }
        return '' + String.prototype.replacePlaceholders.apply(message, Array.prototype.slice.call(arguments, 1));
    };

    Translator.prototype.extendCatalog = function(extension) {
        this.catalog = $.extend(this.catalog, extension);
        return this;
    };

    $.Nethgui = {
        Server: new Server(),
        Translator: new Translator(),
        T: function() {
            return $.Nethgui.Translator.translate.apply($.Nethgui.Translator, Array.prototype.slice.call(arguments, 0));
        }
    };

    $.widget('nethgui.Component', {
        _server: $.Nethgui.Server,
        _deep: true,
        _showDisabledState: true,
        _propagateDisabledState: true,
        _create: function() {
            var self = this;

            // language translation function:
            this.T = this.translate;

            this.widgetEventPrefix = this.namespace;
            this._id = ++counter;
            this._children = [];

            if (this._deep === true) {
                this._initializeDeep(this.element.children().toArray());
            }
            this.element.bind('nethguiupdateview.' + this.namespace, function(e, value, selector) {
                self._updateView(value, selector);
            });
            this.element.bind('nethguienable.' + this.namespace, function(e) {
                self.enable();
                e.stopPropagation();
            });
            this.element.bind('nethguidisable.' + this.namespace, function(e) {
                self.disable();
                e.stopPropagation();
            });
        },
        getChildren: function() {
            return $(this._children);
        },
        _getChildNodes: function() {
            return this._children;
        },
        _findType: function(jqNode) {
            var typeFound = false;
            // Check whether any class of the node is defined in nethgui namespace:
            $.each(jqNode.prop('class').split(/\s+/), function(index, typeName) {
                if (typeName in $.nethgui && jqNode.data('nethgui') === undefined) {
                    typeFound = typeName;
                    return false;
                }
            });
            return typeFound;
        },
        /**
         * Find and initialize any descendant component
         */
        _initializeDeep: function(nodeQueue) {

            var node = nodeQueue.shift();
            var typeName = false;

            // iterate on descendant nodes: if a component is found,
            // initialize it and discard its branch.
            while (node !== undefined) {
                var jqNode = $(node);

                typeName = this._findType(jqNode)
                if (typeName !== false) {
                    // constructor call:
                    if (typeName in $.fn) {
                        $.fn[typeName].apply(jqNode);
                    } else {
                        $.debug('Undefined type ' + typeName);
                    }
                    this._children.push(node);
                } else {
                    Array.prototype.push.apply(nodeQueue, jqNode.children().toArray());
                }
                node = nodeQueue.shift();
            }
        },
        _setOption: function(key, value) {
            if (key === 'disabled' && this.element.hasClass('keepdisabled')) {
                return;
            }
            if (key !== 'disabled' || this._showDisabledState === true) {
                $.Widget.prototype._setOption.apply(this, [key, value]);
            }
            if (key === 'disabled' && this._deep === true && this._propagateDisabledState === true) {
                this.getChildren().trigger('nethgui' + (value ? 'disable' : 'enable'));
            }
        },
        destroy: function() {
            $.Widget.prototype.destroy.call(this);
            this.widget().unbind(this.namespace);
            this.element.unbind(this.namespace);
            this._children = undefined;
        },
        _updateView: function(value, selector) {
            // free to override
        },
        _sendQuery: function(url, data, freezeUi) {
            this._server.ajaxMessage({
                isMutation: false,
                url: url,
                data: typeof data === 'string' ? data : undefined,
                freezeElement: freezeUi ? this.widget() : undefined
            });
        },
        _sendMutation: function(url, data, freezeUi) {
            this._server.ajaxMessage({
                isMutation: true,
                url: url,
                data: typeof data === 'string' ? data : undefined,
                freezeElement: freezeUi ? this.widget() : undefined
            });
        },
        _readHelp: function(url) {

        },
        translate: function() {
            return Translator.prototype.translate.apply($.Nethgui.Translator, Array.prototype.slice.call(arguments, 0))
        },
        startThrobbing: function() {
            this.element.hide();
            this.element.after("<div class='throbber'>Loading...</div>");
        },
        endThrobbing: function() {
            this.element.show();
            this.element.next('.throbber').remove();
        }
    });


    $(document).bind('nethguisendquery.nethgui', function(e, url, delay, freezeUi) {
        var server = new Server();
        if (server.isLocalUrl(url)) {
            window.location = url;
        }
    });

}(jQuery));
