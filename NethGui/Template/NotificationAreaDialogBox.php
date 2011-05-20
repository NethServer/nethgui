<?php

$dialogClass = 'dialog';

switch ($view['type']) {
    case NethGui_Core_DialogBox::NOTIFY_SUCCESS:
        $dialogClass .= ' embedded success';
        break;
    case NethGui_Core_DialogBox::NOTIFY_WARNING:
        $dialogClass .= ' embedded warning';
        break;
    case NethGui_Core_DialogBox::NOTIFY_ERROR:
        $dialogClass .= ' modal error';
        break;
}

?><div class="<?php 
    echo $dialogClass 
?>" id="Dialog_<?php 
    echo $view['dialogId']
?>"><span class="message"><?php
    echo T($view['message']);
?></span><?php

if(count($view['actions']) > 0):
    ?><ul class="actions"><?php
    foreach ($view['actions'] as $action) :
        ?><li><?php echo $action ?></li><?php
    endforeach;
    ?></ul><?php
endif;

?></div>