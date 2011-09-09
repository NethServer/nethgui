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
class Nethgui_Widget_Xhtml_CheckBox extends Nethgui_Widget_Xhtml
{

    public function render()
    {
        $name = $this->getAttribute('name');
        $value = $this->getAttribute('value');
        $uncheckedValue = $this->getAttribute('uncheckedValue', '');
        $flags = $this->getAttribute('flags');
        $content = '';

        $attributes = array(
            'type' => 'checkbox',
            'value' => strval($value),
        );

        if ($value == $this->view[$name]) {
            $flags |= Nethgui_Renderer_Abstract::STATE_CHECKED;
        }

        $flags = $this->applyDefaultLabelAlignment($flags, Nethgui_Renderer_Abstract::LABEL_RIGHT);

        $content .= $this->controlTag('input', $name, $flags, 'HiddenConst', array('type' => 'hidden', 'value' => $uncheckedValue, 'id' => FALSE));
        $content .= $this->labeledControlTag('input', $name, $name, $flags, 'CheckBox', $attributes);

        return $content;
    }

}