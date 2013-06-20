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
class NethPlatform implements PlatformInterface, \Nethgui\Authorization\PolicyEnforcementPointInterface, \Nethgui\Log\LogConsumerInterface, \Nethgui\Utility\PhpConsumerInterface, \Nethgui\Utility\SessionConsumerInterface
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
     * @var \Nethgui\Authorization\UserInterface
     */
    private $user;

    /**
     *
     * @var array
     */
    private $eventQueue;

    /**
     * @var \Nethgui\Log\LogInterface
     */
    private $log;

    /**
     * @var \Nethgui\Utility\PhpWrapper
     */
    private $phpWrapper;

    /**
     * Traced processes
     * @var \ArrayObject
     */
    private $processes;

    public function __construct(\Nethgui\Authorization\UserInterface $user)
    {
        $this->eventQueue = array();
        $this->processes = new \Nethgui\Utility\ArrayDisposable();
        $this->user = $user;
    }

    public function setSession(\Nethgui\Utility\SessionInterface $session)
    {
        $key = get_class($this);

        $s = $session->retrieve($key);

        if ($s instanceof $this->processes) {
            if (count($this->processes) > 0) {
                throw new \UnexpectedValueException(sprintf('%s: some processes are still traced, cannot set the session.', __CLASS__), 1327406381);
            }
            $this->processes = $s;
        } else {
            $session->store($key, $this->processes);
        }
        return $this;
    }

    /**
     *
     * @param string $database SME database configuration name
     * @return EsmithDatabase
     */
    public function getDatabase($database)
    {
        if ( ! isset($this->databases[$database])) {
            $object = new EsmithDatabase($database, $this->user);
            $object->setPolicyDecisionPoint($this->policyDecisionPoint);
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
     * @param mixed  $source
     * @param string $key
     * @param string $prop
     * @return \Nethgui\Serializer\SerializerInterface
     */
    private function getSerializer($source, $key, $prop = NULL)
    {
        if ($source instanceof \ArrayAccess) {
            $serializer = new \Nethgui\Serializer\ArrayAccessSerializer($source, $key, $prop);
        } elseif (is_string($source)) {
            if (is_null($prop)) {
                $serializer = new \Nethgui\Serializer\KeySerializer($this->getDatabase($source), $key);
            } else {
                $serializer = new \Nethgui\Serializer\PropSerializer($this->getDatabase($source), $key, $prop);
            }
        } else {
            throw new \InvalidArgumentException(sprintf('%s: cannot create a SerializerInterface instance', __CLASS__), 1336467547);
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
                && $process->getExitCode() !== 0) {
                $this->getLog()->error(sprintf("%s: process `%s` on queue `%s` exited with code %d. Output: `%s`", get_class($this), $process->getIdentifier(), $queueName, $process->getExitCode(), implode(' ', $process->getOutputArray())));
            }
        }
    }

    public function setPolicyDecisionPoint(\Nethgui\Authorization\PolicyDecisionPointInterface $pdp)
    {
        $this->policyDecisionPoint = $pdp;
        return $this;
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

        if (isset($this->phpWrapper)) {
            $commandObject->setPhpWrapper($this->phpWrapper);
        }

        return $commandObject;
    }

    public function getLog()
    {
        if ( ! isset($this->log)) {
            $this->log = new \Nethgui\Log\Nullog();
        }
        return $this->log;
    }

    public function setLog(\Nethgui\Log\LogInterface $log)
    {
        $this->log = $log;
        return $this;
    }

    public function getDateFormat()
    {
        return 'YYYY-mm-dd';
    }

    public function setPhpWrapper(\Nethgui\Utility\PhpWrapper $object)
    {
        $this->phpWrapper = $object;
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
        $returnProcess = FALSE;

        foreach ($this->processes as $process) {
            if ($process->getIdentifier() === $identifier) {
                $returnProcess = $process;
            }
        }

        return $returnProcess;
    }

    /**
     * Creates a Validator object that checks against each of the function arguemnts
     *
     * @param integer ...
     * @return Validator
     */
    public function createValidator()
    {
        $validator = new Validator($this);

        foreach (func_get_args() as $ruleCode) {
            switch ($ruleCode) {
                case self::ANYTHING:
                    $validator->forceResult(TRUE);
                    break;

                case self::ANYTHING_COLLECTION:
                    $validator->orValidator($this->createValidator()->isEmpty(), $this->createValidator()->collectionValidator($this->createValidator()->forceResult(TRUE)));
                    break;

                case self::USERNAME_COLLECTION:
                    $validator->orValidator($this->createValidator()->isEmpty(), $this->createValidator()->collectionValidator($this->createValidator()->username()));
                    break;

                case self::SERVICESTATUS:
                    $validator->memberOf('enabled', 'disabled');
                    break;

                case self::USERNAME:
                    $validator->username();
                    break;

                case self::HOSTNAME:
                    $validator->hostname();
                    break;
                
                case self::HOSTNAME_FQDN:
                    $validator->hostname(1);
                    break;
                
                case self::HOSTNAME_SIMPLE:
                    $validator->hostname(0, 0);
                    break;

                case self::HOSTADDRESS:
                    $validator->orValidator($this->createValidator()->ipV4Address(), $this->createValidator()->hostname());
                    break;

                case self::NOTEMPTY:
                    $validator->notEmpty();
                    break;

                case self::DATE:
                    $validator->date();
                    break;

                case self::TIME:
                    $validator->time();
                    break;

                case self::IP:
                case self::IPv4:
                    $validator->ipV4Address();
                    break;

                case self::NETMASK:
                case self::IPv4_NETMASK:
                    $validator->ipV4Netmask();
                    break;

                case self::MACADDRESS:
                    $validator->macAddress();
                    break;

                case self::POSITIVE_INTEGER:
                    $validator->integer()->positive();
                    break;

                case self::NONNEGATIVE_INTEGER:
                    $validator->integer()->greatThan(-1);
                    break;

                case self::PORTNUMBER:
                    $validator->integer()->greatThan(0)->lessThan(65535);
                    break;

                case self::BOOLEAN:
                    $validator->memberOf('1', 'yes', '0', '');
                    break;

                case self::IP_OR_EMPTY:
                case self::IPv4_OR_EMPTY:
                    $validator->orValidator($this->createValidator()->ipV4Address(), $this->createValidator()->isEmpty());
                    break;

                case self::NETMASK_OR_EMPTY:
                case self::IPv4_NETMASK_OR_EMPTY:
                    $validator->orValidator($this->createValidator()->ipV4Netmask(), $this->createValidator()->isEmpty());
                    break;

                case self::YES_NO:
                    $validator->memberOf('yes', 'no');
                    break;

                case self::EMAIL:
                    $validator->email();
                    break;

                case self::EMPTYSTRING:
                    $validator->maxLength(0);
                    break;
                
                case NULL:
                    continue;

                default:
                    throw new \InvalidArgumentException(sprintf('%s: Unknown validator code: %s', get_class($this), $ruleCode), 1326380984);
            }
        }
        return $validator;
    }

}

