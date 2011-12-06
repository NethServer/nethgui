<?php
$notifications = $view->getModule()->getNotifications();
$translator = $view->getTranslator();

?><div id="NotificationArea" class="NotificationArea NotificationArea_notifications"><?php
foreach ($notifications as $dialogBox):

    if ( ! $dialogBox instanceof \Nethgui\Client\DialogBox || $dialogBox->isDismissed()) {
        continue;
    }

    $message = $translator->translate($dialogBox->getModule(), \Nethgui\array_head($dialogBox->getMessage()), \Nethgui\array_end($dialogBox->getMessage()));

    switch (intval($dialogBox->getType()) & \Nethgui\Core\CommandFactoryInterface::MASK_SEVERITY) {
        case \Nethgui\Core\CommandFactoryInterface::NOTIFY_SUCCESS:
            $cssClass = 'Notification success ui-state-success';
            $icon = 'check';
            break;
        case \Nethgui\Core\CommandFactoryInterface::NOTIFY_WARNING:
            $cssClass = 'Notification warning ui-state-error';
            $icon = 'info';
            break;
        case \Nethgui\Core\CommandFactoryInterface::NOTIFY_ERROR:
            $cssClass = 'Notification error ui-state-error';
            $icon = 'alert';
            break;
    }

    ?><div class="<?php echo $cssClass ?>" id="<?php echo $dialogBox->getId() ?>"><span class='NotificationIcon ui-icon ui-icon-<?php echo $icon ?>' style='float: left; margin-right: .3em;'></span><span class="message"><?php echo $message; ?></span><?php
    if (count($dialogBox->getActions()) > 0) {
        $elementList = $view->elementList()->setAttribute('class', 'Buttonlist')
            ->setAttribute('wrap', 'div/');
        foreach ($dialogBox->getActions() as $actionView) {
            // TODO
        }
        echo $elementList;
    }
    ?></div><?php
    endforeach;

?></div>
