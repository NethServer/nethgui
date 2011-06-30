<div id="NotificationArea"><?php 
    
if ($view->getModule()->hasValidationErrors()): 

    ?><ul class="validation-errors embedded ui-state-error"><?php 
        foreach ($view['validationErrors'] as $error) : 

           ?><li><?php echo $error; ?></li><?php 

        endforeach ?></ul><?php 

endif;

foreach($view['dialogs'] as $dialogView) { echo $dialogView; }
    
?></div>