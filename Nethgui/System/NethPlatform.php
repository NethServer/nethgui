<?php
/**
 * @package System
 * @author Davide Principi <davide.principi@nethesis.it>
 */

namespace Nethgui\System;

/**
 * Implementation of the platform interface for Nethesis products
 *
 * @package System
 */
class NethPlatform implements PlatformInterface, \Nethgui\Authorization\PolicyEnforcementPointInterface, \Nethgui\Log\LogConsumerInterface, \Nethgui\Core\GlobalFunctionConsumer
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
     * @var \Nethgui\Log\AbstractLog
     */
    private $log;

    /**
     * @var \Nethgui\Core\GlobalFunctionWrapper
     */
    private $globalFunctionWrapper;

    /**
     * We must specify who acts on host configuration.
     * @param \Nethgui\Client\UserInterface $user
     */
    public function __construct(\Nethgui\Client\UserInterface $user)
    {
        $this->user = $user;
        $this->eventQueue = array();
        $this->log = new \Nethgui\Log\Syslog();
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

    /**
     * Signal an event and return the status
     *
     * TODO: authorize user action on PDP.
     *
     * @param string $event Event name
     * @param array $arguments Optional arguments array.
     * @return ProcessInterface
     */
    public function signalEvent($event, $arguments = array())
    {
        array_unshift($arguments, $event);        
        return $this->exec('/usr/bin/sudo /sbin/e-smith/signal-event ${@}', $arguments);
    }

    public function signalEventFinally($event, $argv = array(), $observer = NULL)
    {
        // Ensure that each event is called one time with the same set of arguments
        $eventId = $this->calcEventId($event, $argv);

        if ( ! isset($this->eventQueue[$eventId])) {
            $this->eventQueue[$eventId] = array(
                'name' => $event,
                'args' => $argv,
                'objs' => array(),
            );
        }

        if ($observer instanceof EventObserverInterface) {
            $this->eventQueue[$eventId]['objs'][] = $observer;
        }
    }

    /**
     * Translates a signal call arguments to a unique string identifier.
     * 
     * @param string $eventName
     * @param array $args
     * @return string 
     */
    private function calcEventId($eventName, $args)
    {
        $idList = array($eventName);

        foreach ($args as $arg) {
            if (is_callable($arg)) {
                $idList[] = is_object($arg[0]) ? get_class($arg[0]) : (String) $arg[0];
                $idList[] = (String) $arg[1];
            } else {
                $idList[] = (String) $arg;
            }
        }

        return md5(implode('-', $idList));
    }

    /**
     * Raises all asynchronous events, invoking the given callback functions.
     * @return boolean|NULL
     */
    public function signalFinalEvents()
    {
        if (empty($this->eventQueue)) {
            return NULL;
        }
        foreach ($this->eventQueue as $eventData) {
            $args = array();

            foreach ($eventData['args'] as $arg) {
                if (is_callable($arg)) {
                    // invoke argument value provider:
                    $arg = call_user_func($arg, $eventData['name']);
                }
                if ($arg === NULL) {
                    continue; // skip NULL values
                }
                $args[] = (String) $arg;
            }
            $exitInfo = $this->signalEvent($eventData['name'], $args);
            foreach ($eventData['objs'] as $observer) {
                if ($observer instanceof EventObserverInterface) {
                    $observer->notifyEventCompletion($eventData['name'], $args, $exitInfo->getExitStatus() === 0, $exitInfo->getOutput());
                }
            }
            if ($exitInfo->getExitStatus() !== 0) {
                return FALSE;
            }
        }
        return TRUE;
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
        if ($detached) {
            $commandObject = new ProcessDetached($command, $arguments);
        } else {
            $commandObject = new Process($command, $arguments);
        }

        if (isset($this->globalFunctionWrapper)) {
            $commandObject->setGlobalFunctionWrapper($this->globalFunctionWrapper);
        }

        $commandObject->exec();
        return $commandObject;
    }

    public function getLog()
    {
        return $this->log;
    }

    public function setLog(\Nethgui\Log\AbstractLog $log)
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
}
