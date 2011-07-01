<div id="NotificationArea"><?php 
    
if ($view->getModule()->hasValidationErrors()): 

    ?><div class="notification-dialog embedded ui-state-error"><span class="message"><?php
        echo count($view['validationErrors']) == 1 ? T('Incorrect value') : T('Incorrect values') 
    ?>:</span> <span class="fields"><?php echo implode(', ', iterator_to_array($view['validationErrors'])); ?></span><?php

endif;

foreach($view['dialogs'] as $dialogView) { echo $dialogView; }
    
?></div>