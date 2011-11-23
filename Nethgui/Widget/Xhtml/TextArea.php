<?php
/**
 * @package Widget
 * @subpackage Xhtml
 * @author Davide Principi <davide.principi@nethesis.it>
 * @ignore
 */

namespace Nethgui\Widget\Xhtml;

/**
 * This is a multi-line text input field.
 *
 * A _console_ widget is a readonly text area where strings can only be appended
 * (see appendOnly attribute)
 *
 * Attributes:
 * - dimensions
 * - appendOnly
 * - data
 *
 * @package Widget
 * @subpackage Xhtml
 * @ignore
 */
class \Nethgui\Widget\Xhtml_TextArea extends \Nethgui\Widget\Xhtml
{

    public function render()
    {
        $name = $this->getAttribute('name');
        $flags = $this->getAttribute('flags', 0);        
        $dimensions = explode('x', $this->getAttribute('dimensions', '20x30'));
        $rows = intval($dimensions[0]);
        $cols = intval($dimensions[1]);
        $label = $this->getAttribute('label', $name . '_label');
        $value = htmlspecialchars($this->view[$name]);

        $tagContent = '';
        $htmlAttributes = array(
            'rows' => $rows,
            'cols' => $cols,
        );

        $cssClass = trim('Textarea ' . $this->getAttribute('class', ''));

        if ($this->getAttribute('appendOnly', FALSE)) {
            $cssClass .= ' appendOnly';
        }

        return $this->labeledControlTag($label, 'textarea', $name, $flags, $cssClass, $htmlAttributes, $value);
    }

}
