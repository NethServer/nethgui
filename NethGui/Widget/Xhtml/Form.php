<?php
/**
 * @package Widget
 * @subpackage Xhtml
 * @author Davide Principi <davide.principi@nethesis.it>
 * @internal
 */

/**
 *
 * @package Widget
 * @subpackage Xhtml
 * @internal
 */
class NethGui_Widget_Xhtml_Form extends NethGui_Widget_Xhtml
{

    public function render()
    {
        $name = $this->getAttribute('name');
        $value = $this->getAttribute('value');
        $flags = $this->getAttribute('flags');
        $action = $this->getAttribute('action');
        $content = '';

        $content .= $this->openTag('form', array('method' => 'post', 'action' => $this->buildUrl($action)));
        $content .= $this->openTag('div', array('id' => $this->view->getUniqueId($name ? $name : 'Form')));
        $content .= $this->renderChildren();
        $content .= $this->closeTag('div');
        $content .= $this->closeTag('form');

        return $content;
    }

}