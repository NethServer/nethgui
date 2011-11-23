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
class Nethgui\Widget\Help extends Nethgui\Widget\Abstract
{

    public function render()
    {
        $whatToDo = $this->getAttribute('do');

        if ($whatToDo == 'inset') {
            $renderer = $this->view->offsetGet($this->getAttribute('name'));
            if ($renderer instanceof Nethgui\Renderer\Help) {
                $renderer->nestingLevel = $this->view->nestingLevel + 1;
            }


            return (String) $renderer;
        }

        return parent::render();
    }

}
