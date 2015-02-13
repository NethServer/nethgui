<?php
/* @var $view \Nethgui\Renderer\Xhtml */
$view
    ->includeFile('Nethgui/Js/jquery.nethgui.notification.js')
    ->useFile('js/mustache.js');
            
$panelId = $view->getUniqueId();
$viewTarget = $view->getClientEventTarget('notifications');

$t = $view->getModule()->getTemplates();
$t['__default__'] = array('<i class="fa fa-li fa-info-circle"></i>{{.}}', 'bg-green pre-fa');
$t['warning'] = array('<i class="fa fa-li fa-exclamation-triangle"></i>{{.}}', 'bg-yellow pre-fa');
$t['error'] = array('<i class="fa fa-li fa-exclamation-circle"></i>{{.}}', 'bg-red pre-fa');
$t['validationError'] = array('<i class="fa fa-li fa-exclamation-triangle"></i>
<dl class="fields">
 {{#.}}<dt><a href="#{{parameter}}">{{label}}</a></dt>
 <dd>{{reason}}</dd>{{/.}}
</dl>
', 'validationError bg-red pre-fa');

$view->includeCss("
#Notification { margin-bottom: 0.5em; font-size: 1.2em }
#Notification .fa-ul { margin: 0 }
#Notification > ul > li { padding: 1em; margin-bottom: 0.5em; }
#Notification ul.fa-ul > li { padding-left: 2.5em }
#Notification ul.fa-ul > li.nolpad { padding-left: .5em }
#Notification .fa-li { font-size: 1.2em; left: 0; top: .8em }

.notification.bg-red {color: #fff; background-color: #cd0a0a; border-color: #cd0a0a}
.notification.bg-red a {color: #fff}

.notification.bg-green {color: #fff; background-color: #00a21a; border-color: #00a21a }
.notification.bg-green a {color: #fff}

.notification.bg-yellow {color: #000; background-color: #F4D622; border-color: #F4D622 }
.notification.bg-yellow a {color: #000}

.notification.validationError dd {margin-bottom: .25em}
");

$escc = json_encode($colorMap);
$jsCode = "\n    $.nethgui.Notification.colors = $escc;";
foreach($t as $templateName => $templateValue) {
    $escn = json_encode($templateName);
    $escv = json_encode($templateValue);
    $jsCode .= "\n    $.nethgui.Notification.templates[$escn] = $escv;";
}

$view->includeJavascript(sprintf("\n(function( $ ) {%s\n}( jQuery ));\n", $jsCode));

if(empty($view['notifications'])) {
    echo sprintf('<div id="%s" class="Notifications %s" ></div>', $panelId, $viewTarget);
    return;
}

$mustache = new \Mustache_Engine();
$contents = '';
foreach($view['notifications'] as $n) {
    $contents .= strtr('<li class="notification {{cssClass}}">{{content}}</li>',
        array(
            '{{cssClass}}' => isset($t[$n['t']]) ? $t[$n['t']][1] : $t['__default__'][1],
            '{{content}}' => $mustache->render(isset($t[$n['t']]) ? $t[$n['t']][0] : $t['__default__'][0], $n['a'])
            )
        );
}

echo sprintf('<div id="%s" class="Notifications %s"><ul class="fa-ul">%s</ul></div>', $panelId, $viewTarget, $contents);

$view->includeJavascript("
jQuery(document).ready(function($) {
    $('#${panelId} li').Component();
});");
