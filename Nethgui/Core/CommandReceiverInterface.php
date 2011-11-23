<?php
/**
 * @package Core
 */

/**
 * @package Core
 */
interface Nethgui\Core\CommandReceiverInterface
{
    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function executeCommand($name, $arguments);
}

