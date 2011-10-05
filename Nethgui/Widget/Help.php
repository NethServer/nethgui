<?php
/**
 * @package Widget
 * @author Davide Principi <davide.principi@nethesis.it>
 * @ignore
 */

/**
 * Abstract Help Widget class
 * @package Widget
 * @ignore
 */
class Nethgui_Widget_Help extends Nethgui_Widget_Abstract
{

    public function render()
    {
        $whatToDo = $this->getAttribute('do');

        if ($whatToDo == 'inset') {
            $renderer = $this->view->offsetGet($this->getAttribute('name'));
            if ($renderer instanceof Nethgui_Renderer_Help) {
                $renderer->nestingLevel = $this->view->nestingLevel + 1;
            }


            return (String) $renderer;
        }

        return parent::render();
    }

}
