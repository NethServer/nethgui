<?php
/**
 * @package Core
 */

namespace Nethgui\Core;

/**
 * Allows forwarding of command execution to another object
 *
 * @see getCommandReceiver()
 * @package Core
 * @author Davide Principi <davide.principi@nethesis.it>
 */
interface CommandReceiverAggregateInterface
{
    /**
     *
     *
     * @return CommandReceiverInterface
     */
    public function getCommandReceiver();
}
