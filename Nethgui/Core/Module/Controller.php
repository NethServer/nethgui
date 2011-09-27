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
 * @see Nethgui_Core_Module_Composite
 * @package Core
 * @subpackage Module
 */
class Nethgui_Core_Module_Controller extends Nethgui_Core_Module_Composite implements Nethgui_Core_RequestHandlerInterface
{

    /**
     * The action where to forward method calls
     * @var Nethgui_Core_Module_Interface
     */
    protected $currentAction;
    private $request;

    /**
     *
     * @return Nethgui_Core_RequestInterface
     */
    protected function getRequest()
    {
        return $this->request;
    }

    /**
     * Overrides Composite bind() method, defining what is the current action
     * and forwarding the call to it.
     *
     * @param Nethgui_Core_RequestInterface $request 
     */
    public function bind(Nethgui_Core_RequestInterface $request)
    {
        $this->request = $request;
        $actionId = $this->establishCurrentActionId();

        if (empty($actionId)) {
            return; // don't bind the request to any action.
        }

        $this->currentAction = $this->getAction($actionId);
        $this->currentAction->bind($request->getParameterAsInnerRequest($actionId, array_slice($request->getArguments(), 1)));
    }

    protected function establishCurrentActionId()
    {
        $request = $this->getRequest();
        $arguments = $request->getArguments();
        $actionId = FALSE;

        if ( ! empty($arguments) && isset($arguments[0])) {
            // We can identify the current action from request arguments
            $actionId = $arguments[0];
            if ( ! $this->hasAction($actionId)) {
                // a NULL action at this point results in a "not found" condition:
                throw new Nethgui_Exception_HttpStatusClientError('Not Found', 404);
            }
        }

        return $actionId;
    }

    /**
     * Returns the child with $identifier, or the first child, if $identifier is NULL.
     * 
     * If the child is not found it returns NULL.
     * 
     * @param string $identifier 
     * @return Nethgui_Core_ModuleInterface
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
     * @param Nethgui_Core_ValidationReportInterface $report
     * @return void 
     */
    public function validate(Nethgui_Core_ValidationReportInterface $report)
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
     * @param Nethgui_Core_ViewInterface $view
     * @param type $mode 
     */
    public function prepareView(Nethgui_Core_ViewInterface $view, $mode)
    {
        parent::prepareView($view, $mode);

        if (is_null($this->currentAction)) {
            // Handle a NULL current action, rendering all the children in a
            // "DISABLED" state.
            foreach ($this->getChildren() as $childModule) {
                $innerView = $view->spawnView($childModule, TRUE);
                $childModule->prepareView($innerView, $mode);
            }
            $view->setTemplate(array($this, 'renderDefault'));
        } else {
            $view->setTemplate(array($this, 'renderCurrentAction'));
            $innerView = $view->spawnView($this->currentAction, TRUE);
            $this->currentAction->prepareView($innerView, $mode);
        }
    }

    /**
     * Render callback.
     *
     * This is the view template callback function that forwards the
     * render message to the current action.
     *
     * Note: The current action template is wrapped inside a DIV.Action tag.
     *
     * @internal Actually called by the framework.
     * @param Nethgui_Renderer_Abstract $view The view
     * @return string
     */
    public function renderCurrentAction(Nethgui_Renderer_Abstract $view)
    {
        $contentWidget = $view->inset($this->currentAction->getIdentifier());
        return $view->panel()->setAttribute('class', 'Action')->insert($contentWidget);
    }

    public function renderDefault(Nethgui_Renderer_Abstract $view)
    {
        $container = $view->panel()->setAttribute('class', 'Controller');
        foreach ($this->getChildren() as $index => $module) {
            $this->renderAction($view, $container, $module, $index);
        }
        return $container;
    }

    protected function renderAction(Nethgui_Renderer_Abstract $view, Nethgui_Renderer_WidgetInterface $container, Nethgui_Core_ModuleInterface $module, $index)
    {
        if ($index == 0) {
            $flags = 0;
            $extraCssClass = ' raised';
        } else {
            $flags = Nethgui_Renderer_Abstract::STATE_DISABLED;
            $extraCssClass = '';
        }

        $actionWidget = $view->panel($flags)->insert($view->inset($module->getIdentifier()));

        $actionWidget->setAttribute('name', $module->getIdentifier());

        if ($module instanceof Nethgui_Module_Table_Action && $module->isModal()) {
            $actionWidget->setAttribute('class', 'Dialog');
        } else {
            $actionWidget->setAttribute('class', 'Reaction' . $extraCssClass);
        }

        $container->insert($actionWidget);
    }

}