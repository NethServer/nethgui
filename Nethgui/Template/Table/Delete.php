<?php
echo $view->header($view['__key'])->setAttribute('template', 'Delete `${0}`');
echo $view->textLabel($view['__key'])->setAttribute('template', 'Confirm deletion of `${0}`?');
echo $view->hidden($view['__key']); // Put the key value into an hidden control
echo $view->elementList($view::BUTTON_CANCEL)->insert($view->button('delete', $view::BUTTON_SUBMIT));

