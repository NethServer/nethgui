<?php

abstract class StandardPanel implements PanelInterface, PolicyEnforcementPointInterface {

    private $identifier;

    private $parameters;

    /**
     * @var PolicyDecisionPointInterface;
     */
    private $policyDecisionPoint;

    public function __construct($identifier = NULL)
    {
        $this->identifier = is_null($identifier) ? get_class($this) : $identifier;
    }

    public function bind($parameters)
    {
        $this->parameters = $parameters;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function render()
    {
     
    }

    public function validate()
    {

    }

    public function setPolicyDecisionPoint(PolicyDecisionPointInterface $pdp)
    {
        $this->policyDecisionPoint = $pdp;
    }

    protected function renderView($viewName, $parameters = array())
    {
        return CI_Controller::get_instance()->load->view($viewName, $parameters, true);
    }
}