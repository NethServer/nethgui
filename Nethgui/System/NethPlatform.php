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
 * Implementation of the platform interface for Nethesis products
 *
 */
class NethPlatform implements PlatformInterface, \Nethgui\Authorization\PolicyEnforcementPointInterface, \Nethgui\Log\LogConsumerInterface, \Nethgui\Core\GlobalFunctionConsumerInterface
{

    /**
     * Cache of configuration database objects
     * @var array
     */
    private $databases;

    /**
     * @var PolicyDecisionPointInterface;
     */
    private $policyDecisionPoint;

    /**
     * Keeps User object acting on host configuration.
     * @var \Nethgui\Client\UserInterface
     */
    private $user;
    private $eventQueue;

    /**
     * @var \Nethgui\Log\LogInterface
     */
    private $log;

    /**
     * @var \Nethgui\Core\GlobalFunctionWrapper
     */
    private $globalFunctionWrapper;

    /**
     * Traced processes
     * @var \ArrayObject
     */
    private $processes;

    /**
     * We must specify who acts on host configuration for authorization purposes
     * and to track the processes (s)he starts.
     *
     * @param \Nethgui\Client\UserInterface $user
     */
    public function __construct(\Nethgui\Client\UserInterface $user)
    {
        $this->user = $user;
        $this->eventQueue = array();
        $this->log = new \Nethgui\Log\Syslog();

        $session = $user->getSession();

        $key = get_class($this);

        // check for process session storage initialization:
        if ( ! $session->hasElement($key)) {
            $session->store($key, new \ArrayObject());
        }

        // TODO: scan the process list and remove long-exited processes.
        $this->processes = $session->retrieve($key);
    }

    /**
     *
     * @param string $database SME database configuration name
     * @return ConfigurationDatabase
     */
    public function getDatabase($database)
    {
        if ( ! isset($this->databases[$database])) {
            $object = new ConfigurationDatabase($database, $this->user);
            $object->setPolicyDecisionPoint($this->getPolicyDecisionPoint());
            $this->databases[$database] = $object;
        }

        return $this->databases[$database];
    }

    public function getIdentityAdapter($database, $key, $prop = NULL, $separator = NULL)
    {

        $serializer = $this->getSerializer($database, $key, $prop);

        if (is_null($separator)) {
            $adapter = new \Nethgui\Adapter\ScalarAdapter($serializer);
        } else {
            $adapter = new \Nethgui\Adapter\ArrayAdapter($separator, $serializer);
        }

        return $adapter;
    }

    public function getMapAdapter($readCallback, $writeCallback, $args)
    {

        // Create a Multiple adapter
        $serializers = array();

        foreach ($args as $serializerSpec) {
            $serializers[] = call_user_func_array(array($this, 'getSerializer'), $serializerSpec);
        }

        $adapter = new \Nethgui\Adapter\MultipleAdapter($readCallback, $writeCallback, $serializers);

        return $adapter;
    }

    public function getTableAdapter($database, $typeOrKey, $filterOrProp = NULL, $separators = NULL)
    {
        if (is_null($separators)) {
            return new \Nethgui\Adapter\TableAdapter($this->getDatabase($database), $typeOrKey, $filterOrProp);
        }

        $innerAdapter = $this->getIdentityAdapter($database, $typeOrKey, $filterOrProp, $separators[0]);

        return new \Nethgui\Adapter\TabularValueAdapter($innerAdapter, $separators[1]);
    }

    /**
     *
     * @param string $database
     * @param string $key
     * @param string $prop
     * @return \Nethgui\Serializer\SerializerInterface
     */
    private function getSerializer($database, $key, $prop = NULL)
    {
        if ($database instanceof \ArrayAccess) {
            $serializer = new \Nethgui\Serializer\ArrayAccessSerializer($database, $key, $prop);
        } elseif (is_string($database)) {
            if (is_null($prop)) {
                $serializer = new \Nethgui\Serializer\KeySerializer($this->getDatabase($database), $key);
            } else {
                $serializer = new \Nethgui\Serializer\PropSerializer($this->getDatabase($database), $key, $prop);
            }
        }

        return $serializer;
    }

