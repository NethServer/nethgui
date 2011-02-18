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
        return CI_Controller::get_instance()->load->view($viewName, $parameters, true);
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
        return $output;
    }

}