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
     *
     * @var ModuleInterface;
     */
    private $parent;
    private $formPrefix;

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

    public function initialize()
    {

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

    /**
     * @var array
     */
    private $inputParameters;

    public function bind($inputParameters)
    {
        $this->inputParameters = $inputParameters;
    }

    public function validate()
    {
        return true;
    }

    public function render()
    {
        return "";
    }

    /**
     * Renders a PHP view by means of Code Igniter Framework.
     * @param string $viewName Name of the view .php file under `views/` directory.
     * @param array $parameters View parameters
     * @return string View output
     */
    protected function renderView($viewName, $parameters = array())
    {
        $parameters['panel'] = $this;
        $parameters['inputParameters'] = $this->inputParameters;
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

    public function setParent(ModuleInterface $parentModule)
    {
        $this->parent = $parentModule;
    }

    public function getParent()
    {
        return $this->parent;
    }

}

abstract class StandardCompositeModule extends StandardModule implements ModuleCompositeInterface {

    private $children = array();

    /**
     * Propagates initialize() message to children.
     */
    public function initialize()
    {
        foreach ($this->children as $child)
        {
            $child->initialize();
        }
    }

    public function addChild(ModuleInterface $childModule)
    {
        if ( ! isset($this->children[$childModule->getIdentifier()]))
        {
            $this->children[$childModule->getIdentifier()] = $childModule;
            $childModule->setParent($this);
        }
    }

    public function getChildren()
    {
        // TODO: authorize access request on policy decision point.
        return array_values($this->children);
    }

    /**
     * Default implementation of a composite panel forwards the rendering
     * process to child panels.
     *
     * @return string
     */
    public function render()
    {
        $output = '';
        foreach ($this->getChildren() as $module)
        {
            $output .= $module->render();
        }
        return $this->decorate($output);
    }

    /**
     * Called after children have been rendered.
     *
     * @param string $output Children output
     * @return string Decorated children output
     */
    protected function decorate($output)
    {
        return $output;
    }

}
