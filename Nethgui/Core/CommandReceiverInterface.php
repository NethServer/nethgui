<?php
/**
 * @package Core
 */

namespace Nethgui\Core;

/**
 * @package Core
 */
interface CommandReceiverInterface
{
    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function executeCommand($name, $arguments);
}

