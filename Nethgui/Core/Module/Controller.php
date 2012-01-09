<?php
namespace Nethgui\Core\Module;

/*
 * Copyright (C) 2011 Nethesis S.r.l.
 * 
 * This script is part of NethServer.
 * 
 * NethServer is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * NethServer is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with NethServer.  If not, see <http://www.gnu.org/licenses/>.
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
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 * @api
 * @see Composite
 */
class Controller extends Composite implements \Nethgui\Core\RequestHandlerInterface
{

    /**
     * The action where to forward method calls
     * @var \Nethgui\Core\ModuleInterface
     */
    protected $currentAction;
    private $request;

    /**
     *
     * @return \Nethgui\Core\RequestInterface
     */
    protected function getRequest()
    {
        return $this->request;
    }

    /**
     * Overrides Composite bind() method, defining what is the current action
     * and forwarding the call to it.
     *
     * @param \Nethgui\Core\RequestInterface $request 
     */
    public function bind(\Nethgui\Core\RequestInterface $request)
    {
        $this->request = $request;
        $actionId = $this->establishCurrentActionId();

        if ($actionId === FALSE) {
            return; // don't bind the request to any action. Render action index.
        }

        $this->currentAction = $this->getAction($actionId);

        if (is_null($this->currentAction)) {
            throw new \Nethgui\Exception\HttpException('Not Found', 404, 1322148401);
        }

        if ($this->currentAction instanceof \Nethgui\Core\RequestHandlerInterface) {
            $this->currentAction->bind($request->spawnRequest($actionId, \Nethgui\array_rest($request->getPath())));
        }
    }

    /**
     * Decide what to do
     *
     * Consider the first available path segment as the action identifier. If
     * that is missing, render the action index.
     *
     * You can override this method to set a different rule.
     *
     * @return string|bool The action identifier or FALSE
     */
    protected function establishCurrentActionId()
    {
        return \Nethgui\array_head($this->getRequest()->getPath());
    }

    /**
     * Returns the child with $identifier
     * 
     * If the child is not found it returns NULL.
     * 
     * @param string $identifier 
     * @return \Nethgui\Core\ModuleInterface
     */
    public function getAction($identifier)
    {
        foreach ($this->getChildren() as $child) {
            if ($child->getIdentifier() === $identifier) {
                return $child;
            }
        }
        return NULL;
    }

    protected function hasAction($identifier)
    {
        return $this->getAction($identifier) instanceof \Nethgui\Core\ModuleInterface;
    }

    /**
     * Implements validate() method, forwarding the call to current action only.
     * @param \Nethgui\Core\ValidationReportInterface $report
     * @return void 
     */
    public function validate(\Nethgui\Core\ValidationReportInterface $report)
    {
        if (is_null($this->currentAction)) {
            return;
        }

        if ($this->currentAction instanceof \Nethgui\Core\RequestHandlerInterface) {
            $this->currentAction->validate($report);
        }
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

        if ($this->currentAction instanceof \Nethgui\Core\RequestHandlerInterface) {
            $this->currentAction->process();
        }
    }

    /**
     * Implements prepareView() to display all actions in a disabled 
     * state (index) if current action is not defined, or to display the 
     * current action.
     * 
     * @param \Nethgui\Core\ViewInterface $view
     */
    public function prepareView(\Nethgui\Core\ViewInterface $view)
    {
        parent::prepareView($view);

        if (is_null($this->currentAction)) {
            // Prepare an unobstrusive view of each child. The first one is
            // shown to the user.
            $view->setTemplate(array($this, 'renderIndex'));
            foreach ($this->getChildren() as $childModule) {
                $innerView = $view->spawnView($childModule, TRUE);
                $childModule->prepareView($innerView);
            }
        } elseif ($this->currentAction instanceof \Nethgui\Core\ViewableInterface) {
            $view->setTemplate(array($this, 'renderCurrentAction'));
            $innerView = $view->spawnView($this->currentAction, TRUE);
            $this->currentAction->prepareView($innerView);

            if ($this->getRequest()->isSubmitted()
                && $this->getRequest()->isValidated()
                && $this->currentAction instanceof \Nethgui\Core\Module\ActionInterface) {
                $this->handleNextActionId($view, $this->currentAction);
            } elseif ($view->getTargetFormat() === $view::TARGET_JSON
                && ! $this->getRequest()->isSubmitted()) {
                // JSON view need a show() command:
                $view->getCommandListFor($this->currentAction->getIdentifier())->show();
            }
        }
    }

    private function handleNextActionId(\Nethgui\Core\ViewInterface $view, \Nethgui\Core\Module\ActionInterface $action)
    {
        $actionView = $view->spawnView($action);

        $actionUrl = $actionView->getModuleUrl();
        $nextUrl = $actionView->getModuleUrl($action->getNextActionPath());

        if ($actionUrl === $nextUrl) {
            return;
        }

        $actionView->getCommandList()->sendQuery($nextUrl);
    }

    /**
     * Render callback.
     *
     * This is the view template callback function that forwards the
     * render message to the current action.
     *
     * Note: The current action template is wrapped inside a DIV.Action tag.
     *
     * @param \Nethgui\Renderer\Xhtml $view The view
     * @return string
     */
    public function renderCurrentAction(\Nethgui\Renderer\Xhtml $view)
    {
        $flags = $view::INSET_WRAP;
        if ($this->needsAutoFormWrap($this->currentAction)) {
            $flags |= $view::INSET_FORM;
        }
        return $view->inset($this->currentAction->getIdentifier(), $flags);
    }

    public function renderIndex(\Nethgui\Renderer\Xhtml $renderer)
    {
        $renderer->includeFile('jquery.nethgui.controller.js', 'Nethgui');

        $container = $renderer->panel()->setAttribute('class', 'Controller');

        foreach ($this->getChildren() as $index => $module) {
            $identifier = $module->getIdentifier();

            $flags = $renderer::INSET_WRAP;

            if ($index > 0) {
                $flags |= $renderer::STATE_UNOBSTRUSIVE;
            }

            if ($this->needsAutoFormWrap($module)) {
                $flags |= $renderer::INSET_FORM;
            }

            $container->insert($renderer->inset($identifier, $flags)->setAttribute('class', 'Action'));
        }

        return $container;
    }

    /**
     * Check if the given module is a leaf and can handle requests.
     * 
     * @param \Nethgui\Core\ModuleInterface $module
     * @return bool 
     */
    protected function needsAutoFormWrap(\Nethgui\Core\ModuleInterface $module)
    {
        return $module instanceof \Nethgui\Core\RequestHandlerInterface
            && ! ($module instanceof \Nethgui\Core\ModuleCompositeInterface);
    }

}
