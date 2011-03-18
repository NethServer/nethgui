<?php

/**
 * NethGui
 *
 * @package NethGuiFramework
 */

/**
 * HostConfigurationInterface
 *
 * An NethGui_Core_HostConfigurationInterface implementing object allows
 * access to configuration databases and event signaling.
 *
 *
 * @package NethGuiFramework
 * @subpackage HostConfiguration
 */
interface NethGui_Core_HostConfigurationInterface
{
    /**
     * @var string
     * @return NethGui_Core_ConfigurationDatabase
     */
    public function getDatabase($database);

    /**
     * Signal an event and return the status
     *
     * @param string $event Event name
     * @param array &$output Optional output array. If the output argument is present, then the specified array will be filled with every line of output from the event.
     * @access public
     * @return boolean true on success, false otherwise
     */
    public function signalEvent($event, &$output=array());
}

