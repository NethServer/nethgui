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
class Nethgui_Widget_Xhtml_TextInput extends Nethgui_Widget_Xhtml
{

    public function render()
    {
        $name = $this->getAttribute('name');
        $value = $this->getAttribute('value');
        $flags = $this->getAttribute('flags');
        $content ='';

        if (is_null($value)) {
            $value = $this->view[$name];
        }
        
        $attributes = array(
            'value' => strval($this->view[$name]),
            'type' => 'text',
        );

        $flags = $this->applyDefaultLabelAlignment($flags, Nethgui_Renderer_Abstract::LABEL_ABOVE);

        // Check if $name is in the list of invalid parameters.
        if (isset($this->view['__invalidParameters']) && in_array($name, $this->view['__invalidParameters'])) {
            $flags |= Nethgui_Renderer_Abstract::STATE_VALIDATION_ERROR;
        }

        $content .= $this->labeledControlTag('input', $name, $name, $flags, 'TextInput', $attributes);

        return $content;
    }

}

