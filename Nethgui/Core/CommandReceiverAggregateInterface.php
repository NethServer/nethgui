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
interface Nethgui_Core_CommandReceiverAggregateInterface
{
    /**
     *
     *
     * @return Nethgui_Core_CommandReceiverInterface
     */
    public function getCommandReceiver();
}
