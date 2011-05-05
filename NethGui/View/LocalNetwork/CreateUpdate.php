<?php

echo $view->dialog('CreateUpdate', NethGui_Renderer_Abstract::DIALOG_EMBEDDED | ($view['__action'] == 'index' ? NethGui_Renderer_Abstract::STATE_DISABLED : 0 ))
        ->textInput('network')
        ->textInput('Mask')
        ->textInput('Router')
        ->button('Submit', NethGui_Renderer_Abstract::BUTTON_SUBMIT)
        ->button('Cancel', NethGui_Renderer_Abstract::BUTTON_LINK, '..');
