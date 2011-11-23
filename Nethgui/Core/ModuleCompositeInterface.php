<?php
/**
 * @package Core
 */

/**
 * A complex module, composed by other modules, must implement this interface.
 *
 * @package Core
 */
interface Nethgui\Core\ModuleCompositeInterface
{

    /**
     * @return array An array of Nethgui\Core\ModuleInterface implementing objects.
     */
    public function getChildren();

    /**
     * Adds a child to this Composite. Implementations must send a setParent()
     * message to $module.
     * @param Nethgui\Core\ModuleInterface $module The child module.
     */
    public function addChild(Nethgui\Core\ModuleInterface $module);
}

