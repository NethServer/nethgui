<?php

echo $view->dialog('CreateUpdate')
        ->textInput('network')
        ->textInput('Mask')
        ->textInput('Router')
        ->button('Submit', NethGui_Renderer_Xhtml::BUTTON_SUBMIT)
        ->button('Cancel', NethGui_Renderer_Xhtml::BUTTON_LINK, '..'); /* implicit call to flush() */
