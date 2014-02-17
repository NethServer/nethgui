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

#{$actionId} {
    background: white;
    padding: 1em;
    border: 1px solid {$bg1};
    border-radius: 2px;
    position: absolute;
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
    color: #fff;
    font-size: 120%;
    text-align: center;
    background: {$bg1};
}

#footer {
    border: none;
    position: fixed;
    bottom: 5px;
    right: 5px;
    color: #eee;
    background: transparent;
}
CSS;

$extJs = <<<"JS"
/**
 * Login.php
 */
(function ( $ ) {
    var adjust = function (e) {

            $('#{$actionId}').position({
                my: 'center center',
                at: 'center center',
                of: $(window)
            });

            var w = window,
            d = document,
            e = d.documentElement,
            g = d.getElementsByTagName('body')[0],
            x = w.innerWidth || e.clientWidth || g.clientWidth,
            y = w.innerHeight || e.clientHeight || g.clientHeight;

            var bg = $('#Login .ui-widget-header').css('background');
            $('#allWrapper').css({'height': y, 'width': x, 'background': bg});

    };

    $(window).resize(adjust).scroll(adjust);
    
    $(document).ready(function() {
        window.setTimeout(adjust, 1);
    });
    
} ( jQuery ));
JS;

$view->includeCss($extCss);
$view->includeJavascript($extJs);



