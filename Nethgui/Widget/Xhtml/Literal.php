<?php
/**
 * @author Davide Principi <davide.principi@nethesis.it>
 * @internal
 * @ignore
 */

namespace Nethgui\Widget\Xhtml;

/**
 * Renders the given literal string, optionally escaping special html characters
 * through PHP htmlspecialchars() function.
 *
 * Attributes:
 * - `data` any string or object with string representation
 * - `hsc` boolean
 *
 * @internal
 * @ignore
 */
class Literal extends \Nethgui\Widget\XhtmlWidget
{

    public function render()
    {
        $value = $this->getAttribute('data', '');

        $content = '';

        $flags = $this->getAttribute('flags', 0);

        if ($value instanceof \Nethgui\Renderer\WidgetFactoryInterface) {
            $valueFlags = $value->getDefaultFlags() | $this->view->getDefaultFlags();
        } else {
            $valueFlags = 0;
        }

        if ($value instanceof \Nethgui\Core\ViewInterface && $this->view instanceof \Nethgui\Core\CommandReceiverAggregateInterface) {
            $value = new \Nethgui\Renderer\Xhtml($value, $valueFlags, $this->view->getCommandReceiver());
        }

        $content = (String) $value;

        if ($this->getAttribute('hsc', FALSE) === TRUE) {
            $content = htmlspecialchars($content);
        }

        return $content;
    }

    public function setAttribute($attribute, $value)
    {
        if ($attribute == 'data' && $value instanceof \Nethgui\Core\ViewInterface) {
            parent::setAttribute('name', $value->getModule()->getIdentifier());
        }
        return parent::setAttribute($attribute, $value);
    }

}
