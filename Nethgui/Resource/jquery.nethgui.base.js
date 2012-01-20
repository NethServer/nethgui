/*
 * Nethgui Js Framework
 *
 * Copyright (C) 2011 Nethesis S.r.l.
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @author Giovanni Bezicheri <giovanni.bezicheri@nethesis.it>
 * @author Giacomo Sanchietti <giacomo.sanchietti@nethesis.it>
 */

(function( $ ) {

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
    if( ! String.prototype.replacePlaceholders ) {
        String.prototype.replacePlaceholders = function(o) {
            var s = this;

            if(o === undefined) {
                return s;
            } else if(typeof(o) !== 'object') {
                o = [o].concat(Array.prototype.slice.call(arguments, 1));
            }

            for (var i in o) {
                s = s.replace('${' + i + '}' , o[i]);
            }
            return s;
        }
    }
    

    if($.debug === undefined) {
        $.extend({
            debug: function () {
                typeof(console) == 'object' && console.log.apply(console, arguments);
            }
        });
    }


    var Server = function() {};

    /**
     * Check if url is in the same domain of the current page
     */
    Server.prototype.isLocalUrl = function (url) {
        // site-root relative urls are always accepted:
        if(url.charAt(0) === '/' || url === '') {
            return true;
        }

        var currentUrlParts = window.location.href.split('/');
        var urlParts = url.split('/');

        if(!$.isArray(currentUrlParts) || !$.isArray(urlParts)) {
            return false;
        }

        for(var i = 0; i < 3; i++)
        {
            if(currentUrlParts[i] != urlParts[i]) {
                alert('Url is not local: `' + url + '`.');
                return false;
            }
        }

        return true;
    }

    /**
     * Perform an AJAX request on given URL
     */
    Server.prototype.ajaxMessage = function(isMutation, url, data, freezeElement) {

        /**
         * Send the response containing the view data to controls
         */
        var dispatchResponse = function (response, status, httpStatusCode) {
            if(!$.isArray(response)) {
                alert('Unexpected response format. Please, reload the current page.');
                throw 'Unexpected response format';
            }

            if(httpStatusCode === undefined) {
                httpStatusCode = 200;
            }

            $.each(response, function (index, item) {
                if(item === null) {
                    return;
                }

                var selector = item[0];
                var value = item[1];

                if(selector === '__COMMANDS__') {
                    $.each(value, function (index, command) {
                        if(command.R === 'Main') {
                            $(document).trigger('nethgui' + command.M.toLowerCase(), command.A);
                        } else {
                            $('#' + command.R).trigger('nethgui' + command.M.toLowerCase(), command.A);
                        }
                    });
                } else {
                    $('.' + selector).each(function(index, element) {
                        $(element).triggerHandler('nethguiupdateview', [value, selector, httpStatusCode]);
                    });
                }
            });
        };

        var dispatchError = function(jqXHR, textStatus, errorThrown) {
            if(jqXHR.status == 400 && errorThrown == "Request validation error") {
                dispatchResponse($.parseJSON(jqXHR.responseText), textStatus, jqXHR.status);
            } else {
                // TODO: display a way to recover from the error state.
                $.debug('Server error.', jqXHR, textStatus, errorThrown);
                alert('Server reported an error. Don\'t know how to recover: please, reload the page.');
                throw 'Server error. Don\'t know how to recover';
            }
        }


        /**
         * Replace the path suffix on the given url with newSuffix
         * @return string the new url string
         */
        var replaceFormatSuffix = function(url, newSuffix) {

            var urlParts = url.split('?',2);
            var pathParts = urlParts[0].split('/');
            var lastPart = pathParts.pop();

            if(/.+\.(x?html|json)$/.test(lastPart)) {
                lastPart = lastPart.substr(0, lastPart.lastIndexOf('.')) + '.' + newSuffix;
            } else {
                lastPart += '.' + newSuffix;
            }

            if(urlParts[1] !== undefined) {
                lastPart += '?' + urlParts[1];
            }

            pathParts.push(lastPart);

            return pathParts.join('/');
        };

        if( ! this.isLocalUrl(url)) {
            return;
        }

        if(freezeElement instanceof jQuery) {
            freezeElement.trigger('nethguifreezeui');
        }

        $.ajax(replaceFormatSuffix(url, 'json'), {
            type: isMutation ? 'POST' : 'GET',
            cache: false,
            dataType: 'json',
            data: data,
            success: [dispatchResponse],
            error: dispatchError
        });
    };

    $.widget('nethgui.Component', {
        _server: new Server(),
        _deep: true,                 
        _create: function () {
            var self = this;

            this.widgetEventPrefix = this.namespace;
            this._id = ++counter;
            this._children = [];

            if(this._deep === true) {
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
        getChildren: function () {
            return $(this._children);
        },
        _getChildNodes: function () {
            return this._children;
        },
        _findType: function (jqNode) {
            var typeFound = false;
            // Check whether any class of the node is defined in nethgui namespace:
            $.each(jqNode.prop('class').split(/\s+/), function(index, typeName) {
                if(typeName in $.nethgui && jqNode.data('nethgui') === undefined) {
                    typeFound = typeName;
                    return false;
                }
            });
            return typeFound;
        },
        /**
         * Find and initialize any descendant component
         */
        _initializeDeep: function (nodeQueue) {
            
            var node = nodeQueue.shift();
            var typeName = false;

            // iterate on descendant nodes: if a component is found,
            // initialize it and discard its branch.
            while(node !== undefined) {
                var jqNode = $(node);

                typeName = this._findType(jqNode)
                if(typeName !== false) {
                    // constructor call:
                    if(typeName in $.fn) {
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
        _setOption: function( key, value ) {
            $.Widget.prototype._setOption.apply( this, [key, value] ); 
            if(key === 'disabled' && this._deep === true) {
                this.getChildren().Component('option', key, value);
            }
        },
        destroy: function() {
            $.Widget.prototype.destroy.call( this );
            this.widget().unbind(this.namespace);
            this.element.unbind(this.namespace);
        },
        _updateView: function(value, selector) {
        // free to override
        },
        _sendQuery: function(url, data, freezeUi) {            
            this._server.ajaxMessage(false, url, typeof data === 'string' ? data : undefined, freezeUi ? this.widget() : undefined);
        },
        _sendMutation: function(url, data, freezeUi) {
            this._server.ajaxMessage(true, url, typeof data === 'string' ? data : undefined, freezeUi ? this.widget() : undefined);
        },
        _readHelp: function (url) {
            
        }
    });


    $(document).bind('nethguisendquery.nethgui', function(e, url, delay, freezeUi) {
        var server = new Server();
        if(server.isLocalUrl(url)) {
            window.location = url;
        }
    });

}( jQuery ) );
