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
 * A Controller renders its parts embedded in a FORM container.
 *
 * @see NethGui_Core_Module_Composite
 * @package Core
 * @subpackage Module
 */
class NethGui_Core_Module_Controller extends NethGui_Core_Module_Composite implements NethGui_Core_RequestHandlerInterface
{

    /**
     * The action where to forward method calls
     * @var NethGui_Core_Module_Action
     */
    private $currentAction;

    /**
     * Overrides Composite bind() method, defining what is the current action
     * and forwarding the call to it.
     *
     * @param NethGui_Core_RequestInterface $request 
     */
    public function bind(NethGui_Core_RequestInterface $request)
    {
        $arguments = $request->getArguments();

        if ( ! empty($arguments) && isset($arguments[0])) {
            // We can identify the current action
            $this->currentAction = $this->getAction($arguments[0]);
            if (is_null($this->currentAction)) {
                // a NULL action at this point results in a "not found" condition:
                throw new NethGui_Exception_HttpStatusClientError('Not Found', 404);
            }

            $this->currentAction->bind($request->getParameterAsInnerRequest($arguments[0], array_slice($arguments, 1)));
        }
    }

    /**
     * Returns the child with $identifier, or the first child, if $identifier is NULL.
     * 
     * If the child is not found it returns NULL.
     * 
     * @param string $identifier 
     * @return NethGui_Core_ModuleInterface
     */
    private function getAction($identifier = NULL)
    {
        foreach ($this->getChildren() as $child) {
            if ($child->getIdentifier() == $identifier || is_null($identifier))
            {
                return $child;
            }
        }
        return NULL;
    }

    /**
     * Implements validate() method, forwarding the call to current action only.
     * @param NethGui_Core_ValidationReportInterface $report
     * @return type 
     */
    public function validate(NethGui_Core_ValidationReportInterface $report)
    {
        if (is_null($this->currentAction)) {
            return;
        }

        $this->currentAction->validate($report);
    }

    /**
     * Implements process() method, forwarding the call to current 
     * action only
     * @param NethGui_Core_NotificationCarrierInterface $carrier
     * @return type 
     */
    public function process(NethGui_Core_NotificationCarrierInterface $carrier)
    {
        if (is_null($this->currentAction)) {
            return;
        }

        $this->currentAction->process($carrier);
    }

    /**
     * Implements prepareView() to display all actions in a disabled 
     * state (index) if current action is not defined, or to display the 
     * current action.
     * 
     * @param NethGui_Core_ViewInterface $view
     * @param type $mode 
     */
    public function prepareView(NethGui_Core_ViewInterface $view, $mode)
    {
        parent::prepareView($view, $mode);
        
        if (is_null($this->currentAction)) {
            foreach ($this->getChildren() as $childModule) {
                $innerView = $view->spawnView($childModule, TRUE);
                $childModule->prepareView($innerView, $mode);
                // override action name:
                $innerView['__action'] = 'index';                
            }
            $view->setTemplate(array($this, 'renderController'));
        } else {
            $view->setTemplate(array($this, 'renderCurrentView'));
            $innerView = $view->spawnView($this->currentAction, TRUE);
            $innerView['__action'] = $this->currentAction->getIdentifier();
            $this->currentAction->prepareView($innerView, $mode);
        }
    }

    public function renderController(NethGui_Renderer_Abstract $view)
    {
        $form = $view->form('', NethGui_Renderer_Abstract::STATE_DISABLED);
        foreach ($this->getChildren() as $child) {
            $form->inset($child->getIdentifier());
        }
        return $view;
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