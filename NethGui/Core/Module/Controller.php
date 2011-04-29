<?php
/**
 * @package Core
 * @subpackage Module
 * @author Davide Principi <davide.principi@nethesis.it>
 */

/**
 * Handles action invocations.
 *
 * A Controller is composed of Action Modules. It provides basic request routing
 * and response handling to associated Actions. Only one Action is actually
 * executed. The actual Action is determined by the first URL segment.
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
     * @var NethGui_Core_Module_Action
     */
    private $currentAction;
    /**
     *
     * @var array
     */
    protected $arguments;

    public function __construct($identifier = NULL)
    {
        parent::__construct($identifier);
        $this->viewTemplate = array($this, 'renderCurrentView');
    }

    public function addChild(NethGui_Core_ModuleInterface $module)
    {
        $this->actions[$module->getIdentifier()] = $module;
        $module->setParent($this);
        if ($this->isInitialized()) {
            $module->initialize();
        }
        $module->setHostConfiguration($this->getHostConfiguration());
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

        $this->arguments = $request->getArguments();

        if (empty($this->arguments) || ! isset($this->arguments[0])) {
            // Default action is THE FIRST
            $currentActionIdentifier = key($this->actions);
        } else {
            // The action name is the second argument:
            $currentActionIdentifier = $this->arguments[0];
        }

        if ( ! isset($this->actions[$currentActionIdentifier])) {
            throw new NethGui_Exception_HttpStatusClientError('Not Found', 404);
        }
        $this->currentAction = $this->actions[$currentActionIdentifier];
        $this->currentAction->bind($request->getParameterAsInnerRequest($currentActionIdentifier, array_slice($this->arguments, 1)));
    }

    public function validate(NethGui_Core_ValidationReportInterface $report)
    {
        parent::validate($report);
        $this->currentAction->validate($report);
    }

    public function process()
    {
        parent::process();
        return $this->currentAction->process();
    }

    public function prepareView(NethGui_Core_ViewInterface $view, $mode)
    {
        $innerView = $view->spawnView($this->currentAction, TRUE);
        
        if ($mode == self::VIEW_REFRESH) {
            $view['__action'] = $this->currentAction->getIdentifier();
            $view['__arguments'] = implode('/', $this->arguments);
        }
        parent::prepareView($view, $mode);        
        $this->currentAction->prepareView($innerView, $mode);
    }

    /**
     * Render callback.
     *
     * This is the view template callback function that forwards the
     * render message to the current action.
     *
     * @internal Actually called by the framework.
     * @param array $state The view state
     * @return string
     */
    public function renderCurrentView($state)
    {
        return $state['view'][$this->currentAction->getIdentifier()]->render();
    }

}