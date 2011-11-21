<?php
/**
 * @package Core
 */

/**
 * @package Core
 */
interface Nethgui_Core_CommandReceiverInterface
{
    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function executeCommand($name, $arguments);
}

