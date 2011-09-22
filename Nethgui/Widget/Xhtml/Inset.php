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
        $flags = $this->getAttribute('flags');
        $value = $this->getAttribute('value', $this->view[$name]);
        $content = '';
      
        if ($value instanceof Nethgui_Core_ViewInterface) {
            $content .= $this->includeTemplate($value, $flags);
        } else {
            $content .= $value; // add plain xhtml text
        }

        return $content;
    }

    private function includeTemplate(Nethgui_Core_ViewInterface $view, $flags = 0)
    {
        $dview = new Nethgui_Renderer_Xhtml($view, $flags);
        $module = $view->getModule();

        $languageCatalog = NULL;
        if ($module instanceof Nethgui_Core_LanguageCatalogProvider) {
            $languageCatalog = $module->getLanguageCatalog();
        }

        $state = array('view' => $dview);
        $content = Nethgui_Framework::getInstance()->renderView($view->getTemplate(), $state, $languageCatalog);

        $contentWidget = $dview->literal($content);

        if ($module instanceof Nethgui_Core_RequestHandlerInterface
            && ! $module instanceof Nethgui_Core_ModuleCompositeInterface
            && stripos($content, '<form ') === FALSE) {
            // Wrap a simple module into a FORM tag automatically
            $contentWidget = $dview->form($flags)->insert($contentWidget);
        }

        return (String) $contentWidget;
    }

}
