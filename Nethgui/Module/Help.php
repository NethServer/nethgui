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
    public $moduleSet;
    
    public function initialize()
    {
        parent::initialize();
        $this->loadChildren(array('_Show', '_Template'));

        foreach ($this->getChildren() as $child) {
            $child->moduleSet = $this->moduleSet;
        }
    }

}
