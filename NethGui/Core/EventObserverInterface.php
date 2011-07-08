<?php
/**
 * NethGui
 *
 * @package Core
 */

/**
 * Describes an object that will be notified of a signal-event call completion
 * 
 * @package Core
 * @author Davide Principi <davide.principi@nethesis.it>
 */
interface NethGui_Core_EventObserverInterface
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
