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
interface NethGui_Core_ModuleCompositeInterface
{

    /**
     * @return array An array of NethGui_Core_ModuleInterface implementing objects.
     */
    public function getChildren();

    /**
     * Adds a child to this Composite. Implementations must send a setParent()
     * message to $module.
     * @param NethGui_Core_ModuleInterface $module The child module.
     */
    public function addChild(NethGui_Core_ModuleInterface $module);
}

