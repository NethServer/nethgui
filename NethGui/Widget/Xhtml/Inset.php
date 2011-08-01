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
class NethGui_Widget_Xhtml_Inset extends NethGui_Widget_Xhtml
{

    public function render()
    {
        $name = $this->getAttribute('name');
        $value = $this->getAttribute('value');
        $flags = $this->getAttribute('flags');
        $content ='';

        $value = $this->view[$name];

        if ($value instanceof NethGui_Core_ViewInterface) {            
            $content .= $this->includeTemplate($value, $flags);
        } else {
            $content .= htmlspecialchars($value);
        }

        return $content;
    }

    private function includeTemplate(NethGui_Core_ViewInterface $view, $flags = 0)
    {
        $languageCatalog = NULL;
        if ($view->getModule() instanceof NethGui_Core_LanguageCatalogProvider) {
            $languageCatalog = $view->getModule()->getLanguageCatalog();
        }

        // FIXME: pass $flags
        $state = array(
            'view' => new NethGui_Renderer_Xhtml($view),
        );

        $content = NethGui_Framework::getInstance()->renderView($view->getTemplate(), $state, $languageCatalog);

        return $content;
    }

}
