<?php

$view->requireFlag($view::INSET_DIALOG | $view::INSET_FORM);

echo $view->header($view['__key'])->setAttribute('template', $T('Delete `${0}`'));
echo $view->textLabel($view['__key'])->setAttribute('template', $T('Confirm deletion of `${0}`?'));
// echo $view->hidden($view['__key']); // Put the key value into an hidden control
echo $view->buttonList($view::BUTTON_CANCEL)->insert($view->button('delete', $view::BUTTON_SUBMIT));

