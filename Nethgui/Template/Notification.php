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
#Notification { margin-bottom: 0.5em }
#Notification li.notification { display: flex; color: #363636; border: 1px solid #fcefa1; background-color: #fbf9ee; padding: .5em; margin: 0; border-radius: 2px; font-size: 1.2em;}
#Notification li.notification .fa { flex: none }
#Notification li.notification .pre.fa:before { content: \"\\f05a\"; font-size: 1.2em; }
#Notification li.notification .post.fa:before { content: \"\\20\";  }
#Notification li.notification .content { margin: 0 .5em }

#Notification li.error,
#Notification li.validationError {color: #fff; background-color: #cd0a0a; border-color: #cd0a0a}
#Notification li.validationError a {color: #fff}
#Notification li.error .pre.fa:before,
#Notification li.validationError .pre.fa:before { content: \"\\f071\" }

#Notification li.message,
#Notification li.notice {color: #fff; background-color: #00a21a; border-color: #00a21a }
#Notification li.message .pre.fa:before,
#Notification li.notice .pre.fa:before { content: \"\\f058\" }

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
    $contents .=  sprintf('<li class="notification {{template}}"><span class="pre fa"></span><span class="content">{{content}}</span><span class="post fa"></span></li>',
        array(
            '{{template}}' => $n['template'],
            '{{content}}' => $mustache->render(isset($t[$n['template']]) ? $t[$n['template']] : $t['default'], $n)
            )
        );
}

echo sprintf('<div id="%s" class="Notifications %s"><ul>%s</ul></div>', $panelId, $viewTarget, $contents);

$view->includeJavascript("
jQuery(document).ready(function($) {
    $('#${panelId} li').Component();
});");
