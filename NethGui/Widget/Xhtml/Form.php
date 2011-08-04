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
class NethGui_Widget_Xhtml_Form extends NethGui_Widget_Xhtml_Panel
{

    public function render()
    {
        // Set empty string as default name to obtain an xhtml ID attribute
        $this->setAttribute('name', $this->getAttribute('name', ''));
        
        $action = $this->getAttribute('action');

        $content = '';
        $content .= $this->openTag('form', array('method' => 'post', 'action' => $this->buildUrl($action)));
        $content .= parent::render();
        $content .= $this->closeTag('form');

        return $content;
    }

}