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
class Nethgui_Widget_Xhtml_RadioButton extends Nethgui_Widget_Xhtml
{

    public function render()
    {
        $name = $this->getAttribute('name');
        $value = $this->getAttribute('value');
        $flags = $this->getAttribute('flags');
        $label = $this->getAttribute('label', $name . '_' . $value . '_label');
        $content = '';

        $attributes = array(
            'type' => 'radio',
            'value' => strval($value),
            'id' => $this->view->getUniqueId($name . '_' . $value . '_' . self::getInstanceCounter())
        );

        if ($value === $this->view[$name]) {
            $flags |= Nethgui_Renderer_Abstract::STATE_CHECKED;
        }

        $flags = $this->applyDefaultLabelAlignment($flags, Nethgui_Renderer_Abstract::LABEL_RIGHT);

        $content .= $this->labeledControlTag('input', $name, $label, $flags, 'RadioButton', $attributes);


        return $content;        
    }

}
