<?php
/**
 * @package Widget
 * @subpackage Xhtml
 * @author Davide Principi <davide.principi@nethesis.it>
 * @internal
 * @ignore
 */

/**
 * Wrap FORM tag around a Panel object
 * @package Widget
 * @subpackage Xhtml
 * @internal
 * @ignore
 */
class Nethgui_Widget_Xhtml_Form extends Nethgui_Widget_Xhtml_Panel
{

    public function render()
    {                       
        $action = $this->getAttribute('action');
        $this->setAttribute('class', $this->getAttribute('class', FALSE));
        $this->setAttribute('name', FALSE);

        $content = '';
        $content .= $this->openTag('form', array('method' => 'post', 'action' => $this->buildUrl($action)));
        $content .= parent::render();
        $content .= $this->closeTag('form');

        return $content;
    }

}