<?php
$message = T('Confirm deletion of "%s"?',
        array('%s' => empty($view[$view['__key']]) ? '%s' : $view[$view['__key']]), NULL, NULL, FALSE);

$flags = NethGui_Renderer_Abstract::DIALOG_MODAL;

if ($view['__action'] == 'index') {
    $flags |= NethGui_Renderer_Abstract::STATE_DISABLED;
}

// Render a dialog
echo $view->dialog('ConfirmDeletion', $flags) // Create a MODAL dialog (see $flags)
        ->append($message) // Add the dialog text (see $message)
        ->hidden($view['__key']) // Put the key value into an hidden control
        ->button('Submit', NethGui_Renderer_Xhtml::BUTTON_SUBMIT) // Add SUBMIT button
        ->button('Cancel', NethGui_Renderer_Xhtml::BUTTON_CANCEL) // Add CANCEL button
;

?>
