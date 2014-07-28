<?php
/* @var $view \Nethgui\Renderer\Xhtml */
$view
    ->includeFile('Nethgui/Js/jquery.nethgui.notification.js')
    ->useFile('js/mustache.js');
            
$panelId = $view->getUniqueId();
$viewTarget = $view->getClientEventTarget('notifications');

$t = $view->getModule()->getTemplates();
$t['default'] = '{{#data}}{{.}}{{/data}}';
$t['validationError'] = '{{#data}}<dl class="fields">{{#fields}}
 <dt><a href="#{{parameter}}">{{label}}</a></dt>
 <dd>{{reason}}</dd>
{{/fields}}
    </dl>{{/data}}
';

$view->includeCss("
#Notification li.error,
#Notification li.validationError {color: #cd0a0a; background-color: #fef1ec; border-color: #cd0a0a}
#Notification li.validationError a {color: #cd0a0a}
#Notification dd {margin-bottom: .25em}
");

$jsCode = '';
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
    $contents .=  sprintf('<li class="notification %s"><span class="pre fa %s"></span><span class="content">%s</span></li>', $n['template'], $n['icon'] ? $n['icon'] : 'fa-info-circle', $mustache->render(isset($t[$n['template']]) ? $t[$n['template']] : $t['default'], $n));
}

echo sprintf('<div id="%s" class="Notifications %s"><ul>%s</ul></div>', $panelId, $viewTarget, $contents);

$view->includeJavascript("
jQuery(document).ready(function($) {
    $('#${panelId} li').Component();
});");
