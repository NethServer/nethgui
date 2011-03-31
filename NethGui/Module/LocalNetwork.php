<?php
/**
 * @package Modules
 */

/**
 * TODO: describe class
 *
 * @package Modules
 */
class NethGui_Module_LocalNetwork extends NethGui_Core_Module_Table implements NethGui_Core_TopModuleInterface
{
    public function getTitle()
    {
        return "Local network";
    }

    public function getParentMenuIdentifier()
    {
        return NULL;
    }

    public function initialize()
    {
        parent::initialize();
        $this->declareImmutable('save', 1);
    }

    public function prepareView(NethGui_Core_ViewInterface $view, $mode)
    {
        parent::prepareView($view, $mode);
        $view->setTemplate('NethGui_Core_View_form');
    }

}

