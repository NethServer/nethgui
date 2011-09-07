<?php

echo $view->textLabel($view['__key'])->setAttribute('template', 'Confirm deletion of `${0}`?');

echo $view->hidden($view['__key']); // Put the key value into an hidden control

echo $view->elementList()->setAttribute('class', 'buttonList')
    ->insert($view->button('Submit', NethGui_Renderer_Abstract::BUTTON_SUBMIT))
    ->insert($view->button('Cancel', NethGui_Renderer_Abstract::BUTTON_CANCEL))
;

