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
class Nethgui_Widget_Xhtml_Panel extends Nethgui_Widget_Xhtml
{

    public function render()
    {
        $content = '';
        $cssClass = $this->getAttribute('class', 'panel');

        $flags = $this->getAttribute('flags');
        if ($flags & Nethgui_Renderer_Abstract::STATE_DISABLED) {
            $cssClass .= ' disabled';
        }

        if ($this->hasAttribute('name') && $this->getAttribute('name') !== FALSE) {
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