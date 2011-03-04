<?php

/**
 * NethGui
 *
 * @package NethGuiFramework
 */

/**
 * A NethGui_Core_ContainerModule wraps its children into a DIV tag.
 *
 * @package NethGuiFramework
 * @subpackage StandardImplementation
 */
class NethGui_Core_ContainerModule extends NethGui_Core_StandardModuleComposite
{

    protected function decorate($output, NethGui_Core_ResponseInterface $response)
    {
        if ($response->getViewType() === Response::HTML) {
            return '<div class="' . $this->getIdentifier() . '">' . $output . '</div>';
        }

        return $output;
    }

}

?>
