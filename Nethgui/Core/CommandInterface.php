<?php
/**
 * @package Core
 */

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
interface Nethgui_Core_CommandInterface
{

    /**
     * Executes the command on the given receiver object
     *
     * @see setReceiver()
     * @param object $context
     * @return mixed.
     */
    public function execute();

    /**
     * Set the command receiver, the object where the command is executed
     *
     * @param object An object implementing either Nethgui_Core_CommandReceiverInterface or Nethgui_Client_CommandReceiverAggregateInterface
     * @see Nethgui_Core_CommandReceiverInterface
     * @see Nethgui_Client_CommandReceiverAggregateInterface
     * @return Nethgui_Core_CommandInterface
     */
    public function setReceiver($receiver);

    /**
     * @see execute()
     * @return boolean
     */
    public function isExecuted();
}

