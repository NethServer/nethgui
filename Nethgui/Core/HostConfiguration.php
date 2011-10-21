<?php
/**
 * @package Core
 * @author Davide Principi <davide.principi@nethesis.it>
 */

/**
 * TODO describe this class
 * @package Core
 */
class Nethgui_Core_HostConfiguration implements Nethgui_Core_HostConfigurationInterface, Nethgui_Authorization_PolicyEnforcementPointInterface
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
     * @var Nethgui_Core_UserInterface
     */
    private $user;
    private $eventQueue;

    /**
     * We must specify who acts on host configuration.
     * @param Nethgui_Core_UserInterface $user
     */
    public function __construct(Nethgui_Core_UserInterface $user)
    {
        $this->user = $user;
        $this->eventQueue = array();
    }

    /**
     *
     * @param string $database SME database configuration name
     * @return Nethgui_Core_ConfigurationDatabase
     */
    public function getDatabase($database)
    {
        if ( ! isset($this->databases[$database])) {
            $object = new Nethgui_Core_ConfigurationDatabase($database, $this->user);
            $object->setPolicyDecisionPoint($this->getPolicyDecisionPoint());
            $this->databases[$database] = $object;
        }

        return $this->databases[$database];
    }

    public function getIdentityAdapter($database, $key, $prop = NULL, $separator = NULL)
    {

        $serializer = $this->getSerializer($database, $key, $prop);

        if (is_null($separator)) {
            $adapter = new Nethgui_Adapter_ScalarAdapter($serializer);
        } else {
            $adapter = new Nethgui_Adapter_ArrayAdapter($separator, $serializer);
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

        $adapter = new Nethgui_Adapter_MultipleAdapter($readCallback, $writeCallback, $serializers);

        return $adapter;
    }

    public function getTableAdapter($database, $typeOrKey, $filterOrProp = NULL, $separators = NULL)
    {
        if (is_null($separators)) {
            return new Nethgui_Adapter_TableAdapter($this->getDatabase($database), $typeOrKey, $filterOrProp);
        }

        $innerAdapter = $this->getIdentityAdapter($database, $typeOrKey, $filterOrProp, $separators[0]);

        return new Nethgui_Adapter_TabularValueAdapter($innerAdapter, $separators[1]);
    }

    /**
     *
     * @param string $database
     * @param string $key
     * @param string $prop
     * @return Nethgui_Serializer_SerializerInterface
     */
    private function getSerializer($database, $key, $prop = NULL)
    {
        if ($database instanceof ArrayAccess) {
            $serializer = new Nethgui_Serializer_ArrayAccessSerializer($database, $key, $prop);
        } elseif (is_string($database)) {
            if (is_null($prop)) {
                $serializer = new Nethgui_Serializer_KeySerializer($this->getDatabase($database), $key);
            } else {
                $serializer = new Nethgui_Serializer_PropSerializer($this->getDatabase($database), $key, $prop);
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
     * @return Nethgui_System_ProcessInterface
     */
    public function signalEvent($event, $arguments = array())
    {
        array_unshift($arguments, $event);
        $command = '/usr/bin/sudo /sbin/e-smith/signal-event ' . implode(' ', array_map('escapeshellarg', $arguments));
        return $this->exec($command);
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

        if ($observer instanceof Nethgui_Core_EventObserverInterface) {
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
                if ($observer instanceof Nethgui_Core_EventObserverInterface) {
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

    public function setPolicyDecisionPoint(Nethgui_Authorization_PolicyDecisionPointInterface $pdp)
    {
        $this->policyDecisionPoint = $pdp;
    }

    public function exec($command, $arguments = array())
    {
        $commandObject = new Nethgui_System_Process($command, $arguments);
        $commandObject->exec();
        return $commandObject;
    }

    public function trace(Nethgui_Core_UserInterface $user, $command, $arguments = array())
    {
        $commandObject = new Nethgui_System_ProcessDetached($command);
        $commandObject->exec();
        return $commandObject;
    }
}
