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
class Nethgui\Widget\Xhtml_RadioButton extends Nethgui\Widget\Xhtml
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
            $flags |= Nethgui\Renderer\WidgetFactoryInterface::STATE_CHECKED;
        }

        $flags = $this->applyDefaultLabelAlignment($flags, Nethgui\Renderer\WidgetFactoryInterface::LABEL_RIGHT);

        $content .= $this->labeledControlTag($label, 'input', $name, $flags, 'RadioButton', $attributes);


        return $content;
    }

}
