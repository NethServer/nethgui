<div id="NotificationArea"><?php 
    
if ($view->getModule()->hasValidationErrors()): 

    ?><div class="validation-errors embedded ui-state-error"><span class="message"><?php
        echo count($view['validationErrors']) == 1 ? T('Incorrect value') : T('Incorrect values') 
    ?></span><ul class="validation-error-list"><?php
        foreach ($view['validationErrors'] as $error) : 

           ?><li><?php echo $error; ?></li><?php 

        endforeach ?></ul></div><?php

endif;

foreach($view['dialogs'] as $dialogView) { echo $dialogView; }
    
?></div>