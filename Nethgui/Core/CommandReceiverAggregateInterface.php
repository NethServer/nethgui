<?php
/**
 * @package Core
 */

/**
 * Allows forwarding of command execution to another object
 *
 * @see getCommandReceiver()
 * @package Core
 * @author Davide Principi <davide.principi@nethesis.it>
 */
interface Nethgui\Core\CommandReceiverAggregateInterface
{
    /**
     *
     *
     * @return Nethgui\Core\CommandReceiverInterface
     */
    public function getCommandReceiver();
}
