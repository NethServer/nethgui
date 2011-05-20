<?php
/**
 * @package Core
 * @author Davide Principi <davide.principi@nethesis.it>
 */

/**
 * TODO describe this class
 * @package Core
 */
class NethGui_Core_HostConfiguration implements NethGui_Core_HostConfigurationInterface, NethGui_Authorization_PolicyEnforcementPointInterface
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
     * @var NethGui_Core_UserInterface
     */
    private $user;
    
    private $asyncEvents;

    /**
     * We must specify who acts on host configuration.
     * @param NethGui_Core_UserInterface $user
     */
    public function __construct(NethGui_Core_UserInterface $user)
    {
        $this->user = $user;
        $this->asyncEvents = array();
    }

    /**
     *
     * @param string $database SME database configuration name
     * @return NethGui_Core_ConfigurationDatabase
     */
    public function getDatabase($database)
    {
        if ( ! isset($this->databases[$database])) {
            $object = new NethGui_Core_ConfigurationDatabase($database, $this->user);
            $object->setPolicyDecisionPoint($this->getPolicyDecisionPoint());
            $this->databases[$database] = $object;
        }

        return $this->databases[$database];
    }

    public function getIdentityAdapter($database, $key, $prop = NULL, $separator = NULL)
    {
        $db = $this->getDatabase($database);

        if (is_string($key)) {
            $serializer = $this->getSerializer($database, $key, $prop);

            if (is_null($separator)) {
                $adapter = new NethGui_Adapter_ScalarAdapter($serializer);
            } else {
                $adapter = new NethGui_Adapter_ArrayAdapter($separator, $serializer);
            }
        } elseif (is_callback($key)) {
            // TODO
            throw new Exception('Not implemented');
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

        $adapter = new NethGui_Adapter_MultipleAdapter($readCallback, $writeCallback, $serializers);

        return $adapter;
    }
    
    public function getTableAdapter($database, $typeOrKey, $filterOrProp = NULL, $separators = NULL)
    {
        if(is_null($separators)) {
            return new NethGui_Adapter_TableAdapter($this->getDatabase($database), $typeOrKey);
        }
        
        $innerAdapter = $this->getIdentityAdapter($database, $typeOrKey, $filterOrProp, $separators[0]);
        
        return new NethGui_Adapter_TabularValueAdapter($innerAdapter, $separators[1]);
    }

    /**
     *
     * @param string $database
     * @param string $key
     * @param string $prop
     * @return NethGui_Serializer_SerializerInterface
     */
    private function getSerializer($database, $key, $prop = NULL)
    {
        if (is_null($prop)) {
            $serializer = new NethGui_Serializer_KeySerializer($this->getDatabase($database), $key);
        } else {
            $serializer = new NethGui_Serializer_PropSerializer($this->getDatabase($database), $key, $prop);
        }

        return $serializer;
    }

    /**
     * Signal an event and return the status
     *
     * TODO: authorize user action on PDP.
     *
     * @param string $event Event name
     * @param array &$output Optional output array. If the output argument is present, then the specified array will be filled with every line of output from the event.
     * @access public
     * @return boolean true on success, false otherwise
     */
    public function signalEvent($event, &$output=array())
    {
        exec('/usr/bin/sudo /sbin/e-smith/signal-event' . ' ' . escapeshellarg($event), $output, $ret);
        return ($ret == 0);
    }

    public function signalEventAsync($event, $callback = NULL)
    {
        if ( ! isset($this->asyncEvents[$event])) {
            $this->asyncEvents[$event] = array();
        }
        if (is_callable($callback)) {
            $this->asyncEvents[$event][] = $callback;
        }
    }
    
    /**
     * Raises all asynchronous events, invoking the given callback functions.
     * @return boolean|NULL
     */
    public function raiseAsyncEvents() {
        if(empty($this->asyncEvents)) {
            return NULL;
        }
        
        $eventNames = array_keys($this->asyncEvents);
        foreach($eventNames as $eventName) {
            $exitStatus = $this->signalEvent($eventName);
            
            $callbacks = $this->asyncEvents[$eventName];
            foreach($callbacks as $callback) {
                call_user_func($callback, $exitStatus);
            }
            
            if($exitStatus === FALSE) {
                return FALSE;
            }
        }
        return TRUE;
    }

    public function getPolicyDecisionPoint()
    {
        return $this->policyDecisionPoint;
    }

    public function setPolicyDecisionPoint(NethGui_Authorization_PolicyDecisionPointInterface $pdp)
    {
        $this->policyDecisionPoint = $pdp;
    }

}