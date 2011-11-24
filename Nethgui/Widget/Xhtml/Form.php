<?php
/**
 * @author Davide Principi <davide.principi@nethesis.it>
 * @internal
 * @ignore
 */

namespace Nethgui\Widget\Xhtml;

/**
 * Wrap FORM tag around a Panel object
 * @internal
 * @ignore
 */
class Form extends Panel
{

    public function render()
    {                       
        $action = $this->getAttribute('action', '');
        $this->setAttribute('class', $this->getAttribute('class', FALSE));
        $this->setAttribute('name', FALSE);

        $content = '';
        $content .= $this->openTag('form', array('method' => 'post', 'action' => $this->view->getModuleUrl($action)));
        $content .= parent::render();
        $content .= $this->closeTag('form');

        return $content;
    }

}
