<?php
/**
 * @author Davide Principi <davide.principi@nethesis.it>
 * @ignore
 */

namespace Nethgui\Widget;

/**
 * Abstract Help Widget class
 * @ignore
 */
class Help extends AbstractWidget
{

    public function render()
    {
        $whatToDo = $this->getAttribute('do');

        if ($whatToDo == 'inset') {
            $renderer = $this->view->offsetGet($this->getAttribute('name'));
            if ($renderer instanceof \Nethgui\Renderer\Help) {
                $renderer->nestingLevel = $this->view->nestingLevel + 1;
            }


            return (String) $renderer;
        }

        return parent::render();
    }

}
