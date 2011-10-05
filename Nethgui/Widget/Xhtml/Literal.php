<?php
/**
 * @package Widget
 * @subpackage Xhtml
 * @author Davide Principi <davide.principi@nethesis.it>
 * @internal
 * @ignore
 */

/**
 * Renders the given literal string, optionally escaping special html characters
 * through PHP htmlspecialchars() function.
 *
 * Attributes:
 * - `data` any string or object with string representation
 * - `hsc` boolean
 *
 * @package Widget
 * @subpackage Xhtml
 * @internal
 * @ignore
 */
class Nethgui_Widget_Xhtml_Literal extends Nethgui_Widget_Xhtml
{

    public function render()
    {
        $value = $this->getAttribute('data', '');

        $content = '';

        if ($value instanceof Nethgui_Core_ViewInterface) {
            $renderer = clone $this->view;
            $renderer->setInnerView($value);
            $content = (String) $renderer; 
        } else {
            $content = (String) $value;
        }

        if ($this->getAttribute('hsc', FALSE) === TRUE) {
            $content = htmlspecialchars($content);
        }

        return $content;
    }

}