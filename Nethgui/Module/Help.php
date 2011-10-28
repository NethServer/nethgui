<?php
/**
 * @package Module
 */

/**
 * @package Module
 * @author Davide Principi <davide.principi@nethesis.it>
 */
class Nethgui_Module_Help extends Nethgui_Core_Module_Controller
{

    /**
     *
     * @var Nethgui_Core_ModuleSetInterface
     */
    private $moduleSet;

    public function __construct(Nethgui_Core_ModuleSetInterface $moduleSet)
    {
        parent::__construct(NULL);
        $this->moduleSet = $moduleSet;
    }

    public function initialize()
    {
        parent::initialize();
        $this->loadChildren(array('_Show', '_Template', '_Read'));

        // Propagate moduleSet to children
        foreach ($this->getChildren() as $child) {
            $child->moduleSet = $this->moduleSet;
        }
    }

    public function prepareView(Nethgui_Core_ViewInterface $view, $mode)
    {
        if (is_null($this->currentAction)) {
            $view->setTemplate('Nethgui_Template_Help');
        } else {
            parent::prepareView($view, $mode);
        }
    }

}
