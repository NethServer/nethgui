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
class NethGui_Widget_Xhtml_ElementList extends NethGui_Widget_Xhtml
{

    public function render()
    {
        $name = $this->getAttribute('name');
        $value = $this->getAttribute('value');
        $flags = $this->getAttribute('flags');
        $classes = 'actions buttonList';

        if ($flags & NethGui_Renderer_Abstract::STATE_DISABLED) {
            $classes .= ' disabled';
        }
        
        $content = '';

        $content .= $this->openTag('ul', array('class' => $classes));
        $content .= $this->renderChildren();
        $content .= $this->closeTag('ul');

        return $content;
    }

    protected function wrapChild($childOutput)
    {
        $content = '';
        $content .= $this->openTag('li');
        $content .= parent::wrapChild($childOutput);
        $content .= $this->closeTag('li');
        return $content;
    }

}

