<?php
namespace Nethgui\Module;

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
 * A Controller for handling a generic table CRUD scenario, and any other
 * action defined on a table.
 * 
 * - Tracks the actions involving a row
 * - Tracks the actions involving the whole table
 *
 * @see Table\Modify
 * @see Table\Read
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 * @api
 */
class TableController extends \Nethgui\Core\Module\Controller
{

    /**
     *
     * @var array
     */
    private $tableAdapterArguments;

    /**
     * @var array
     */
    private $rowActions;

    /**
     * @var array
     */
    private $tableActions;

    /**
     *
     * @var \Nethgui\Adapter\AdapterInterface
     */
    private $tableAdapter;

    /**
     * @param string $identifier
     * @param array $tableAdapterArguments     
     * @param array $columns
     * @param array $rowActions
     * @param array $tableActions
     */
    public function __construct($identifier, $tableAdapterArguments, $columns, $rowActions, $tableActions)
    {
        parent::__construct($identifier);
        $this->tableAdapterArguments = $tableAdapterArguments;

        /*
         *  Create and add the READ action, that displays the table.
         */
        $this->addChild(new Table\Read('read', $columns));

        foreach ($rowActions as $actionObject) {
            $this->addRowAction($actionObject);
        }

        foreach ($tableActions as $actionObject) {
            $this->addTableAction($actionObject);
        }
    }

    public function initialize()
    {
        /*
         * Create the table adapter object and assign it to every children, if
         * it has not been done before.
         */
        $this->tableAdapter = call_user_func_array(array($this->getPlatform(), 'getTableAdapter'), $this->tableAdapterArguments);
        foreach ($this->getChildren() as $action) {
            if ($action instanceof Table\Action
                && ! $action->hasTableAdapter()) {
                $action->setTableAdapter($this->tableAdapter);
            }
        }

        /**
         * Calling the parent method at this point ensures that the table
         * adapter has been set BEFORE the child initialization
         */
        parent::initialize();
    }

    public function addChild(\Nethgui\Core\ModuleInterface $childModule)
    {
        parent::addChild($childModule);
        if (!is_null($this->tableAdapter)
            && $childModule instanceof Table\ActionInterface
            && ! $childModule->hasTableAdapter()) {
            $childModule->setTableAdapter($this->tableAdapter);
        }
        return $this;
    }

    /**
     * A column action is executed in a row context (i.e. row updating, deletion...)
     * @see getRowActions()
     * @return TableController
     */
    public function addRowAction(\Nethgui\Core\ModuleInterface $a)
    {
        $this->rowActions[] = $a;
        $this->addChild($a);
        return $this;
    }

    /**
     * Actions for a single row of the table
     * @return array
     */
    public function getRowActions()
    {
        return $this->rowActions;
    }

    /**
     * A table action involves the whole table (i.e. create a new row, 
     * print the table...)
     * @see getTableActions()
     * @return TableController
     */
    public function addTableAction(\Nethgui\Core\ModuleInterface $a)
    {
        $this->tableActions[] = $a;
        $this->addChild($a);
        return $this;
    }

    /**
     * Actions for the whole table
     * @return array
     */
    public function getTableActions()
    {
        return $this->tableActions;
    }

    protected function getCurrentActionParameter($parameter)
    {
        if ( ! isset($this->currentAction)) {
            return NULL;
        }

        $currentActionRequest = $this->getRequest()->spawnRequest($this->currentAction->getIdentifier());

        if ( ! $currentActionRequest->hasParameter($parameter)) {
            return NULL;
        }

        return $currentActionRequest->getParameter($parameter);
    }

    /**
     * @todo refactor into parent class
     */
    public function prepareView(\Nethgui\Core\ViewInterface $view)
    {
        parent::prepareView($view);
        if ($view->getTargetFormat() !== $view::TARGET_JSON) {
            return;
        }

        if ($this->getRequest()->isSubmitted()
            && $this->getRequest()->isValidated()
            && $this->hasAction('read')) {
            // Load 'read' action when some other action has occurred,
            // to refresh the tabular data.
            $readAction = $this->getAction('read');
            $innerView = $view->spawnView($readAction, TRUE);
            $readAction->prepareView($innerView);
            $view->getCommandListFor('read')->show();
        } elseif ( ! $this->getRequest()->isSubmitted()) {
            $view->getCommandListFor($this->currentAction->getIdentifier())->show();
        }
    }

    /**
     *
     * @param array $createDefaults
     * @return TableController
     */
    protected function setCreateDefaults($createDefaults)
    {
        $create = $this->getAction('create');
        if (is_null($create)) {
            return $this;
        }
        $create->setCreateDefaults($createDefaults);
        return $this;
    }

    public function renderIndex(\Nethgui\Renderer\Xhtml $view)
    {
        $view->includeFile('jquery.nethgui.controller.js', 'Nethgui');

        $container = $view->panel()
            ->setAttribute('class', 'Controller')
            ->setAttribute('receiver', '');

        foreach ($this->getChildren() as $index => $module) {

            $moduleIdentifier = $module->getIdentifier();

            $flags = \Nethgui\Renderer\WidgetFactoryInterface::INSET_WRAP;

            if ($moduleIdentifier !== 'read') {
                $flags |= \Nethgui\Renderer\WidgetFactoryInterface::STATE_UNOBSTRUSIVE;
            }

            if ($this->needsAutoFormWrap($module)) {
                $flags |= \Nethgui\Renderer\WidgetFactoryInterface::INSET_FORM;
            }

            $action = $view->inset($moduleIdentifier, $flags)
                ->setAttribute('class', 'Action');

            $container->insert($action);
        }
        return $container;
    }

    public function onParametersSaved(\Nethgui\Core\ModuleInterface $currentAction, $changes)
    {
        // NOOP;
    }

}
