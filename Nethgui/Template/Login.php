<?php

/* @var $view Nethgui\Renderer\Xhtml */
$this->rejectFlag($view::INSET_FORM);

echo $view->form()
    ->insert($view->header('hostname')->setAttribute('template', $T('Welcome on ${0}')))
    ->insert($view->textInput('username'))
    ->insert($view->textInput('password', $view::TEXTINPUT_PASSWORD))
    ->insert($view->selector('language', $view::SELECTOR_DROPDOWN))
    ->insert($view->hidden('path'))
    ->insert($view->buttonList()
        ->insert($view->button('Login', $view::BUTTON_SUBMIT)))
;

$actionId = $view->getUniqueId();
$bg1 = '#3D4547';

$extCss = <<<"CSS"
/*
 * Login.php
 */
html,body {
    height:100%;
    background: {$bg1}
}

#allWrapper {
    margin: 0;
    height:100%;
    display: flex !important;
    flex-flow: column;
    justify-content: space-between;
    align-items: center;
}

#Notification {
    width: 99%;
}

#footer {
    align-self: flex-end;
    border: none;
    color: #eee;
    background: transparent;
    padding: 0.5em;
}

.primaryContent { margin: 0 }

#{$actionId} {
    background: white;
    padding: 1em;
    border: 1px solid {$bg1};
    border-radius: 2px;
}

#{$actionId} .Button.submit {
    font-size: 130%;
}
#{$actionId} .Buttonlist {
    margin-top: 20px;
    text-align: center;    
}
#{$actionId} .ui-widget-header {
    margin-left: -10px;
    margin-right: -10px;
    margin-top: -10px;
    padding: 10px;
    color: #fff !important;
    font-size: 120%;
    text-align: center;
    background: {$bg1};
}


CSS;

$view->includeCss($extCss);



