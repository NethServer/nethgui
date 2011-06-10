<?php
$message = T('Confirm deletion of "%s"?',
        array('%s' => empty($view[$view['__key']]) ? '%s' : $view[$view['__key']]), NULL, NULL, FALSE);

// Render the dialog content
echo $view
        ->append($message) // Add the dialog text (see $message)
        ->hidden($view['__key']) // Put the key value into an hidden control
    ;
?><ul class="actions"><li><?php 
    echo $view->button('Submit', NethGui_Renderer_Xhtml::BUTTON_SUBMIT) // Add SUBMIT button 
        ?></li><li><?php
    echo $view->button('Cancel', NethGui_Renderer_Xhtml::BUTTON_CANCEL) // Add CANCEL button
        ?></li></ul>