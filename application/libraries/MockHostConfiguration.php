<?php

final class MockHostConfiguration implements HostConfigurationInterface, PolicyEnforcementPointInterface {

    /**
     * @var PolicyDecisionPointInterface;
     */
    private $policyDecisionPoint;
    private $esmith = array();

    /**
     *
     * @var UserInterface
     */
    private $user;

    public function __construct()
    {
        $this->esmith['configuration'] = array();
        $this->esmith['configuration']['validFromNetworkMask'] = '255.255.255.0';
        $this->esmith['configuration']['validFromNetworkAddress'] = '192.168.1.0';
    }

    public function commit()
    {
        return TRUE;
    }

    public function read($resourcePath)
    {
        $request = new AccessControlRequest($this->user, implode('/', $resourcePath), 'READ');

        $response = $this->policyDecisionPoint->authorizeRequest($request);

        if(! $response )
        {
            throw new Exception($response->getMessage());
        }

        $db = $resourcePath[0];
        $key = $resourcePath[1];
        $property = isset($resourcePath[2]) ? $resourcePath[2] : NULL;
      
        if (isset($property))
        {
            return $this->esmith[$db][$key][$property];
        }

        return $this->esmith[$db][$key];
    }

    public function write($resourcePath, $value)
    {
        
    }

    public function setPolicyDecisionPoint(PolicyDecisionPointInterface $pdp)
    {
        $this->policyDecisionPoint = $pdp;
    }

    public function getPolicyDecisionPoint()
    {
        return $this->policyDecisionPoint;
    }

    public function  setUser(UserInterface $user)
    {
        $this->user = $user;
    }


}
