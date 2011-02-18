<?php

interface ModuleAggregationInterface {

    /**
     * The Client of this interface asks for activation of a certain Module
     * if it wishes to use its Panel.
     */
    public function activate($moduleIdentifier);
    
    /**
     * Since Modules are arranged in a composition, one of them plays the
     * "root" role.
     * @return ModuleCompositeInterface
     */
    public function findRootModule();

    /**
     * @return ModuleInterface
     */
    public function findModule($moduleIdentifier);

    /**
     *
     */
    public function attachModule(ModuleInterface $module);
    
}

