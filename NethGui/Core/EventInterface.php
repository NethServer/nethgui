<?php

/**
 * NethGui
 *
 * @package NethGuiFramework
 */

/**
 * EventInterfaceInterface
 *
 * An NethGui_Core_EventInterface implementing event signaling
 *
 * @package NethGuiFramework
 */
interface NethGui_Core_EventInterface
{

    /**
     * Signal an event and return the status
     * 
     * @param string $event Event name
     * @param array &$output Optional output array. If the output argument is present, then the specified array will be filled with every line of output from the event.
     * @access public
     * @return boolean true on success, false otherwise
     */
    public function signalEvent($event,&$output=array());

}

