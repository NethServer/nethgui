<div id="<?php echo $view->getFullId() ?>"><?php 
    
if ($view->getModule()->hasValidationErrors()): 

    ?><ul class="validation-errors"><?php 
        foreach ($view['validationErrors'] as $error) : 

           ?><li><a class="control-label" href="#<?php echo $error[0] ?>"><?php echo $error[1] ?></a><span class="message"><?php echo $error[2] ?></span></li><?php 

        endforeach ?></ul><?php 

endif;

foreach($view['dialogs'] as $dialogData) :

    $dialogClass = 'dialog';

    switch ($dialogData['type']) {
        case NethGui_Core_NotificationCarrierInterface::NOTIFY_SUCCESS:
            $dialogClass .= ' embedded success';
            break;
        case NethGui_Core_NotificationCarrierInterface::NOTIFY_WARNING:
            $dialogClass .= ' embedded warning';
            break;
        case NethGui_Core_NotificationCarrierInterface::NOTIFY_ERROR:
            $dialogClass .= ' modal error';
            break;
    }

    ?><div class="<?php 
        echo $dialogClass 
    ?>" id="Dialog_<?php 
        echo $dialogData['dialogId']
    ?>"><span class="message"><?php
        echo $dialogData['message']
    ?></span><?php

    if(count($dialogData['actions']) > 0):
        ?><ul class="actions"><?php
        foreach ($dialogData['actions'] as $action) :
            ?><li><?php echo $action ?></li><?php
        endforeach;
        ?></ul><?php
    endif;
    
    ?></div><?php

endforeach;
    
?></div>