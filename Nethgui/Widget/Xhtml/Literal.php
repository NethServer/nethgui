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

        $flags = $this->getAttribute('flags', 0);

        if ($value instanceof Nethgui_Renderer_WidgetFactoryInterface) {
            $valueFlags = $value->getDefaultFlags() | $this->view->getDefaultFlags();
        } else {
            $valueFlags = 0;
        }

        if ($value instanceof Nethgui_Core_ViewInterface && $this->view instanceof Nethgui_Core_CommandReceiverAggregateInterface) {
            $value = new Nethgui_Renderer_Xhtml($value, $valueFlags, $this->view->getCommandReceiver());
        }

        $content = (String) $value;

        if ($this->getAttribute('hsc', FALSE) === TRUE) {
            $content = htmlspecialchars($content);
        }

        return $content;
    }

    public function setAttribute($attribute, $value)
    {
        if ($attribute == 'data' && $value instanceof Nethgui_Core_ViewInterface) {
            parent::setAttribute('name', $value->getModule()->getIdentifier());
        }
        return parent::setAttribute($attribute, $value);
    }

}