<?php
/**
 * @package NethGui
 * @subpackage Module
 */

/**
 * @todo describe class
 * @package NethGui
 * @subpackage Module
 */
class NethGui_Module_RemoteAccess extends NethGui_Core_Module_Composite implements NethGui_Core_TopModuleInterface
{
    public function initialize()
    {
        parent::initialize();
        // TODO: implement child autoloading in Composite.
        foreach (array('Pptp', 'RemoteManagement', /*'Ssh',*/ 'Ftp') as $dependency) {
            $childModuleClass = 'NethGui_Module_RemoteAccess_' . $dependency;
            $childModule = new $childModuleClass();
            $childModule->setHostConfiguration($this->getHostConfiguration());
            $this->addChild($childModule);
        }

        $this->declareImmutable('save', 1);
    }

    public function prepareView(NethGui_Core_ViewInterface $view, $mode)
    {
        parent::prepareView($view, $mode);
        $view->setTemplate('NethGui_Core_View_form');
    }

    public function getParentMenuIdentifier()
    {
        return "Security";
    }

}