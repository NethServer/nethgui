<?php
/**
 * NethGui
 *
 * @package ExtensibleApi
 */

/**
 * A complex module, composed by other modules, must implement this interface.
 *
 * @package ExtensibleApi
 */
interface ModuleCompositeInterface {
    /**
     * @return array An array of ModuleInterface implementing objects.
     */
    public function getChildren();   

    /**
     * Adds a child to this Composite. Implementations must send a setParent()
     * message to $module.
     * @param ModuleInterface $module The child module.
     */
    public function addChild(ModuleInterface $module);

}

