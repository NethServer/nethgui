<?php
/**
 * @package Widget
 * @subpackage Xhtml
 * @author Davide Principi <davide.principi@nethesis.it>
 * @internal
 * @ignore
 */

namespace Nethgui\Widget\Xhtml;

/**
 *
 * @package Widget
 * @subpackage Xhtml
 * @internal
 * @ignore
 */
class Nethgui\Widget\Xhtml_CheckBox extends Nethgui\Widget\Xhtml
{

    public function render()
    {
        $name = $this->getAttribute('name');
        $value = $this->getAttribute('value');
        $uncheckedValue = $this->getAttribute('uncheckedValue', '');
        $flags = $this->getAttribute('flags');
        $label = $this->getAttribute('label', $name . '_label');
        $content = '';

        $flags = $this->applyDefaultLabelAlignment($flags, Nethgui\Renderer\WidgetFactoryInterface::LABEL_RIGHT);

        $attributes = array(
            'type' => 'checkbox',
            'value' => $value,
        );

        if ($value === $this->view[$name] || $flags & Nethgui\Renderer\WidgetFactoryInterface::STATE_CHECKED) {
            $attributes['checked'] = 'checked';
        }
       
        if ($uncheckedValue !== FALSE) {
            $content .= $this->controlTag('input', $name, $flags, 'HiddenConst', array('type' => 'hidden', 'value' => $uncheckedValue, 'id' => FALSE));
            if ($this->view[$name] === $uncheckedValue) {
                $attributes['checked'] = FALSE;
            }
        }
        
        $content .= $this->labeledControlTag($label, 'input', $name, $flags, 'CheckBox', $attributes);

        return $content;
    }

}
