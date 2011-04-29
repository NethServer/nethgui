<?php
/**
 * @package Core
 * @subpackage Module
 * @author Davide Principi <davide.principi@nethesis.it>
 */

/**
 * Shows other actions in a "disabled" state.
 *
 * The client framework receives the complete views in a "disabled" state.
 * It will enable required view subparts when required.
 *
 * @package Core
 * @subpackage Module
 */
class NethGui_Core_Module_ActionIndex extends NethGui_Core_Module_Standard
{

    public function prepareView(NethGui_Core_ViewInterface $view, $mode)
    {
        parent::prepareView($view, $mode);

        // Output is generated in VIEW_REFRESH mode only.
        if ($mode === self::VIEW_UPDATE) {
            return;
        }

        $view->setTemplate(array($this, 'renderActions'));
        foreach ($this->getParent()->getChildren() as $action) {
            // Always skip this instance
            if ($action === $this
                || ! $action instanceof NethGui_Core_ModuleInterface) {
                continue;
            }

            // Initialize
            if ( ! $action->isInitialized()) {
                $action->initialize();
            }

            // Prepare a sub view for $action
            $innerView = $view->spawnView($action, TRUE);
            $action->prepareView($innerView, $mode);
            $innerView['__action'] = 'index';
        }
    }

    /**
     * A callback render Template.
     *
     * It concatenates all the child views of the parent controller.
     *
     * @param array $state The view state
     * @return string The rendered views.
     */
    public function renderActions($state)
    {
        $output = '';
        foreach ($this->getParent()->getChildren() as $action) {
            $actionIdentifier = $action->getIdentifier();

            if ( ! isset($state['view'][$actionIdentifier])) {
                continue;
            }

            $output .= $state['view'][$actionIdentifier]->render();
        }
        return $output;
    }

}
