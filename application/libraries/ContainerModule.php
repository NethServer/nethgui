<?php

/**
 * A ContainerModule wraps its children into a DIV tag.
 */
class ContainerModule extends StandardModuleComposite {

    protected function decorate($output, Response $response)
    {
        if ($response->getViewType() === Response::HTML)
        {
            return '<div class="' . $this->getIdentifier() . '">' . $output . '</div>';
        }
    }

}

?>
