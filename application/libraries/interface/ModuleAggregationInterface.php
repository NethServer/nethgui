<?php

interface ModuleAggregationInterface {

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

