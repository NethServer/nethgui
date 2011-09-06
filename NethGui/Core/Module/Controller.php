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
 * A a top level Controller renders its parts embedded in a FORM container.
 *
 * @see NethGui_Core_Module_Composite
 * @package Core
 * @subpackage Module
 */
class NethGui_Core_Module_Controller extends NethGui_Core_Module_Composite implements NethGui_Core_RequestHandlerInterface
{

    /**
     * The action where to forward method calls
     * @var NethGui_Core_Module_Interface
     */
    protected $currentAction;
    private $request;

    /**
     *
     * @return NethGui_Core_RequestInterface
     */
    protected function getRequest()
    {
        return $this->request;
    }

    /**
     * Overrides Composite bind() method, defining what is the current action
     * and forwarding the call to it.
     *
     * @param NethGui_Core_RequestInterface $request 
     */
    public function bind(NethGui_Core_RequestInterface $request)
    {
        $this->request = $request;
        $arguments = $request->getArguments();

        if ( ! empty($arguments) && isset($arguments[0])) {
            // We can identify the current action from request arguments
            $actionId = $arguments[0];
            if ( ! $this->hasAction($actionId)) {
                // a NULL action at this point results in a "not found" condition:
                throw new NethGui_Exception_HttpStatusClientError('Not Found', 404);
            }
        } elseif ($request->isSubmitted() && $request->hasParameter('__action')) {
            // We don't have request arguments, but if request is submitted
            // we can check the `__action` parameter that is automatically
            // added by this class.
            $actionId = $request->getParameter('__action');
        } else {
            return; // don't bind the request to any action.
        }

        $this->currentAction = $this->getAction($actionId);
        $this->currentAction->bind($request->getParameterAsInnerRequest($actionId, array_slice($arguments, 1)));
    }

    /**
     * Returns the child with $identifier, or the first child, if $identifier is NULL.
     * 
     * If the child is not found it returns NULL.
     * 
     * @param string $identifier 
     * @return NethGui_Core_ModuleInterface
     */
    protected function getAction($identifier = NULL)
    {
        foreach ($this->getChildren() as $child) {
            if ($child->getIdentifier() == $identifier || is_null($identifier))
            {
                return $child;
            }
        }
        return NULL;
    }

    protected function hasAction($identifier)
    {
        return is_object($this->getAction($identifier));
    }

    /**
     * Implements validate() method, forwarding the call to current action only.
     * @param NethGui_Core_ValidationReportInterface $report
     * @return void 
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
     * @return void 
     */
    public function process()
    {
        if (is_null($this->currentAction)) {
            return;
        }

        $this->currentAction->process();
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
            return;
        }

        $view->setTemplate(array($this, 'renderCurrentAction'));
        $innerView = $view->spawnView($this->currentAction, TRUE);
        $view['__action'] = $this->currentAction->getIdentifier();
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
    public function renderCurrentAction(NethGui_Renderer_Abstract $view)
    {
        return $this->renderFormWrap($view, $this->currentAction->getIdentifier());
    }

}