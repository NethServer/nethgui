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
class Inset extends \Nethgui\Widget\Xhtml
{

    public function render()
    {
        $name = $this->getAttribute('name');
        $flags = $this->getAttribute('flags', 0);
        $value = $this->view->offsetGet($name);
        $content = '';

        if ($value instanceof \Nethgui\Core\ViewInterface && $this->view instanceof \Nethgui\Core\CommandReceiverAggregateInterface) {
            $innerRenderer = new \Nethgui\Renderer\Xhtml($value, $flags, $this->view->getCommandReceiver());
            $content = (String) $this->wrapView($innerRenderer);
        } else {
            $content = (String) $this->view->literal($value, $flags);
        }

        return $content;
    }

    private function wrapView(\Nethgui\Renderer\Xhtml $inset)
    {
        $module = $inset->getModule();

        $content = (String) $inset;
        $contentWidget = $this->view->literal($content);

        // 1. If we have a NOFORMWRAP give up here.
        if ($module instanceof \Nethgui\Core\Module\DefaultUiStateInterface
            && $module->getDefaultUiStyleFlags() & \Nethgui\Core\Module\DefaultUiStateInterface::STYLE_NOFORMWRAP) {
            return $contentWidget;
        }

        // 2. Wrap automatically a FORM tag only if instancof RequestHandler and no FORM tag has been emitted.
        if ($module instanceof \Nethgui\Core\RequestHandlerInterface
            && stripos($content, '<form ') === FALSE) {
            // Wrap a simple module into a FORM tag automatically
            $contentWidget = $inset->form()->insert($contentWidget);

            // Re-wrap a simple root-module into an Action div
            if ($module->getParent() === NULL) {
                $contentWidget = $inset->panel()->setAttribute('class', 'Action')->insert($contentWidget);
            }
        }

        return $contentWidget;
    }

}
