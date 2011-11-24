<?php
/**
 */

namespace Nethgui\Core;

/**
 * Allows forwarding of command execution to another object
 *
 * @see getCommandReceiver()
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
