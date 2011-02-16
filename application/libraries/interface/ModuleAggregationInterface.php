<?php

interface ModuleAggregationInterface {
    /**
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

