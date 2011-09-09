<?php
/**
 * @package Core
 */

/**
 * A Nethgui_Core_ModuleSetInterface implementation contains all known modules.
 * 
 * It allows finding a module and iterating over root modules,
 * arranged in a hierarchical structure.
 *
 * @package Core
 */
interface Nethgui_Core_ModuleSetInterface
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

