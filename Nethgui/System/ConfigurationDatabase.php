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

use Nethgui\Exception\AuthorizationException;

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
 * @author Giacomo Sanchietti <giacomo.sanchietti@nethesis.it>
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 * @internal
 */
class ConfigurationDatabase implements \Nethgui\System\DatabaseInterface, \Nethgui\Authorization\PolicyEnforcementPointInterface, \Nethgui\Core\GlobalFunctionConsumerInterface
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
            throw new \InvalidArgumentException(sprintf("%s: You must provide a valid database name.", get_class($this)), 1322148910);

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

    public function getAll($type = NULL, $filter = NULL)
    {
        if ( ! is_null($filter)) {
            throw new \InvalidArgumentException(sprintf('%s: $filter argument must be NULL!', get_class($this)), 1322149165);
        }

        if ( ! $this->canRead) {
            throw new AuthorizationException(sprintf("%s: Permission Denied", get_class($this)), 1322149164);
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

    public function getKey($key)
    {
        if ( ! $this->canRead) {
            throw new AuthorizationException(sprintf("%s: Permission Denied", get_class($this)), 1322149166);
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

    public function setKey($key, $type, $props)
    {
        if ( ! $this->canWrite) {
            throw new AuthorizationException(sprintf("%s: Permission Denied", get_class($this)), 1322149167);
        }

        $output = NULL;
        $ret = $this->dbExec('set', $this->prepareArguments($key, $type, $props), $output);
        return ($ret == 0);
    }

    public function deleteKey($key)
    {
        if ( ! $this->canWrite) {
            throw new AuthorizationException(sprintf("%s: Permission Denied", get_class($this)), 1322149168);
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
            throw new AuthorizationException(sprintf("%s: Permission Denied", get_class($this)), 1322149169);
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
            throw new AuthorizationException(sprintf("%s: Permission Denied", get_class($this)), 1322149193);
        }

        $output = NULL;
        $ret = $this->dbExec('settype', $this->prepareArguments($key, $type), $output);
        return ($ret == 0);
    }

    public function getProp($key, $prop)
    {
        if ( ! $this->canRead) {
            throw new AuthorizationException(sprintf("%s: Permission Denied", get_class($this)), 1322149194);
        }

        $output = NULL;
        $ret = $this->dbExec('getprop', $this->prepareArguments($key, $prop), $output);
        return trim($output);
    }

    public function setProp($key, $props)
    {
        if ( ! $this->canWrite) {
            throw new AuthorizationException(sprintf("%s: Permission Denied", get_class($this)), 1322149191);
        }

        $output = NULL;
        $ret = $this->dbExec('setprop', $this->prepareArguments($key, $props), $output);
        return ($ret == 0);
    }

    public function delProp($key, $props)
    {
        if ( ! $this->canWrite) {
            throw new AuthorizationException(sprintf("%s: Permission Denied", get_class($this)), 1322149192);
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
        return $p->getExitCode();
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
