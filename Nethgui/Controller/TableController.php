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
class TableController extends \Nethgui\Controller\CompositeController implements \Nethgui\Adapter\AdapterAggregateInterface
{
    /**
     *
     * @var Table\Read
     */
    private $readAction = NULL;

    /**
     * @var array
     */
    private $rowActions = array();

    /**
     * @var array
     */
    private $tableActions = array();

    /**
     *
     * @var \Nethgui\Adapter\AdapterInterface
     */
    private $tableAdapter = NULL;

    /**
     * Set the adapter object that gives access to the database table
     * 
     * @param \Nethgui\Adapter\AdapterInterface $tableAdapter
     * @return TableController
     */
    protected function setTableAdapter(\Nethgui\Adapter\AdapterInterface $tableAdapter)
    {
        $this->tableAdapter = $tableAdapter;
        return $this;
    }

    public function getAdapter()
    {
        return $this->tableAdapter;
    }

    public function hasAdapter()
    {
        return $this->tableAdapter instanceof \Nethgui\Adapter\AdapterInterface;
    }

    /**
     *
     * @param array $columns
     * @return TableController
     */
    protected function setColumns($columns)
    {
        if (is_null($this->readAction)) {
            $this->readAction = new Table\Read('read');
            $this->addChild($this->readAction);
        }
        $this->readAction->setColumns($columns);
        return $this;
    }

    public function initialize()
    {
        if ( ! $this->hasAdapter()) {
            throw new \LogicException(sprintf('%s: you must call setTableAdapter() before %s::initialize()', get_class($this), __CLASS__), 1325610869);
        }

        // propagate the adapter to every that is missing it:
        foreach ($this->getChildren() as $childAction) {
            if ($childAction instanceof \Nethgui\Controller\Table\AbstractAction
                && ! $childAction->hasAdapter()) {
                $childAction->setAdapter($this->getAdapter());
            }
        }

        /**
         * Calling the parent method at this point ensures that the table
         * adapter has been set BEFORE the child initialization
         */
        parent::initialize();
    }

    /**
     * Add $childAction to the TableController, and propagate the table adapter, 
     * if it has not been done yet.
     * 
     * @param \Nethgui\Module\ModuleInterface $childAction 
     */
    public function addChild(\Nethgui\Module\ModuleInterface $childAction)
    {
        if ($childAction instanceof \Nethgui\Controller\Table\AbstractAction) {
            if ($childAction instanceof \Nethgui\Controller\Table\AbstractAction
                && ! $childAction->hasAdapter()) {
                $childAction->setAdapter($this->getAdapter());
            }
        }
        return parent::addChild($childAction);
    }

    /**
     * A row action is executed in a row context (i.e. row updating, deletion...)
     * 
     * @see getRowActions()
     * @param \Nethgui\Controller\Table\AbstractAction $action
     * @return \Nethgui\Controller\TableController
     */
    public function addRowAction(\Nethgui\Controller\Table\AbstractAction $action)
    {
        $this->rowActions[] = $action;
        $this->addChild($action);
        return $this;
    }

    /**
     * Enable $action to be expanded by instantiating all the classes 
     * under the given $path. 
     * 
     * @param \Nethgui\Controller\Table\AbstractAction $action
     * @param string $path
     * @return \Nethgui\Controller\TableController
     */
    public function addRowActionPluggable(\Nethgui\Controller\Table\AbstractAction $action, $path = 'Plugin')
    {
        return $this->addRowAction(new \Nethgui\Controller\Table\PluggableAction($action, $path));
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
    public function addTableAction(\Nethgui\Module\ModuleInterface $a)
    {
        $this->tableActions[] = $a;
        $this->addChild($a);
        return $this;
    }

    /**
     * Enable $action to be expanded by instantiating all the classes 
     * under the given $path. 
     * 
     * @see addRowActionPluggable()
     * @see addTableAction()
     * @param \Nethgui\Controller\Table\AbstractAction $action
     * @param string $path
     * @return \Nethgui\Controller\TableController
     */
    public function addTableActionPluggable(\Nethgui\Controller\Table\AbstractAction $action, $path = 'Plugin')
    {
        return $this->addTableAction(new \Nethgui\Controller\Table\PluggableAction($action, $path));
    }

    /**
     * Actions for the whole table
     * @return array
     */
    public function getTableActions()
    {
        return $this->tableActions;
    }

    public function renderIndex(\Nethgui\Renderer\Xhtml $renderer)
    {
        $renderer->includeFile('Nethgui/Js/jquery.nethgui.controller.js');
        $this->sortChildren(function(\Nethgui\Module\ModuleInterface $a, \Nethgui\Module\ModuleInterface $b) {
                if ($a->getIdentifier() === 'read') {
                    return -1;
                } elseif ($b->getIdentifier() === 'read') {
                    return 1;
                }
                return 0;
            });

        return parent::renderIndex($renderer);
    }

    /**
     *
     * @param \Nethgui\Module\ModuleInterface $currentAction The current action
     * @param array $changes changed parameters list
     * @param array $parameters Parameters for $currentAction
     */
    public function onParametersSaved(\Nethgui\Module\ModuleInterface $currentAction, $changes, $parameters)
    {
        // NOOP;
    }

}
