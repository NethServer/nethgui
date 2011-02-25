<?php

abstract class StandardModule implements ModuleInterface, PolicyEnforcementPointInterface {

    /**
     * @var string
     */
    private $identifier;
    /**
     * @var PolicyDecisionPointInterface;
     */
    private $policyDecisionPoint;
    /**
     *
     * @var ModuleInterface;
     */
    private $parent;
    private $formPrefix;

    /*
     * @var bool
     */
    private $initialized = FALSE;
    /**
     *
     * @var RequestInterface
     */
    protected $parameters;

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

    /**
     *  Overriding methods can read current state from model.
     */
    public function initialize()
    {
        if ($this->initialized === FALSE)
        {
            $this->initialized = TRUE;
        }
        else
        {
            throw new Exception("Double Module initialization is forbidden.");
        }
    }

    public function isInitialized()
    {
        return $this->initialized;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function getTitle()
    {
        return $this->getIdentifier();
    }

    public function getDescription()
    {
        return "";
    }

    public function setParent(ModuleInterface $parentModule)
    {
        $this->parent = $parentModule;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function setPolicyDecisionPoint(PolicyDecisionPointInterface $pdp)
    {
        $this->policyDecisionPoint = $pdp;
    }

    public function getPolicyDecisionPoint()
    {
        return $this->policyDecisionPoint;
    }

    public function bind(RequestInterface $parameters)
    {
        $this->parameters = $parameters;
    }

    public function validate(ValidationReportInterface $report)
    {
        if ( ! $this->parameters instanceof RequestInterface)
        {
            throw new Exception("Unbounded parameters.");
        }
    }

    public function process()
    {
        // DO NOTHING 
    }

    public function renderView(Response $response)
    {
        return "";
    }

    protected function renderCodeIgniterView($viewName, $parameters = array())
    {
        $parameters['module'] = $this;
        return CI_Controller::get_instance()->load->view($viewName, $parameters, true);
    }

    public function getNameAttribute($fieldName)
    {
        if ( ! isset($this->formPrefix))
        {
            $this->formPrefix = $this->calculateFormPrefix();
        }
        return $this->formPrefix . '[' . $fieldName . ']';
    }

    private function calculateFormPrefix()
    {
        $module = $this;
        $prefix = '';
        while (true)
        {
            $identifier = $module->getIdentifier();
            $module = $module->getParent();
            if (is_null($module))
            {
                $prefix = $identifier . $prefix;
                break;
            }
            else
            {
                $prefix = '[' . $identifier . ']' . $prefix;
            }
        }

        return $prefix;
    }

    public function getIdAttribute($fieldName)
    {
        $name = $this->getNameAttribute($fieldName);
        $name = str_replace('[', '_', $name);
        $name = str_replace(']', '_', $name);

        return $name;
    }


}

