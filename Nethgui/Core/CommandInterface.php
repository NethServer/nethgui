<?php
/**
 * @package Core
 */

namespace Nethgui\Core;

/**
 * Invoke a Nethgui javascript method on the client-side.
 *
 * Applies Command pattern
 *
 * Roles:
 * - Client, a Module
 * - Invoker, a Renderer
 * - Receiver, a Widget or the client-side javascript components.
 *
 * @see http://en.wikipedia.org/wiki/Command_pattern
 * @package Core
 */
interface CommandInterface
{

    /**
     * Executes the command on the given receiver object
     *
     * Called by Invoker
     *
     * @see setReceiver()
     * @param object $context
     * @return mixed.
     */
    public function execute();

    /**
     * Set the command receiver, the object where the command is executed
     *
     * @param object An object implementing either CommandReceiverInterface or \Nethgui\Client\CommandReceiverAggregateInterface
     * @see CommandReceiverInterface
     * @see \Nethgui\Client\CommandReceiverAggregateInterface
     * @return CommandInterface
     */
    public function setReceiver($receiver);

    /**
     * @see execute()
     * @return boolean
     */
    public function isExecuted();
}

