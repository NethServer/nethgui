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
        $name = $this->getParameter('name');
        $value = $this->getParameter('value');
        $flags = $this->getParameter('flags');
        $content = '';

        $attributes = array(
            'type' => 'radio',
            'value' => strval($value),
            'id' => $this->getUniqueId($name . '_' . $value . '_' . self::getInstanceCounter())
        );

        if ($value === $this->view[$name]) {
            $flags |= self::STATE_CHECKED;
        }

        $flags = $this->applyDefaultLabelAlignment($flags, self::LABEL_RIGHT);

        $content .= $this->labeledControlTag('input', $name, $name . '_' . $value, $flags, '', $attributes);


        return $content;        
    }

}
