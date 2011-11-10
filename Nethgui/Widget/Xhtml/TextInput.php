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
class Nethgui_Widget_Xhtml_TextInput extends Nethgui_Widget_Xhtml
{

    public function render()
    {
        $name = $this->getAttribute('name');
        $value = $this->getAttribute('value');
        $flags = $this->getAttribute('flags');
        $label = $this->getAttribute('label', $name . '_label');
        $cssClass = $this->getAttribute('class', '');
        $cssClass = trim('TextInput ' . $cssClass);
        $content ='';

        if (is_null($value)) {
            $value = $this->view[$name];
        }
        
        $attributes = array(
            'value' => strval($this->view[$name]),
            'type' => ($flags & Nethgui_Renderer_WidgetFactoryInterface::TEXTINPUT_PASSWORD) ? 'password' : 'text',
            'placeholder' => $this->getAttribute('placeholder',false),
        );

        $flags = $this->applyDefaultLabelAlignment($flags, Nethgui_Renderer_WidgetFactoryInterface::LABEL_ABOVE);

        // Check if $name is in the list of invalid parameters.
        if (isset($this->view['__invalidParameters']) && in_array($name, $this->view['__invalidParameters'])) {
            $flags |= Nethgui_Renderer_WidgetFactoryInterface::STATE_VALIDATION_ERROR;
        }

        $content .= $this->labeledControlTag($label, 'input', $name, $flags, $cssClass, $attributes);

        return $content;
    }

}

