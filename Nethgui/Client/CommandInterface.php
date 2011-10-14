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
     * The jQuery selector where to invoke the command
     * @return string
     */
    public function getTargetSelector();
    /**
     * The Nethgui method to be invoked
     * @return string
     */
    public function getMethod();
    /**
     * The array of the arguments to the Nethgui method
     * @return string
     */
    public function getArguments();

    public function isRedirection();
    
    public function getRedirectionUrl();
}

