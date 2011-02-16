<?php

final class Local_system_configuration extends CI_Model implements SystemConfigurationInterface, PolicyEnforcementPointInterface {

    public function  __construct()
    {
        parent::__construct();
    }

    public function apply()
    {

    }

    public function read($resource)
    {
        
    }

    public function write($resource, $value)
    {

    }

    public function setPolicyDecisionPoint(PolicyDecisionPointInterface $pdp)
    {

    }

}
?>
