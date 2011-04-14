<?php
/**
 * NethGui
 *
 * @package NethGui
 * @subpackage Core
 */

/**
 * HostConfigurationInterface
 *
 * An NethGui_Core_HostConfigurationInterface implementing object allows
 * access to configuration databases and event signaling.
 *
 *
 * @package NethGui
 * @subpackage Core
 */
interface NethGui_Core_HostConfigurationInterface
{

    /**
     * @var string
     * @return NethGui_Core_ConfigurationDatabase
     */
    public function getDatabase($database);

    /**
     * Get an adapter for a "key" or "prop".
     *
     * An Identity Adapter is associated with a database value stored in a key
     * or prop value. If a $separator character is specified, the adapter
     * is enhanced with an ArrayAccess interface, and the value is stored
     * imploding its elements on that $separator.
     *
     * @see NethGui_Core_AdapterAggregationInterface
     * @see getMapAdapter()
     * @param string $database Database name
     * @param string $key Key connected to the adapter.
     * @param string $prop Optional - Set to a prop name to connect a prop instead of a key.
     * @param string $separator Optional - Specify a single character string to obtain an ArrayAccess and Countable interface.
     * @return NethGui_Core_AdapterInterface
     */
    public function getIdentityAdapter($database, $key, $prop = NULL, $separator = NULL);

    /**
     * Get a mapping Adapter.
     *
     * A Map Adapter maps database values through a "reader" and a "writer"
     * function. Values are specified through $args parameter.
     *
     * @see getIdentityAdapter()
     * @see NethGui_Core_AdapterAggregationInterface
     * @param callback $readCallback If $args has N elements $readCallback must accept N parameters and return a value.
     * @param callback $writeCallback If $args has N elements $writeCallback must accept a parameter and return an array of N elements.
     * @param array $args An array of arrays in the form ($database, $key, $prop). $prop is optional.
     *
     * @return NethGui_Core_AdapterInterface
     */
    public function getMapAdapter($readCallback, $writeCallback, $args);


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

