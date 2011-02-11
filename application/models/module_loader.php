<?php

final class Module_loader extends CI_Model implements ModuleLoaderInterface, PolicyEnforcementPointInterface {

    /**
     * @return ModuleInterface
     */
    public function findModule($moduleName)
    {
        $a = $this->findModule(test);
        
    }

    public function getRootModule()
    {
        
    }

    public function setPolicyDecisionPoint(PolicyDecisionPointInterface $pdp)
    {

    }

}
?>
