<?php

/**
 * A ModuleSet contains all known modules. It allows adding and finding a
 * module and also iterating over root modules, arranged in a hierarchical
 * menu structure.
 */
interface ModuleSetInterface {

    /**
     * @return RecursiveIterator
     */
    public function getTopModules();

    /**
     * @return ModuleInterface
     */
    public function findModule($moduleIdentifier);

}

