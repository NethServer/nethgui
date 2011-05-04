<?php
$message = T('Confirm deletion of "%s"?',
        array('%s' => isset($view[$view['__key']]) ? $view[$view['__key']] : '%s')
);

$flags = NethGui_Renderer_Abstract::DIALOG_MODAL;

if ($view['__action'] == 'index') {
    $flags |= NethGui_Renderer_Abstract::STATE_DISABLED;
}

$dialog = $view->dialog('ConfirmDeletion', $message, $flags);

$dialog->hidden($view['__key'], $view[$view['__key']]);

$dialog
    ->button('Submit', NethGui_Renderer_Xhtml::BUTTON_SUBMIT)
    ->button('Cancel', NethGui_Renderer_Xhtml::BUTTON_CANCEL)
;

echo $dialog;

?>
