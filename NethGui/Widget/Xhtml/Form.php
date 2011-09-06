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
        $action = $this->getAttribute('action');
        $this->setAttribute('class', FALSE);
        $this->setAttribute('name', FALSE);

        $content = '';
        $content .= $this->openTag('form', array('method' => 'post', 'action' => $this->buildUrl($action)));
        $content .= parent::render();
        $content .= $this->closeTag('form');

        return $content;
    }

}