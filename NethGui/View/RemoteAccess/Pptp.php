<?php

$view->fieldsetSwitch('status', 'disabled');


$view->fieldsetSwitch('status', 'enabled')
    ->textInput('client');

echo $view;

?>