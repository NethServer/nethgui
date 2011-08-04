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
class NethGui_Widget_Xhtml_Panel extends NethGui_Widget_Xhtml
{

    public function render()
    {
        $content = '';
        $cssClass = $this->getAttribute('class', 'panel');

        if ($this->hasAttribute('name')) {
            $id = $this->view->getUniqueId($this->getAttribute('name'));
        } else {
            $id = FALSE;
        }

        $attributes = array(
            'class' => $cssClass,
            'id' => $id
        );

        $content .= $this->openTag('div', $attributes);
        $content .= $this->renderChildren();
        $content .= $this->closeTag('div');

        return $content;
    }

}