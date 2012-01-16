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
class TableController extends \Nethgui\Module\Controller
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

    protected function setTableAdapter(\Nethgui\Adapter\AdapterInterface $tableAdapter)
    {
        $this->tableAdapter = $tableAdapter;

        /*
         * Propagate the table adapter object to every children, if
         * it has not been done before by addChild()
         */
        $actions = array_merge(array($this->readAction), $this->rowActions, $this->tableActions);

        foreach ($actions as $action) {
            if ( ! $action instanceof \Nethgui\Module\Table\TableActionInterface || $action->hasTableAdapter()) {
                continue;
            }

            $action->setTableAdapter($this->tableAdapter);
        }

        return $this;
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
        if (is_null($this->tableAdapter)) {
            throw new \LogicException(sprintf('%s: call setTableAdapter() before %s::initialize()', get_class($this), __CLASS__), 1325610869);
        }

        /**
         * Calling the parent method at this point ensures that the table
         * adapter has been set BEFORE the child initialization
         */
        parent::initialize();
    }

    /**
     * Add a child setting its table adapter, if the child is an instance
     * of ActionInterface.
     *
     * @see \Nethgui\Module\Table\TableActionInterface
     * @param \Nethgui\Module\ModuleInterface $childModule
     * @return TableController
     */
    public function addChild(\Nethgui\Module\ModuleInterface $childModule)
    {
        parent::addChild($childModule);
        if ( ! is_null($this->tableAdapter)
            && $childModule instanceof \Nethgui\Module\Table\TableActionInterface
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
    public function addRowAction(\Nethgui\Module\ModuleInterface $a)
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
    public function addTableAction(\Nethgui\Module\ModuleInterface $a)
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

    public function renderIndex(\Nethgui\Renderer\Xhtml $renderer)
    {
        $renderer->includeFile('jquery.nethgui.controller.js', 'Nethgui');
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

    public function onParametersSaved(\Nethgui\Module\ModuleInterface $currentAction, $changes)
    {
        // NOOP;
    }

}
