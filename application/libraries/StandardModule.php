<?php

abstract class StandardModule implements ModuleInterface, PolicyEnforcementPointInterface {

    /**
     * @var string
     */
    private $identifier;
    /**
     * @var ModuleAggregationInterface;
     */
    private $aggregation;
    /**
     * @var PolicyDecisionPointInterface;
     */
    private $policyDecisionPoint;

    /**
     * @param string $identifier
     */
    public function __construct($identifier = NULL)
    {
        if (isset($identifier))
        {
            $this->identifier = $identifier;
        }
        else
        {
            $this->identifier = get_class($this);
        }
    }

    /*
     * ModuleInterface implementation
     */

    public function getDescription()
    {
        return "";
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function getParentIdentifier()
    {
        return NULL;
    }

    public function getPanel()
    {
        return NULL;
    }

    public function getTitle()
    {
        return $this->getIdentifier();
    }

    public function setPolicyDecisionPoint(PolicyDecisionPointInterface $pdp)
    {
        $this->policyDecisionPoint = $pdp;
    }

    public function getPolicyDecisionPoint()
    {
        return $this->policyDecisionPoint;
    }

    public function aggregate(ModuleAggregationInterface $aggregation)
    {
        $this->aggregation = $aggregation;
    }

}

abstract class StandardCompositeModule extends StandardModule implements ModuleCompositeInterface {

    private $children = array();

    public function addChild(ModuleInterface $childModule)
    {
        if ( ! isset($this->children[$childModule->getIdentifier()]))
        {
            $this->children[$childModule->getIdentifier()] = $childModule;
            ksort($this->children);
        }
    }

    public function getChildren()
    {
        // TODO: authorize access request on policy decision point.
        return array_values($this->children);
    }

}