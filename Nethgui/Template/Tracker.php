<?php

/* @var $view \Nethgui\Renderer\Xhtml  */
$target = $view->getClientEventTarget('dialog');

echo  $view->panel()->setAttribute('receiver', '')->setAttribute('class', $target)
    ->insert($view->progressBar('progress'))
    ->insert($view->textLabel('message')->setAttribute('class', 'wspreline')->setAttribute('tag', 'div'));

$messageTarget = $view->getClientEventTarget('message');

// Define a notification template that opens the first running task details:
$view->getModule()->defineNotificationTemplate('trackerRunning', strtr('<span>{{message}}</span> <a class="Button link" href="{{btnLink}}">{{btnLabel}}</a>', array(
    '{{message}}' => $view->translate('Tracker_running_tasks_message'),
    '{{btnLink}}' => $view->getModuleUrl('/Tracker/{{data.taskId}}'),
    '{{btnLabel}}' => $view->translate('Tracker_button_label')
)));

$view->getModule()->defineNotificationTemplate('trackerError', strtr('<span>{{genericLabel}}</span> <dl>{{#data.failedTasks}}<dt>{{title}} #{{id}} (code {{code}})</dt><dd class="wspreline">{{message}}</dd>{{/data.failedTasks}}</dl>', array(
    '{{genericLabel}}' => $view->translate('Tracker_task_error_message')
)));

$view->includeCss("
#Tracker {display:none}
#Notification li.trackerError {color: #cd0a0a; background-color: #fef1ec; border-color: #cd0a0a}
.trackerError dt:before { content: \"\\2192\\20\" }
.trackerError dt { margin-top: .2em}
.trackerError dd { margin: 0 0 0 1em }
.${messageTarget} { min-height: 2.5em }
");

$closeLabel = json_encode($view->translate("Close_label"));

$view->includeJavascript("
jQuery(document).ready(function($) {

    var tid;  // the timeout id
    var xhr;  // the last ajax request

    $('#Tracker').dialog({
        autoOpen: false,
        closeOnEscape: false,
        modal: true,
        dialogClass: 'trackerDialog',
        buttons: {
            $closeLabel: function () {
                $(this).dialog('close');
                if(tid) {
                    window.clearTimeout(tid);
                }
                tid = false;
                if(xhr) {
                    try {
                        xhr.abort();
                    } catch (e) {
                        //pass
                    }
                }
                xhr = false;
            }
        }
    }).on('nethguiupdateview', function (e, value, selector) {
        if( ! $.isPlainObject(value)) {
            $(this).dialog('close');
            return;
        }
        
        $(this).dialog(value.action).dialog('option', 'title', value.title);

        if(value.nextPath) {
            tid = window.setTimeout(function() {
                xhr = $.Nethgui.Server.ajaxMessage({
                    url: value.nextPath
                });
            }, value.sleep);
        }
    }).Component();
});");
