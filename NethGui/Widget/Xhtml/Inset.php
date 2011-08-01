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
        $name = $this->getParameter('name');
        $value = $this->getParameter('value');
        $flags = $this->getParameter('flags');
        $content ='';

        $value = $this->view[$offset];

        if ($value instanceof NethGui_Core_ViewInterface) {
            $insetRenderer = new self($value);
            $insetRenderer->includeTemplate($value->getTemplate(), $this->flags);
            $this->append((String) $insetRenderer, FALSE);

            $content .= $insetRenderer->render();

        } else {
            $content .= htmlspecialchars($value);
        }

        return $content;
    }

    public function includeTemplate($template, $flags = 0)
    {
        $languageCatalog = NULL;
        if ($this->view->getModule() instanceof NethGui_Core_LanguageCatalogProvider) {
            $languageCatalog = $this->view->getModule()->getLanguageCatalog();
        }

        // FIXME: pass $flags
        $state = array(
            'view' => $this->view,
        );

        $content = NethGui_Framework::getInstance()->renderView($template, $state, $languageCatalog);

        return $content;
    }

}
