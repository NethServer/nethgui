<?php
/**
 * @package Module
 */

/**
 * @todo describe class
 * @package Module
 */
class NethGui_Module_RemoteAccess extends NethGui_Core_Module_Composite implements NethGui_Core_TopModuleInterface
{
    public function __construct()
    {
        parent::__construct(NULL, self::TEMPLATE_FORM);
    }
    
    public function initialize()
    {
        parent::initialize();
        // TODO: implement child autoloading in Composite.
        foreach (array('Pptp', /*'RemoteManagement', /*'Ssh', 'Ftp'*/) as $dependency) {
            $childModuleClass = 'NethGui_Module_RemoteAccess_' . $dependency;
            $childModule = new $childModuleClass();
            $childModule->setHostConfiguration($this->getHostConfiguration());
            $this->addChild($childModule);
        }
    }

    public function getParentMenuIdentifier()
    {
        return "Security";
    }

}