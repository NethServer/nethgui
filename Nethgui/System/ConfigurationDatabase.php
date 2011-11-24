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
 * Read and write parameters into SME DB
 *
 * Ths class implements an interface to SME database executing the command /sbin/e-smith/db with sudo.
 * The class needs /etc/sudoers configurazione. In the sudoers file you must have something like this:
 * <code>
 * Cmnd_Alias SME = /sbin/e-smith/db, /sbin/e-smith/signal-event
 * www ALL=NOPASSWD: SME
 * </code>
 *
 * Before use any method in the class, the method st($db) must be called. 
 *
 * 
 * 
 */
class ConfigurationDatabase implements \Nethgui\Authorization\PolicyEnforcementPointInterface, \Nethgui\Core\GlobalFunctionConsumer
{

    /**
     *
     * @var \Nethgui\Core\GlobalFunctionWrapper
     */
    private $globalFunctionWrapper;

    /**
     * @var PolicyDecisionPointInterface;
     */
    private $policyDecisionPoint;

    /**
     * @var SME DB database command
     * */
    private $command = "/usr/bin/sudo /sbin/e-smith/db";

    /**
     * @var $db Database name, it's translated into the db file path. For example: /home/e-smith/db/testdb
     * */
    private $db;

    /**
     * @var $canRead Read flag permission, it's true if the current user can read the database, false otherwise
     * */
    private $canRead = FALSE;

    /**
     * @var $canWrite Write flag permission, it's true if the current user can write the database, false otherwise
     * */
    private $canWrite = FALSE;

    /**
     * Keeps User object acting on this database. 
     * @var \Nethgui\Client\UserInterface
     */
    private $user;

    /**
     * setPolicyDecisionPoint 
     * 
     * @param \Nethgui\Authorization\PolicyDecisionPointInterface $pdp 
     * @access public
     * @return void
     */
    public function setPolicyDecisionPoint(\Nethgui\Authorization\PolicyDecisionPointInterface $pdp)
    {
        $this->policyDecisionPoint = $pdp;
        $this->authorizeDbAccess();
    }

    /**
     * Return current getPolicyDecisionPoint 
     * 
     * @access public
     * @return policyDecisionPoint
     */
    public function getPolicyDecisionPoint()
    {
        return $this->policyDecisionPoint;
    }

    /**
     * Construct an object to access a SME Configuration database file
     * with $user's privileges.
     * 
     * @param string $database Database name
     */
    public function __construct($database, \Nethgui\Client\UserInterface $user)
    {
        if ( ! $database)
            throw new Exception("You must provide a valid database name.");

        $this->db = $database;
        $this->user = $user;
    }

    private function authorizeDbAccess()
    {
        $requestRead = new \Nethgui\Authorization\AccessControlRequest($this->user, $this->db, 'READ');
        $responseRead = $this->policyDecisionPoint->authorizeRequest($requestRead);
        if ($responseRead) {
            $this->canRead = TRUE;
        }

        $requestWrite = new \Nethgui\Authorization\AccessControlRequest($this->user, $this->db, 'WRITE');
        $responseWrite = $this->policyDecisionPoint->authorizeRequest($requestWrite);
        if ($responseWrite) {
            $this->canWrite = TRUE;
        }
    }

    /**
     * Retrieve all keys from the database. If needed, you can use filter the results by type and key name. 
     *
     * @param string $type (optional) type of the key
     * @param string $filter (optional) case insensitive fulltext search on key value
     * @access public
     * @return array associative array in the form "[KeyName] => array( [type] => [TypeValue], [PropName1] => [PropValue1], [PropName2] => [PropValue2], ...) 
     */
    public function getAll($type = NULL, $filter = NULL)
    {
        if ( ! $this->canRead) {
            throw new Exception("Permission Denied");
        }

        if ( ! is_null($filter)) {
            throw new InvalidArgumentException(sprintf('%s: $filter argument must be NULL!', get_class($this)));
        }

        $output = NULL;
        $ret = $this->dbExec('print', array(), $output);

        if (empty($output)) {
            return array();
        }

        $result = array();

        foreach (explode("\n", $output) as $line) {
            $line = trim($line);
            if ($line) {
                $tokens = explode("=", $line);
                $key = $tokens[0];
                $tokens = explode("|", $tokens[1]);
                if ( ! is_null($type) && $tokens[0] != $type)
                    continue;

                $result[$key]['type'] = $tokens[0];
                for ($i = 1; $i <= count($tokens); $i ++ ) { //skip type
                    if (isset($tokens[$i])) //avoid outbound tokens
                        $result[$key][trim($tokens[$i])] = trim($tokens[ ++ $i]);
                }
            }
        }

        return $result;
    }

    /**
     * Retrieve a key from the database. 
     * Act like : /sbin/e-smith/db dbfile get key
     *
     * @param string $key the key to read
     * @access public
     * @return array associative array in the form [PropName] => [PropValue]
     */
    public function getKey($key)
    {
        if ( ! $this->canRead) {
            throw new Exception("Permission Denied");
        }

        $result = array();
        $output = NULL;

        $ret = $this->dbExec('get', $this->prepareArguments($key), $output);

        if ($output != "") {
            $tokens = explode("|", $output);
            for ($i = 1; $i <= count($tokens); $i ++ ) { //skip type
                if (isset($tokens[$i])) //avoid outbound tokens
                    $result[trim($tokens[$i])] = trim($tokens[ ++ $i]);
            }
        }
        return $result;
    }

