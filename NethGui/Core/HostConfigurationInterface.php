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
     * Obtain an adapter for a "key" or "prop".
     * Adapters may be aggregated into an Adapter aggregation.
     *
     * @see NethGui_Core_AdapterAggregationInterface
     * @param string $database Database name
     * @param string $key Key connected to the adapter.
     * @param string $prop Optional. Set to a prop name to connect a prop instead of a key.
     * @param string $separator Specify a single character string to obtain an array-like interface.
     * @return NethGui_Core_AdapterInterface
     */
    public function getAdapter($database, $key, $prop = NULL, $separator = NULL);

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

