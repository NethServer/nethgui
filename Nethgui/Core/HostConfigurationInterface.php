<?php
/**
 * Nethgui
 *
 * @package Core
 */

/**
 * HostConfigurationInterface
 *
 * An Nethgui_Core_HostConfigurationInterface implementing object allows
 * access to configuration databases and event signaling.
 *
 *
 * @package Core
 */
interface Nethgui_Core_HostConfigurationInterface
{

    /**
     * @var string
     * @return Nethgui_Core_ConfigurationDatabase
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
     * @see Nethgui_Adapter_AdapterAggregationInterface
     * @see getMapAdapter()
     * @param string $database Database name
     * @param string $key Key connected to the adapter.
     * @param string $prop Optional - Set to a prop name to connect a prop instead of a key.
     * @param string $separator Optional - Specify a single character string to obtain an ArrayAccess and Countable interface.
     * @return Nethgui_Adapter_AdapterInterface
     */
    public function getIdentityAdapter($database, $key, $prop = NULL, $separator = NULL);

    /**
     * Get a mapping Adapter.
     *
     * A Map Adapter maps database values through a "reader" and a "writer"
     * converter method. Values are specified through $args parameter.
     *
     * @see getIdentityAdapter()
     * @see Nethgui_Adapter_AdapterAggregationInterface
     * @param callback $readCallback If $args has N elements $readCallback must accept N parameters and return a value.
     * @param callback $writeCallback If $args has N elements $writeCallback must accept a parameter and return an array of N elements.
     * @param array $args An array of arrays in the form ($database, $key, $prop). $prop is optional.
     *
     * @return Nethgui_Adapter_AdapterInterface
     */
    public function getMapAdapter($readCallback, $writeCallback, $args);

    /**
     * Get a table adapter
     * 
     * A table adapter has an array interface, where each element represents a row, and each row
     * is an array itself. 
     * 
     * @param string $database Database name
     * @param string $typeOrKey The type of the keys to read from database or the key value where the data is stored
     * @param string $filterOrProp The string to filter the table data or set to a prop name to connect a prop instead of a key.
     * @param array $separators An array of one or two separator strings. The first is for the rows, the second for the columns. Set to NULL if $typeOrKey is a TYPE!
     * @return Nethgui_Adapter_AdapterInterface An adapter with array and countable interfaces.
     */
    public function getTableAdapter($database, $typeOrKey, $filterOrProp = NULL, $separators = NULL);

    /**
     * Signal an event and return the status synchronously
     *
     * @param string $event Event name
     * @param array $argv Optional event arguments
     * @return Nethgui_Core_SystemCommandInterface
     */
    public function signalEvent($event, $arguments = array());

    /**
     * Ask the host configuration to signal the given event lately, after all database 
     * write operations occurred.
     * 
     * @param string $event
     * @param array $argv Optional event arguments
     * @param Nethgui_Core_EventObserverInterface $observer Optional
     */
    public function signalEventFinally($eventName, $argv = array(), $observer = NULL);

    /**
     * PHP exec() wrapper
     *
     * The arguments are replaced in the command string, searching for placeholders
     * in the form ${1} .. ${N}.
     *
     * NOTE: Only placeholders corresponding to i+1 element in the
     * arguments array are replaced.
     *
     * @param string $command
     * @param array $arguments Arguments for the command. Will be shell-escaped.
     * @return Nethgui_Core_SystemCommandInterface
     */

    public function exec($command, $arguments = array());
    
    public function trace(Nethgui_Core_UserInterface $user, $command, $arguments = array());
        
}


