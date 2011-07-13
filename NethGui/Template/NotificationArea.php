<div id="NotificationArea"><div class="notification-dialog LoadingMessage ui-state-highlight ui-corner-all" style="display: none"><span class="notification-icon ui-icon ui-icon-transfer-e-w" ></span><span class="message"><?php echo T('Please wait...'); ?></span></div><?php 
    
if ($view->getModule()->hasValidationErrors()): 

    ?><div class="notification-dialog embedded ui-state-error"><span class="message"><?php
        echo $view['validationLabel']
    ?>:</span> <span class="fields"><?php echo implode(', ', iterator_to_array($view['validationErrors'])); ?></span><?php

endif;

foreach($view['dialogs'] as $dialogView) { echo $dialogView; }
    
?></div>