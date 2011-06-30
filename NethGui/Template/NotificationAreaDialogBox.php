<?php

$dialogClass = 'notification-dialog';

switch ($view['type']) {
    case NethGui_Core_DialogBox::NOTIFY_SUCCESS:
        $dialogClass .= ' embedded success ui-state-highlight';
        $icon = 'check';
        break;
    case NethGui_Core_DialogBox::NOTIFY_WARNING:
        $dialogClass .= ' embedded warning ui-state-error';
        $icon = 'info';
        break;
    case NethGui_Core_DialogBox::NOTIFY_ERROR:
        $dialogClass .= ' modal error ui-state-error';
        $icon = 'alert';
        break;
}

?><div class="<?php 
    echo $dialogClass 
?>" id="<?php 
    echo $view['dialogId']
?>"><span class='ui-icon ui-icon-<?php echo $icon ?>' style='float: left; margin-right: .3em;'></span><span class="message"><?php
    echo T($view['message']); ?></span><?php 

if(count($view['actions']) > 0):
    ?><ul class="actions"><?php
    foreach ($view['actions'] as $action) :
        ?><li><?php echo $action ?></li><?php
    endforeach;
    ?></ul><?php
endif;

?></div>