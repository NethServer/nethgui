<?php
/**
 * @package NethGuiFramework
 * @author Davide Principi <davide.principi@nethesis.it>
 */

/**
 * TODO describe this class
 * @package NethGuiFramework
 * @subpackage Core
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

    /**
     * We must specify who acts on host configuration.
     * @param NethGui_Core_UserInterface $user
     */
    public function __construct(NethGui_Core_UserInterface $user)
    {
        $this->user = $user;
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


    /**
     * @return NethGui_Core_AdapterInterface
     */
    public function getAdapter($database, $key, $prop = NULL, $separator = NULL)
    {
        $db = $this->getDatabase($database);

        if (is_null($prop)) {
            $serializer = new NethGui_Core_KeySerializer($db, $key);
        } else {
            $serializer = new NethGui_Core_PropSerializer($db, $key, $prop);
        }

        if(is_null($separator)) {
            $adapter = new NethGui_Core_ScalarAdapter($serializer);
        } else {
            $adapter = new NethGui_Core_ArrayAdapter($separator, $serializer);
        }

        return $adapter;
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

    public function getPolicyDecisionPoint()
    {
        return $this->policyDecisionPoint;
    }

    public function setPolicyDecisionPoint(NethGui_Authorization_PolicyDecisionPointInterface $pdp)
    {
        $this->policyDecisionPoint = $pdp;
    }

}