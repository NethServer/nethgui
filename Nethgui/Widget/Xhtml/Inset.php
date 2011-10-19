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
class Nethgui_Widget_Xhtml_Inset extends Nethgui_Widget_Xhtml
{

    public function render()
    {
        $name = $this->getAttribute('name');
        $flags = $this->getAttribute('flags', 0);
        $value = $this->view->offsetGet($name);
        $content = '';

        if ($value instanceof Nethgui_Renderer_Abstract) {
            $content = (String) $this->wrapView($value);
        } else {
            $content = (String) $this->view->literal($value, $flags);
        }

        return $content;
    }

    protected function wrapView(Nethgui_Renderer_Abstract $inset)
    {
        $module = $inset->getModule();
        $flags = $this->getAttribute('flags', 0);

        $inset->setDefaultFlags($this->view->getDefaultFlags() | $flags);
        $content = (String) $inset;
        $contentWidget = $this->view->literal($content);

        // 1. If we have a NOFORMWRAP give up here.
        if ($module instanceof Nethgui_Core_Module_DefaultUiStateInterface
            && $module->getDefaultUiStyleFlags() & Nethgui_Core_Module_DefaultUiStateInterface::STYLE_NOFORMWRAP) {
            return $contentWidget;
        }

        // 2. Wrap automatically a FORM tag only if instancof RequestHandler and no FORM tag has been emitted.
        if ($module instanceof Nethgui_Core_RequestHandlerInterface
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
