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
class NethGui_Widget_Xhtml_RadioButton extends NethGui_Widget_Xhtml
{

    public function render()
    {
        $name = $this->getAttribute('name');
        $value = $this->getAttribute('value');
        $flags = $this->getAttribute('flags');
        $content = '';

        $attributes = array(
            'type' => 'radio',
            'value' => strval($value),
            'id' => $this->view->getUniqueId($name . '_' . $value . '_' . self::getInstanceCounter())
        );

        if ($value === $this->view[$name]) {
            $flags |= NethGui_Renderer_Abstract::STATE_CHECKED;
        }

        $flags = $this->applyDefaultLabelAlignment($flags, NethGui_Renderer_Abstract::LABEL_RIGHT);

        $content .= $this->labeledControlTag('input', $name, $name . '_' . $value, $flags, 'RadioButton', $attributes);


        return $content;        
    }

}