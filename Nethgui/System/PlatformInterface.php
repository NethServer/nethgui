<?php
namespace Nethgui\System;

/*
 * Copyright (C) 2011 Nethesis S.r.l.
 * 
 * This script is part of NethServer.
 * 
 * NethServer is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * NethServer is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with NethServer.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Interface to the underlying platform
 *
 * @api
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
interface PlatformInterface
{
    /**
     * A valid service status is a 'disabled' or 'enabled' string.
     * @api
     */
    const SERVICESTATUS = 1248968160;

    /**
     * A valid *nix username token
     * @api
     */
    const USERNAME = 1248968161;

    /**
     * A not empty value
     * @api
     */
    const NOTEMPTY = 1248968162;

    /**
     * Accepts any value
     * @api
     */
    const ANYTHING = 1248968163;

    /**
     * Accept a value that represents a collection of any thing
     * @api
     */
    const ANYTHING_COLLECTION = 1248968164;

    /**
     * Accept a value that represents a collection of any Unix usernames
     * @api
     */
    const USERNAME_COLLECTION = 1248968165;

    /**
     * Accept positive integer
     * @api
     */
    const POSITIVE_INTEGER = 1248968166;

    /**
     * Accept a non-negative integer, an integer greater than or equal to zero
     * @api
     */
    const NONNEGATIVE_INTEGER = 1366805296;

    /**
     * Valid generic hostname
     *
     * @api
     * @see #478
     */
    const HOSTNAME = 1248968167;

    /**
     * Valid simple hostname without domain part
     * 
     * @api
     * @see #1052 
     */
    const HOSTNAME_SIMPLE = 1334736972;

    /**
     * Valid hostname with domain part (FQDN)
     * 
     * @api
     * @see #1052 
     */
    const HOSTNAME_FQDN = 1334741642;

    /**
     * Valid host name or ip address
     *
     * @api
     * @see #478
     */
    const HOSTADDRESS = 1248968168;

    /**
     * Valid date
     *
     * @api
     * @see #513
     */
    const DATE = 1248968169;

    /**
     * Valid time
     *
     * @api
     * @see #513
     */
    const TIME = 1248968170;

    /**
     * Boolean validator.
     * 
     * @api
     * '', '0', FALSE are FALSE boolean values. Other values are TRUE.
     */
    const BOOLEAN = 1248968171;

    /**
     * A valid IPv4 address like '192.168.1.1' 
     * 
     * @api
     */
    const IPv4 = 1248968172;

    /**
     * A valid IPv4 address like '192.168.1.1' ore empty
     * 
     * @api
     */
    const IPv4_OR_EMPTY = 1248968173;

    /**
     * A valid netmask address like '255.255.255.0' ore empty
     * 
     * @api
     */
    const NETMASK_OR_EMPTY = 1365512893;

    /**
     * Alias for VALID_IPv4 
     * 
     * @api
     */
    const IP = 1248968174;

    /**
     * Alias for VALID_IPv4_OR_EMPTY
     * 
     * @api
     */
    const IP_OR_EMPTY = 1248968175;

    /**
     * Alias for NETMASK_OR_EMPTY
     * 
     * @api
     */
    const IPv4_NETMASK_OR_EMPTY = 1365513038;

    /**
     * A valid TCP/UDP port number 0-65535
     * 
     * @api
     */
    const PORTNUMBER = 1248968176;

    /**
     * A choice between 'yes' and 'no' values
     * 
     * @api
     */
    const YES_NO = 1248968177;

    /**
     * A valid ipv4 netmask address like '255.255.255.0'
     * 
     * @api
     */
    const IPv4_NETMASK = 1248968178;

    /**
     * Alias for VALID_IPv4_NETMASK
     * 
     * @api
     */
    const NETMASK = 1248968179;

    /**
     * A valid mac address like 00:16:3E:78:7A:7B
     * 
     * @api 
     */
    const MACADDRESS = 1248968180;

    /**
     * Valid email address 
     * 
     * A restricted set of RFC5322 dot-atom form (sect 3.4.1)
     * 
     * @api 
     */
    const EMAIL = 1340359603;

    /**
     * An empty string
     *
     * This is useful to compose _OR_EMPTY rules
     *
     * @api
     */
    const EMPTYSTRING = 1368694834;

    /**
     * CIDR block validator
     *
     * Eg 192.144.33.0/24
     *
     * @api
     */
    const CIDR_BLOCK = 1402048238;


    /* ---------------------------------------8<-------------------------
         !!! TO DEVELOPERS !!!

         Define new constant values assigning
         the current Unix timestamp (`/bin/date +%s` ouput)
       -----8<----------------------------------------------------------- */

    /**
     * @api
     * @var string
     * @return \Nethgui\System\DatabaseInterface
     */
    public function getDatabase($database);

    /**
     * Get an adapter for a "key" or "prop".
     *
     * An Identity Adapter is associated with a database value stored in a key
     * or prop value. If a $separator character is specified, the adapter
     * is enhanced with an ArrayAccess interface, and the value is stored
     * joining its elements with that $separator.
     *
     * @api
     * @see \Nethgui\Adapter\AdapterAggregationInterface
     * @see getMapAdapter()
     * @param string|ArrayAccess $database Database name or ArrayAccess object
     * @param string $key Key connected to the adapter.
     * @param string $prop Optional - Set to a prop name to connect a prop instead of a key.
     * @param string $separator Optional - Specify a single character string to obtain an ArrayAccess and \Countable interface.
     * @return \Nethgui\Adapter\AdapterInterface
     */
    public function getIdentityAdapter($database, $key, $prop = NULL, $separator = NULL);

    /**
     * Get a mapping Adapter.
     *
     * A Map Adapter maps database values through a "reader" and a "writer"
     * converter method. Values are specified through $args parameter.
     *
     * @api
     * @see getIdentityAdapter()
     * @see \Nethgui\Adapter\AdapterAggregationInterface
     * @param callback $readCallback If $args has N elements $readCallback must accept N parameters and return a value.
     * @param callback $writeCallback If $args has N elements $writeCallback must accept a parameter and return an array of N elements.
     * @param array $args An array of arrays in the form ($database, $key, $prop). $prop is optional.
     *
     * @return \Nethgui\Adapter\AdapterInterface
     */
    public function getMapAdapter($readCallback, $writeCallback, $args);

    /**
     * Get a table adapter
     * 
     * A table adapter has an array interface, where each element represents a row, and each row
     * is an array itself. 
     *
     * @api
     * @param string $database Database name
     * @param string $typeOrKey The type of the keys to read from database or the key value where the data is stored
     * @param string $filterOrProp The string to filter the table data or set to a prop name to connect a prop instead of a key.
     * @param array $separators An array of one or two separator strings. The first is for the rows, the second for the columns. Set to NULL if $typeOrKey is a TYPE!
     *
     * @return \Nethgui\Adapter\AdapterInterface An adapter with array and countable interfaces.
     */
    public function getTableAdapter($database, $typeOrKey, $filterOrProp = NULL, $separators = NULL);

    /**
     * Run an event handler
     *
     * The event can be signaled immediately or added to a queue.
     *
     * An event is represented by a ProcessInterface. If the event specification
     * terminates with an ampersand (&) the process is detached.
     *     
     * @api
     * @see runEvents()
     * @see exec()
     * @param string $event Event specification <eventName>[@<queueName>][ &]
     * @param array $arguments Optional event arguments
     * @return \Nethgui\System\ProcessInterface
     */
    public function signalEvent($event, $arguments = array());

    /**
     * Run events on the give queue
     *
     * @param string $queueName
     * @return void
     */
    public function runEvents($queueName);

    /**
     * PHP exec() wrapper
     *
     * The arguments are replaced in the command string, searching for placeholders
     * in the form ${1} .. ${N}.
     *
     * NOTE: Only placeholders corresponding to i+1 element in the
     * arguments array are replaced.
     *
     * @api
     * @param string $command
     * @param array $arguments Arguments for the command. Will be shell-escaped.
     * @param boolean $detached If set the command is run detached from the PHP system process and traced
     * @return \Nethgui\System\ProcessInterface
     */
    public function exec($command, $arguments = array(), $detached = FALSE);

    /**
     * Create a platform validator object
     *
     * @api
     * @param int ... One of the VALID_* constants
     * @return \Nethgui\System\ValidatorInterface
     */
    public function createValidator();

    /**
     * Get the system internal date format
     *
     * @return string
     */
    public function getDateFormat();

    /**
     * 
     * @return array An array of \Nethgui\System\ProcessInterface traced objects
     * @see \Nethgui\System\ProcessInterface
     * @deprecated since version 1.6
     */
    public function getDetachedProcesses();

    /**
     * @api
     * @param string $name The identifier of the process
     * @return \Nethgui\System\ProcessInterface
     * @deprecated since version 1.6
     */
    public function getDetachedProcess($identifier);
}