    /**
     * Set a database key with type and properties.
     * Act like: /sbin/e-smith/db dbfile set key type [prop1 val1] [prop2 val2] ... 
     * 
     * @param string $key Key to write
     * @param string $type Type of the key
     * @param string $props Array of properties in the form [PropName] => [PropValue]
     * @access public
     * @return bool TRUE on success, FALSE otherwise
     *
     */
    public function setKey($key, $type, $props)
    {
        if ( ! $this->canWrite) {
            throw new Exception("Permission Denied");
        }

        $output = NULL;
        $ret = $this->dbExec('set', $this->prepareArguments($key, $type, $props), $output);
        return ($ret == 0);
    }

    /**
     * Delete a key and all its properties 
     * Act like: /sbin/e-smith/db dbfile delete key
     * 
     * @param mixed $key 
     * @access public
     * @return void
     */
    public function deleteKey($key)
    {
        if ( ! $this->canWrite) {
            throw new Exception("Permission Denied");
        }

        $output = NULL;
        $output = NULL;
        $ret = $this->dbExec('delete', $this->prepareArguments($key), $output);
        return ($ret == 0);
    }

    /**
     * Return the type of a key
     * Act like: /sbin/e-smith/db dbfile gettype key
     * 
     * @param string $key the key to retrieve
     * @access public
     * @return string the type of the key
     */
    public function getType($key)
    {
        if ( ! $this->canRead) {
            throw new Exception("Permission Denied");
        }

        $output = NULL;
        $ret = $this->dbExec('gettype', $this->prepareArguments($key), $output);
        return trim($output);
    }

    /**
     * Set the type of a key 
     * Act like: /sbin/e-smith/db dbfile settype key type
     * 
     * @param string $key the key to change
     * @param string $type the new type
     * @access public
     * @return bool true on success, FALSE otherwise
     */
    public function setType($key, $type)
    {
        if ( ! $this->canWrite) {
            throw new Exception("Permission Denied");
        }

        $output = NULL;
        $ret = $this->dbExec('settype', $this->prepareArguments($key, $type), $output);
        return ($ret == 0);
    }

    /**
     * Read the value of the given property
     * Act like: /sbin/e-smith/db dbfile getprop key prop
     * 
     * @param string $key the parent property key
     * @param string $prop the name of the property
     * @access public
     * @return string the value of the property
     */
    public function getProp($key, $prop)
    {
        if ( ! $this->canRead) {
            throw new Exception("Permission Denied");
        }

        $output = NULL;
        $ret = $this->dbExec('getprop', $this->prepareArguments($key, $prop), $output);
        return trim($output);
    }

    /**
     * Set one or more properties under the given key
     * Act like: /sbin/e-smith/db dbfile setprop key prop1 val1 [prop2 val2] [prop3 val3] ...
     * 
     * @param string $key the property parent key
     * @param array $props an associative array in the form [PropName] => [PropValue]  
     * @access public
     * @return bool TRUE on success, FALSE otherwise
     */
    public function setProp($key, $props)
    {
        if ( ! $this->canWrite) {
            throw new Exception("Permission Denied");
        }

        $output = NULL;
        $ret = $this->dbExec('setprop', $this->prepareArguments($key, $props), $output);
        return ($ret == 0);
    }

    /**
     * Delete one or more properties under the given key 
     * Act like: sbin/e-smith/db dbfile delprop key prop1 [prop2] [prop3] ...
     * 
     * @param string $key the property parent key
     * @param array $props a simple array containg the properties to be deleted
     * @access public
     * @return bool TRUE on success, FALSE otherwise
     */
    public function delProp($key, $props)
    {
        if ( ! $this->canWrite) {
            throw new Exception("Permission Denied");
        }

        $output = NULL;
        $ret = $this->dbExec('delprop', array_merge(array($key), array_values($props)), $output);
        return ($ret == 0);
    }

    public function setGlobalFunctionWrapper(\Nethgui\Core\GlobalFunctionWrapper $object)
    {
        $this->globalFunctionWrapper = $object;
    }

    private function dbExec($command, $args, &$output)
    {
        // prepend the database name and command
        array_unshift($args, $this->db, $command);
        $p = new Process($this->command . ' ${@}', $args);
        if (isset($this->globalFunctionWrapper)) {
            $p->setGlobalFunctionWrapper($this->globalFunctionWrapper);
        }
        $p->exec();
        $output = $p->getOutput();
        return $p->getExitStatus();
    }

    /**
     * Take arbitrary arguments and flattenize to an array
     *
     * @param mixed $_
     * @return array
     */
    private function prepareArguments()
    {
        $args = array();

        foreach (func_get_args() as $arg) {
            if (is_array($arg)) {
                foreach ($arg as $propName => $propValue) {
                    $args[] = $propName;
                    $args[] = $propValue;
                }
            } else {
                $args[] = (String) $arg;
            }
        }

        return $args;
    }

}
