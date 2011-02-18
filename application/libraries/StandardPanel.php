<?php

abstract class StandardPanel implements PanelInterface, PolicyEnforcementPointInterface {

    private $identifier;
    /**
     * @var array
     */
    private $inputParameters;
    /**
     * @var PolicyDecisionPointInterface;
     */
    private $policyDecisionPoint;

    public function __construct($identifier = NULL)
    {
        $this->identifier = is_null($identifier) ? get_class($this) : $identifier;
    }

    public function setModule(ModuleInterface $module)
    {
        $this->module = $module;
    }

    public function getModule()
    {
        return $module;
    }

    public function bind($inputParameters)
    {
        $this->inputParameters = $inputParameters;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function getTitle()
    {
        return "";
    }

    public function getDescription()
    {
        return "";
    }

    public function validate()
    {
        return true;
    }

    public function render()
    {
        return "";
    }

    public function setPolicyDecisionPoint(PolicyDecisionPointInterface $pdp)
    {
        $this->policyDecisionPoint = $pdp;
    }

    public function getPolicyDecisionPoint()
    {
        return $this->policyDecisionPoint;
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
        return $this->getModuleIdentifier() . '[' . $this->getIdentifier() . "][{$fieldName}]";
    }

    public function getIdAttribute($fieldName)
    {
        return $this->getModuleIdentifier() . '_' . $this->getIdentifier() . "_{$fieldName}";
    }

    private function getModuleIdentifier()
    {
        
    }

}

abstract class StandardCompositePanel extends StandardPanel implements PanelCompositeInterface {

    private $children = array();

    public function getChildren()
    {
        return array_values($this->children);
    }

    public function addChild(PanelInterface $panel)
    {
        $this->children[$panel->getIdentifier()] = $panel;

        if ($this instanceof PolicyEnforcementPointInterface
                && $panel instanceof PolicyEnforcementPointInterface
                && ! is_null($this->getPolicyDecisionPoint()))
        {
            $panel->setPolicyDecisionPoint($this->getPolicyDecisionPoint());
        }
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
        foreach ($this->getChildren() as $panel)
        {
            if ($panel instanceof PanelInterface)
            {
                $output .= $panel->render();
            }
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