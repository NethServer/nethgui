<?php

/* @var $view \Nethgui\Renderer\Xhtml  */
$trackerStateTarget = $view->getClientEventTarget('trackerState');

echo  $view->panel()->setAttribute('receiver', '')->setAttribute('class', $trackerStateTarget)
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

    var updateDialog = function(value) {
        $('body > .ui-widget-overlay').css('cursor', value.action == 'open' ? 'progress' : 'auto');

        if(value.action) {
            $(this).dialog(value.action);
        }

        if(value.title) {
            $(this).dialog('option', 'title', value.title);
        }
    };

    var updateLocation = function(value) {
        if( ! value.url) {
            return;
        }
        var sendQuery = function() {
            xhr = $.Nethgui.Server.ajaxMessage({
                url: value.url,
                freezeElement: value.freeze ? $('#Tracker') : false
            });
        };
        if(value.sleep > 0) {
            tid = window.setTimeout(sendQuery, value.sleep);
        } else {
            tid = false; sendQuery();
        }
    };

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
        if($.isPlainObject(value.dialog)) {
            updateDialog.call(this, value.dialog);
        }
        if($.isPlainObject(value.location)) {
            updateLocation.call(this, value.location);
        }
    }).Component();
});");
