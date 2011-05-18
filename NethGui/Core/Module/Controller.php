<?php
/**
 * @package Core
 * @subpackage Module
 * @author Davide Principi <davide.principi@nethesis.it>
 */

/**
 * A composition of modules, where only one member receives the request handling calls.
 *
 * A Controller is composed of modules representing actions. 
 * It determines the "current" action to be executed by looking at the 
 * request arguments.
 *
 * @see NethGui_Core_Module_Composite
 * @package Core
 * @subpackage Module
 */
class NethGui_Core_Module_Controller extends NethGui_Core_Module_Abstract implements NethGui_Core_ModuleCompositeInterface, NethGui_Core_RequestHandlerInterface
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
        // If we have no action defined there is nothing to do here.
        if (empty($this->actions)) {
            throw new NethGui_Exception_HttpStatusClientError('Not Found', 404);
        }

        reset($this->actions);

        $arguments = $request->getArguments();

        if (empty($arguments) || ! isset($arguments[0])) {
            // Default action is THE FIRST
            $currentActionIdentifier = key($this->actions);
        } else {
            // The action name is the first argument:
            $currentActionIdentifier = $arguments[0];
        }

        if ( ! isset($this->actions[$currentActionIdentifier])) {
            throw new NethGui_Exception_HttpStatusClientError('Not Found', 404);
        }
        $this->currentAction = $this->actions[$currentActionIdentifier];
        $this->currentAction->bind($request->getParameterAsInnerRequest($currentActionIdentifier, array_slice($arguments, 1)));
    }

    public function validate(NethGui_Core_ValidationReportInterface $report)
    {
        $this->currentAction->validate($report);
    }

    public function process(NethGui_Core_NotificationCarrierInterface $carrier)
    {
        $this->currentAction->process($carrier);
    }

    public function prepareView(NethGui_Core_ViewInterface $view, $mode)
    {
        parent::prepareView($view, $mode);
        $view->setTemplate(array($this, 'renderCurrentView'));

        if ($mode == self::VIEW_REFRESH) {
            $view['__action'] = $this->currentAction->getIdentifier();
        }        
               
        $innerView = $view->spawnView($this->currentAction, TRUE);
        $this->currentAction->prepareView($innerView, $mode);
    }

    /**
     * Render callback.
     *
     * This is the view template callback function that forwards the
     * render message to the current action.
     *
     * @internal Actually called by the framework.
     * @param NethGui_Renderer_Abstract $view The view
     * @return string
     */
    public function renderCurrentView(NethGui_Renderer_Abstract $view)
    {
        $action = $this->currentAction->getIdentifier();
        return $view
            ->form($action)
            ->inset($action)
        ;
    }

}