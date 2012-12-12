/*
 * Loading dialog
 *
 * Copyright (C) 2011 Nethesis S.r.l.
 */
(function( $ ) {
    /*
     * Refs #355. Freeze UI while loading.
     *
     * Adds an overlaying modal dialog. The external CSS class
     * "overlay-loading-message" ensures the dialog is actually
     * not displayed, while keeping the original jQuery UI
     * overlaing div.
     */

    var dialog = $('<div id="NethguiOverlayLoadingMessage" style="display: none"/>');

    var activeRequests = 0;

    $('body').append(dialog);
    dialog.dialog({
        autoOpen: false,
        modal: true,
        closeOnEscape: false,
        dialogClass: "NethguiLoading"
    });

    $(document).bind("nethguifreezeui.nethgui", function() {
        // Todo: open dialog after a small timeout, to avoid flashes on cached responses.
        if(! dialog.dialog('isOpen')) {
            dialog.dialog('open');
        }
        activeRequests += 1;
    }).bind("ajaxStop.nethgui", function() {
        activeRequests -= 1;
        if(dialog.dialog('isOpen') && activeRequests < 1) {
            activeRequests = 0;
            dialog.dialog('close');
        }
    });
    
}( jQuery ));
