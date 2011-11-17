<?php
/**
 * @package Client
 */

/**
 * Invoke a Nethgui javascript method on the client-side.
 *
 * @package Client
 */
interface Nethgui_Client_CommandInterface
{

    /**
     * Executes the command 
     * @return void.
     */
    public function execute();

    /**
     * The object that will receive the command
     * @param mixed $receiver 
     */
    public function setReceiver($receiver);

    public function getReceiver();

    public function getMethod();

    public function getArguments();
}

