/*
 * DataTable
 *
 * Copyright (C) 2011 Nethesis S.r.l.
 */
(function( $ ) {
    var SUPER = $.nethgui.Component;
    $.widget('nethgui.DataTable', SUPER, {
        language: {
            'en':{
                "sProcessing":   "Processing...",
                "sLengthMenu":   "Show _MENU_ entries",
                "sZeroRecords":  "No matching records found",
                "sInfo":         "Showing _START_ to _END_ of _TOTAL_ entries",
                "sInfoEmpty":    "Showing 0 to 0 of 0 entries",
                "sInfoFiltered": "(filtered from _MAX_ total entries)",
                "sInfoPostFix":  "",
                "sSearch":       "Search:",
                "sUrl":          "",
                "oPaginate": {
                    "sFirst":    "First",
                    "sPrevious": "Previous",
                    "sNext":     "Next",
                    "sLast":     "Last"
                }
            },
            'it':{
                "sProcessing":   "Caricamento...",
                "sLengthMenu":   "Visualizza _MENU_ elementi",
                "sZeroRecords":  "La ricerca non ha portato alcun risultato.",
                "sInfo":         "Vista da _START_ a _END_ di _TOTAL_ elementi",
                "sInfoEmpty":    "Vista da 0 a 0 di 0 elementi",
                "sInfoFiltered": "(filtrati da _MAX_ elementi totali)",
                "sInfoPostFix":  "",
                "sSearch":       "Cerca:",
                "sUrl":          "",
                "oPaginate": {
                    "sFirst":    "Inizio",
                    "sPrevious": "Precedente",
                    "sNext":     "Successivo",
                    "sLast":     "Fine"
                }
            }
        },
        

        // define a builtin "buttonList" formatter for actions..
        _formatterFunctions: {
            'default': function(o) {
                if(typeof o == "string") {
                    return Mustache.escape(o);
                } else if(o === undefined || o === null) {
                    return '';
                } else {
                    return Mustache.escape(String(o));
                }
            },
            'fmtButtonlist': function(o) {
                if(typeof o == "string") {
                    return Mustache.escape(o);
                }
                var buttons = [];
                var buttonTemplate = '<span><a href="${0}" class="Button link">${1}</a></span>';
                for(var i in o) {
                    buttons.push(buttonTemplate.replacePlaceholders(Mustache.escape(o[i][1]), Mustache.escape(o[i][0])));
                }
                if(buttons.length == 0) {
                    return '';
                }
                return '<div class="Buttonlist">' + buttons.join('') + '</div>';
            },
            'fmtButtonset': function(o) {
                if(typeof o == "string") {
                    return Mustache.escape(o);
                }
                var buttons = [];
                var buttonTemplate = '<span><a href="${0}" class="Button link">${1}</a></span> ';
                for(var i in o) {
                    buttons.push(buttonTemplate.replacePlaceholders(Mustache.escape(o[i][1]), Mustache.escape(o[i][0])));
                }
                if(buttons.length == 0) {
                    return '';
                }
                return '<div class="Buttonset v1">' + buttons.join('') + '</div>';
            },
            'fmtRawHtml': function(o) {
                return o;
            }
        },
        _initializeColumnFormatters: function(columnDefs) {
            var self = this;

            this._columnFormatters = [];

            // Extract the formatter name from the class attribute of each TH element:
            this._dataTable.children('thead').find('th').each(function(index, th) {
                var options = $.parseJSON($(th).attr('data-options'));
                options = $.extend({
                    name: 'column#' + index,
                    formatter: 'default',
                    columnDefs: false,
                },options);
                if($.isPlainObject(options.columnDefs)) {
                    options.columnDefs.targets = index;
                    columnDefs.push(options.columnDefs)
                }
                self._columnFormatters.push(options.formatter);
            });
        },
        _create: function () {
            SUPER.prototype._create.apply(this);
            
            var self = this;            
            
            this._rowMeta = [];            
            this._dataTable = this.element.children('table').first();

            var language = this.language[$('html').attr('lang')];
            
            if(language === undefined) {
                language = this.language['en'];
            }

            var defaultSettings = {
                bJQueryUI: true,
                columnDefs: [],
                fnRowCallback: function( nRow, aData ) {
                    var $nRow = $(nRow);
                    var key = aData[0];
                    //$nRow.children('td:first').remove();
                    self._initializeDeep($nRow.children().toArray());
                    if(self._rowMeta[key] !== undefined) {
                        //apply tr class
                        $nRow.addClass(self._rowMeta[key].rowCssClass);
                    }
                    return nRow
                },
                oLanguage: language,
                iDisplayLength: 25
            };

            if(this.element.hasClass('small-dataTable')) {
                defaultSettings = $.extend(defaultSettings, {
                    bPaginate: false,
                    bFilter: false,
                    bInfo: false
                });
            } else {
                defaultSettings = $.extend(defaultSettings, {
                    sPaginationType: "full_numbers"
                });
            }

            this._initializeColumnFormatters(defaultSettings.columnDefs);

            // Attach DataTable plugin to the TABLE element:
            this._dataTable.dataTable(defaultSettings);
        },
        _updateView: function(rows, selector) {            
            this._rowMeta = [];
            this._dataTable.fnClearTable(false);
            for(var i = 0; i < rows.length; i++) {
                var currentRow = [];
                this._rowMeta[rows[i][1]] = rows[i][0];
                for(var j = 1; j < rows[i].length; j++) {
                    // invoke the formatter function - see addFormatters():
                    var formatter = this._formatterFunctions[this._columnFormatters[j-1]];                    
                    currentRow.push(formatter.call(this, rows[i][j]));
                }
                this._dataTable.fnAddData(currentRow, false);
            }
            this._dataTable.fnDraw();
        },

        /*
         * For complex cell values you can define appropriate formatter functions
         * that transform an object into an HTML string. A formatter
         * is associated to a column by its name and the css CLASS attribute
         * of the column header (TH element).
         */
        addFormatters: function(formatterFunctions) {
            $.extend(this._formatterFunctions, formatterFunctions);
        }
    });
}( jQuery ));
