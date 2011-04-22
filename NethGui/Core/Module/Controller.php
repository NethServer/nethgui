<?php
/**
 * @package Core
 * @subpackage Module
 * @author Davide Principi <davide.principi@nethesis.it>
 */

/**
 * Handles action invocations.
 *
 * A Controller is composed of Actions. It provides basic request routing and response handling to associated Actions.
 * Only an Action is actually executed. The actual Action is determined by the first URL segment.
 *
 * @see NethGui_Core_Module_Composite
 * @package Core
 * @subpackage Module
 */
class NethGui_Core_Module_Controller extends NethGui_Core_Module_Standard implements NethGui_Core_ModuleCompositeInterface
{

    /**
     * @var array
     */
    private $actions = array();
    /**
     *
     * @var array
     */
    private $arguments;
    /**
     *
     * @var NethGui_Core_Module_Action
     */
    private $currentAction;

    public function __construct($identifier = NULL)
    {
        parent::__construct($identifier);
        $this->viewTemplate = array($this, 'renderCurrentView');
    }

    public function addChild(NethGui_Core_ModuleInterface $module)
    {
        $this->actions[$module->getIdentifier()] = $module;
        $module->setParent($this);
    }

    public function getChildren()
    {
        return array_values($this->actions);
    }

    public function initialize()
    {
        parent::initialize();
        foreach ($this->getChildren() as $action) {
            if ( ! $action->isInitialized()) {
                $action->initialize();
            }
        }
    }

    public function setHostConfiguration(NethGui_Core_HostConfigurationInterface $hostConfiguration)
    {
        parent::setHostConfiguration($hostConfiguration);
        foreach ($this->getChildren() as $action) {
            $action->setHostConfiguration($hostConfiguration);
        }
    }

    public function bind(NethGui_Core_RequestInterface $request)
    {
        parent::bind($request);

        // If we have no action defined there is nothing to do here.
        if (empty($this->actions)) {
            return;
        }

        reset($this->actions);

        $this->arguments = $this->extractNumericKeys($request);

        if (empty($this->arguments)) {
            // Default action is THE FIRST
            $currentActionIdentifier = key($this->actions);
        } else {
            $currentActionIdentifier = $this->arguments[0];
        }

        if ( ! isset($this->actions[$currentActionIdentifier])) {
            throw new NethGui_Exception_HttpStatusClientError('Not Found', 404);
        }
        $this->currentAction = $this->actions[$currentActionIdentifier];
        $this->currentAction->bindArguments($currentActionIdentifier, array_slice($this->arguments, 1));
        $this->currentAction->bind($request->getParameterAsInnerRequest($currentActionIdentifier));
    }

    public function validate(NethGui_Core_ValidationReportInterface $report)
    {
        parent::validate($report);
        $this->currentAction->validate($report);
    }

    public function process()
    {
        parent::process();
        $this->currentAction->process();
    }

    public function prepareView(NethGui_Core_ViewInterface $view, $mode)
    {        
        $innerView = $view->spawnView($this->currentAction);        
        $view[$this->currentAction->getIdentifier()] = $innerView;
        parent::prepareView($view, $mode);
        $this->currentAction->prepareView($innerView, $mode);
    }

    private function extractNumericKeys(NethGui_Core_RequestInterface $request)
    {
        $arguments = array();

        foreach ($request->getParameters() as $parameterName) {
            if (is_numeric($parameterName)) {
                $arguments[intval($parameterName)] = $request->getParameter($parameterName);
            }
        }

        ksort($arguments);

        return array_values($arguments);
    }

    public function renderCurrentView($state)
    {
        return $state['view'][$this->currentAction->getIdentifier()]->render();
    }

    /**
     *
     * @return NethGui_Core_Module_Action
     */
    protected function getCurrentAction() {
        return $this->currentAction;
    }
}