    public function signalEvent($eventSpecification, $arguments = array())
    {
        $matches = array();

        preg_match('/(?P<event>[^@]+)(@(?P<queue>[^ ]+))?( +(?P<detached>&))?/', $eventSpecification, $matches);

        if ( ! isset($matches['event'])) {
            throw new \InvalidArgumentException(sprintf("%s: invalid event specification", get_class($this)), 1325578497);
        }

        $queue = isset($matches['queue']) ? $matches['queue'] : 'now';
        $detached = isset($matches['detached']) ? TRUE : FALSE;

        // prepend the event name to the argument list:
        array_unshift($arguments, $matches['event']);

        $command = $this
            ->createCommandObject('/usr/bin/sudo /sbin/e-smith/signal-event ${@}', $arguments, $detached)
            ->setIdentifier(uniqid($matches['event'] . '-'))
        ;

        if ($queue === 'now') {
            $command->exec();
        } else {
            if ( ! isset($this->eventQueue[$queue])) {
                $this->eventQueue[$queue] = array();
            }

            $this->eventQueue[$queue][] = $command;
        }

        return $command;
    }

    /**
     * Run enqueued events
     * 
     * @return void
     */
    public function runEvents($queueName)
    {
        if ( ! isset($this->eventQueue[$queueName])) {
            return;
        }

        foreach ($this->eventQueue[$queueName] as $process) {
            if ( ! $process instanceof ProcessInterface) {
                continue;
            }

            $process->exec();

            if ($process->readExecutionState() === ProcessInterface::STATE_EXITED
                && $process->getExitStatus() !== 0) {
                $this->getLog()->error(sprintf("%s: process `%s` on queue `%s` exited with code %d. Output: `%s`", get_class($this), $process->getIdentifier(), $queueName, $process->getExitStatus(), implode(' ', $process->getOutputArray())));
            }
        }
    }

    public function getPolicyDecisionPoint()
    {
        return $this->policyDecisionPoint;
    }

    public function setPolicyDecisionPoint(\Nethgui\Authorization\PolicyDecisionPointInterface $pdp)
    {
        $this->policyDecisionPoint = $pdp;
    }

    public function exec($command, $arguments = array(), $detached = FALSE)
    {
        return $this->createCommandObject($command, $arguments, $detached)->exec();
    }

    /**
     *
     * @param type $command
     * @param type $arguments
     * @param type $detached
     * @return ProcessInterface
     */
    private function createCommandObject($command, $arguments, $detached)
    {
        if ($detached === TRUE) {
            $commandObject = new ProcessDetached($command, $arguments);
            $this->traceProcess($commandObject);
        } else {
            $commandObject = new Process($command, $arguments);
        }

        if (isset($this->globalFunctionWrapper)) {
            $commandObject->setGlobalFunctionWrapper($this->globalFunctionWrapper);
        }

        return $commandObject;
    }

    public function getLog()
    {
        return $this->log;
    }

    public function setLog(\Nethgui\Log\LogInterface $log)
    {
        $this->log = $log;
    }

    public function createValidator()
    {
        return new Validator($this);
    }

    public function getDateFormat()
    {
        return 'YYYY-mm-dd';
    }

    public function setGlobalFunctionWrapper(\Nethgui\Core\GlobalFunctionWrapper $object)
    {
        $this->globalFunctionWrapper = $object;
    }

    private function traceProcess(\Nethgui\System\ProcessInterface $process)
    {
        $this->processes[] = $process;
        return $this;
    }

    public function getDetachedProcesses()
    {
        return $this->processes->getArrayCopy();
    }

    public function getDetachedProcess($identifier)
    {
        // scan the process list 
        foreach ($this->processes as $process) {
            if ($process->getIdentifier() === $identifier) {
                return $process;
            }
        }
        return FALSE;
    }

}
