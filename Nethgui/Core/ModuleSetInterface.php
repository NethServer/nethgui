<?php
/**
 * @package Core
 */

namespace Nethgui\Core;

/**
 * A ModuleSetInterface implementation contains all known modules.
 * 
 * It allows finding a module and iterating over root modules,
 * arranged in a hierarchical structure.
 *
 * @package Core
 */
interface ModuleSetInterface
{

    /**
     * @return RecursiveIterator A RecursiveIterator to iterate over all accessible Modules
     */
    public function getModules();

    /**
     * @param string $moduleIdentifier
     * @return ModuleInterface
     */
    public function findModule($moduleIdentifier);
}

