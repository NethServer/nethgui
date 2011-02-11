<?php

interface ModuleAggregateInterface {
    /**
     * @return ModuleInterface
     */
    public function getRootModule();

    /**
     * @return ModuleInterface
     */
    public function findModule($moduleName);
}
?>
