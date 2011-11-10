<?php
/**
 * @package Widget
 * @subpackage Xhtml
 * @author Davide Principi <davide.principi@nethesis.it>
 * @internal
 * @ignore
 */

/**
 *
 * @package Widget
 * @subpackage Xhtml
 * @internal
 * @ignore
 */
class Nethgui_Widget_Xhtml_Panel extends Nethgui_Widget_Xhtml
{

    public function render()
    {
        $content = '';
        $cssClass = $this->getAttribute('class', FALSE);
        $tag = $this->getAttribute('tag', 'div');

        $flags = $this->getAttribute('flags');
        if ($cssClass && ($flags & Nethgui_Renderer_WidgetFactoryInterface::STATE_DISABLED)) {
            $cssClass .= ' disabled';
        }

        if ($this->hasAttribute('name') && $this->getAttribute('name') !== FALSE) {
            $id = $this->view->getUniqueId($this->getAttribute('name'));
        } else {
            $id = FALSE;
        }

        $attributes = array(
            'class' => empty($cssClass) ? FALSE : trim($cssClass),
            'id' => $id
        );

        $content .= $this->openTag($tag, $attributes);
        $content .= $this->renderChildren();
        $content .= $this->closeTag($tag);

        return $content;
    }

}