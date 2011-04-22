<?php
/**
 * @package Core
 * @subpackage Module
 * @author Davide Principi <davide.principi@nethesis.it>
 */

/**
 * Shows other actions in disabled state.
 *
 * The client framework receives the complete views in a "disabled" state.
 * It will enable required view subparts when required.
 *
 * @package Core
 * @subpackage Module
 */
class NethGui_Core_Module_ActionIndex extends NethGui_Core_Module_Action
{

    public function prepareView(NethGui_Core_ViewInterface $view, $mode)
    {
        parent::prepareView($view, $mode);
        $view->setTemplate(array($this, 'renderActions'));
        foreach ($this->getParent()->getChildren() as $action) {
            if ($action === $this
                || ! $action instanceof NethGui_Core_ModuleInterface) {
                continue;
            }

            if ( ! $action->isInitialized()) {
                $action->initialize();
            }

            $innerView = $view->spawnView($action);
            $view[$action->getIdentifier()] = $innerView;
            $action->prepareView($innerView, $mode);
        }
    }

    public function renderActions($state)
    {
        $output = '';
        foreach ($this->getParent()->getChildren() as $action) {
            $actionIdentifier =$action->getIdentifier();
            if(!isset($state['view'][$actionIdentifier])) {
                continue;
            }
            $output .= $state['view'][$actionIdentifier]->render();
        }
        return $output;
    }

}
