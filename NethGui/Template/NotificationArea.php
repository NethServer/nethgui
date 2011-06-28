<div id="<?php echo $view->getFullId() ?>"><?php 
    
if ($view->getModule()->hasValidationErrors()): 

    ?><ul class="validation-errors modal ui-state-error"><?php 
        foreach ($view['validationErrors'] as $error) : 

           ?><li><a class="control-label" href="#<?php echo $error[0] ?>"><?php echo $error[1] ?></a><span class="message"><?php echo $error[2] ?></span></li><?php 

        endforeach ?></ul><?php 

endif;

foreach($view['dialogs'] as $dialogView) :

    echo $dialogView;
    
endforeach;
    
?></div>