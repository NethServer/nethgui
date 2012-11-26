<?php

/* @var $view Nethgui\Renderer\Xhtml */
$this->rejectFlag($view::INSET_FORM);

echo $view->form()->setAttribute('action', $view['path'])
    ->insert($view->header('hostname')->setAttribute('template', $T('Welcome on ${0}')))
    ->insert($view->textInput('username'))
    ->insert($view->textInput('password', $view::TEXTINPUT_PASSWORD))
    ->insert($view->selector('language', $view::SELECTOR_DROPDOWN))
    ->insert($view->buttonList()
        ->insert($view->button('Login', $view::BUTTON_SUBMIT)))
;

$images = array('Waves', 'Flow', 'Spring', 'Silk', 'Gulp');
$backgroundUrl = $view->getPathUrl() . "images/{$images[3]}.png";
$actionId = $view->getUniqueId();
$bg1 = '#1d247c';

$extCss = <<<"CSS"
/*
 * Login.php
 */
#allWrapper {
    background: {$bg1} url("{$backgroundUrl}") no-repeat center center !important;
}

#{$actionId} {
    -moz-box-shadow: 5px 5px 15px {$bg1};
    -webkit-box-shadow: 5px 5px 15px {$bg1};
    box-shadow: 5px 5px 15px {$bg1};
    background: white;
    padding: 1em;
    border: 1px solid {$bg1};
    border-radius: 4px;
    position: absolute;
}

#footer {
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

            $('#allWrapper').css({'height': y, 'width': x});
    };

    $(window).resize(adjust).scroll(adjust);
    
    $(document).ready(function() {
        window.setTimeout(adjust, 1);
    });
    
} ( jQuery ));
JS;

$view->includeCss($extCss);
$view->includeJavascript($extJs);



