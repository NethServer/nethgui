<?php
/**
 */

namespace Nethgui\Core;

/**
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

