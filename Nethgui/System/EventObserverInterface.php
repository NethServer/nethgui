<?php
/**
 * Nethgui
 *
 * @package System
 */

/**
 * Describes an object that will be notified of a signal-event call completion
 * 
 * @package System
 * @author Davide Principi <davide.principi@nethesis.it>
 */
interface Nethgui_System_EventObserverInterface
{

    /**
     * This operation is performed after a signal-event has occurred and receives
     * the input and output parameters of the original call.
     *
     * @param string $eventName
     * @param array $args
     * @param array $output
     */
    public function notifyEventCompletion($eventName, $args, $exitStatus, $output);
}