<div class="dialog-text"><?php
$message = T('Confirm deletion of "%s"?',
        array('%s' => empty($view[$view['__key']]) ? '%s' : $view[$view['__key']]), NULL, NULL, FALSE);

// Render the dialog content

echo htmlspecialchars($message); // Add the dialog text (see $message)
?></div><?php

echo $view->hidden($view['__key']); // Put the key value into an hidden control

echo $view->elementList()->setAttribute('class', 'buttonList')
    ->insert($view->button('Submit', NethGui_Renderer_Abstract::BUTTON_SUBMIT))
    ->insert($view->button('Cancel', NethGui_Renderer_Abstract::BUTTON_CANCEL))
;

