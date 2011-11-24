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
 * @see Composite
 */
class Controller extends Composite implements \Nethgui\Core\RequestHandlerInterface, DefaultUiStateInterface
{

    /**
     * The action where to forward method calls
     * @var Nethgui_Core_Module_Interface
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

        if (empty($actionId)) {
            return; // don't bind the request to any action.
        }

        $this->currentAction = $this->getAction($actionId);
        if ($this->currentAction instanceof \Nethgui\Core\RequestHandlerInterface) {
            $this->currentAction->bind($request->getParameterAsInnerRequest($actionId, \Nethgui\array_rest($request->getArguments())));
        }
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
                throw new \Nethgui\Exception\HttpException('Not Found', 404, 1322148401);
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
     * @return \Nethgui\Core\ModuleInterface
     */
    public function getAction($identifier = NULL)
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
     * @param type $mode 
     */
    public function prepareView(\Nethgui\Core\ViewInterface $view, $mode)
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
     * @param \Nethgui\Renderer\Xhtml $view The view
     * @return string
     */
    public function renderCurrentAction(\Nethgui\Renderer\Xhtml $view)
    {
        return $view->inset($this->currentAction->getIdentifier());
    }

    public function renderDefault(\Nethgui\Renderer\Xhtml $view)
    {
        $containerClass = 'Controller';

        if ($this instanceof DefaultUiStateInterface) {
            if ($this->getDefaultUiStyleFlags()
                & DefaultUiStateInterface::STYLE_CONTAINER_TABLE) {
                $containerClass = 'TableController';
            } elseif ($this->getDefaultUiStyleFlags()
                & DefaultUiStateInterface::STYLE_CONTAINER_TABS) {
                $containerClass = 'TabsController';
            }
        }

        $container = $view->panel()->setAttribute('class', $containerClass);

        foreach ($this->getChildren() as $index => $module) {
            if ($module instanceof DefaultUiStateInterface) {
                $flagEnabled = $module->getDefaultUiStyleFlags()
                    & DefaultUiStateInterface::STYLE_ENABLED;
                if ($module->getDefaultUiStyleFlags()
                    & DefaultUiStateInterface::STYLE_DIALOG) {
                    $widgetClass = 'Dialog';
                } else {
                    $widgetClass = 'Action';
                }
            } else {
                $flagEnabled = $index == 0;
                $widgetClass = 'Action';
            }

            $flags = 0;

            if ( ! $flagEnabled) {
                $flags |= $view::STATE_DISABLED;
            } else {
                $widgetClass .= ' visible';
            }

            $panel = $view->panel()
                ->setAttribute('flags', $flags)
                ->setAttribute('class', $widgetClass)
                ->setAttribute('name', $module->getIdentifier())
                ->insert($view->inset($module->getIdentifier()));
            $container->insert($panel);
        }
        return $container;
    }

    public function getDefaultUiStyleFlags()
    {
        return self::STYLE_NOFORMWRAP;
    }

}
