<?php
namespace Nethgui\Controller;

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
class CompositeController extends \Nethgui\Module\Composite implements \Nethgui\Controller\RequestHandlerInterface
{
    /**
     * The action where to forward method calls
     * @var \Nethgui\Module\ModuleInterface
     */
    protected $currentAction;
    private $request;

    /**
     *
     * @return \Nethgui\Controller\RequestInterface
     */
    protected function getRequest()
    {
        return isset($this->request) ? $this->request : NullRequest::getInstance();;
    }

    /**
     * Overrides Composite bind() method, defining what is the current action
     * and forwarding the call to it.
     *
     * @param \Nethgui\Controller\RequestInterface $request 
     */
    public function bind(\Nethgui\Controller\RequestInterface $request)
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

        if ($this->currentAction instanceof \Nethgui\Controller\RequestHandlerInterface) {
            $this->currentAction->bind($request->spawnRequest($actionId));
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
     * @return \Nethgui\Module\ModuleInterface
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
        return $this->getAction($identifier) instanceof \Nethgui\Module\ModuleInterface;
    }

    /**
     * Implements validate() method, forwarding the call to current action only.
     * @param \Nethgui\Controller\ValidationReportInterface $report
     * @return void 
     */
    public function validate(\Nethgui\Controller\ValidationReportInterface $report)
    {
        if (is_null($this->currentAction)) {
            return;
        }

        if ($this->currentAction instanceof \Nethgui\Controller\RequestHandlerInterface) {
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

        if ($this->currentAction instanceof \Nethgui\Controller\RequestHandlerInterface) {
            $this->currentAction->process();
        }
    }

    /**
     * The original Framework behaviour is implemented by
     * prepareNextViewOptimized()
     *
     * @see prepareNextViewOptimized()
     * @return boolean FALSE
     */
    public function nextPath()
    {
        return FALSE;
    }

    /**
     * Display all actions in a disabled state if current action is not defined,
     * otherwise display the current action only.
     * 
     * @param \Nethgui\View\ViewInterface $view
     */
    public function prepareView(\Nethgui\View\ViewInterface $view)
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
        } elseif ($this->currentAction instanceof \Nethgui\View\ViewableInterface) {
            $view->setTemplate(array($this, 'renderCurrentAction'));

            // Spawn and prepare the view for the current action:
            $innerView = $view->spawnView($this->currentAction, TRUE);
            $this->currentAction->prepareView($innerView);

            // Optimize next view for valid requests:
            if ($this->getRequest()->isValidated()) {
                $this->prepareNextViewOptimized($view);
            }
        }
    }

    /**
     * Save a request/response round, putting the next view data in the response
     * 
     * @param \Nethgui\View\ViewInterface $view 
     */
    private function prepareNextViewOptimized(\Nethgui\View\ViewInterface $view)
    {
        $np = $this->currentAction->nextPath();

        if ($np === FALSE) {
            return;
        }

        $nextModule = $this->getAction(\Nethgui\array_head(explode('/', $np)));
        $location = $view->getModuleUrl($np);
        if ($nextModule instanceof \Nethgui\View\ViewableInterface) {
            // spawn and prepare the next view data:
            $nextView = $view->spawnView($nextModule, TRUE);
            $nextModule->prepareView($nextView);
            if ($view->getTargetFormat() === $view::TARGET_JSON) {
                $nextView->getCommandList()->prefetched(); // placeholder.
                $nextView->getCommandList()->show(); // Display the prefetched view
                $this->getPlatform()
                    ->setDetachedProcessCondition('success', array('location' => array('url' => $location . '?taskStatus=success&taskId={taskId}'), 'freeze' => TRUE))
                    ->setDetachedProcessCondition('failure', array('location' => array('url' => $location . '?taskStatus=failure&taskId={taskId}'), 'freeze' => TRUE))
                ;
            } else {
                // show is implemented as HTTP redirection. Avoid self-loops:
                if ($nextModule !== $this->currentAction) {
                    $view->getCommandList()->sendQuery($location);
                }
            }
        } else {
            // next path does not corresponds to a child action: start
            // a new query request to get the next view data:
            $view->getCommandList()->sendQuery($location);
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
        $renderer->includeFile('Nethgui/Js/jquery.nethgui.controller.js');

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
     * @param \Nethgui\Module\ModuleInterface $module
     * @return bool 
     */
    protected function needsAutoFormWrap(\Nethgui\Module\ModuleInterface $module)
    {
        return $module instanceof \Nethgui\Controller\RequestHandlerInterface
            && ! ($module instanceof \Nethgui\Module\ModuleCompositeInterface);
    }

}
