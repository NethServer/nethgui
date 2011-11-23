<?php
switch (intval($view['type']) & Nethgui\Client\DialogBox::MASK_SEVERITY) {
    case Nethgui\Client\DialogBox::NOTIFY_SUCCESS:
        $cssClass = 'Notification success ui-state-success';
        $icon = 'check';
        break;
    case Nethgui\Client\DialogBox::NOTIFY_WARNING:
        $cssClass = 'Notification warning ui-state-error';
        $icon = 'info';
        break;
    case Nethgui\Client\DialogBox::NOTIFY_ERROR:
        $cssClass = 'Notification error ui-state-error';
        $icon = 'alert';
        break;
}

?><div class="<?php echo $cssClass ?>" id="<?php echo $view['dialogId']

?>"><span class='NotificationIcon ui-icon ui-icon-<?php echo $icon ?>' style='float: left; margin-right: .3em;'></span><span class="message"><?php echo $view['message']; ?></span><?php
if (count($view['actions']) > 0) {
    $elementList = $view->elementList()->setAttribute('class', 'Buttonlist')
        ->setAttribute('wrap', 'div/');
    foreach ($view['actions'] as $actionView) {
        $elementList->insert($view->literal($actionView));
    }
    echo $elementList;
}

?></div>
