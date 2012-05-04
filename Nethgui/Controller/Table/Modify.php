<?php
namespace Nethgui\Controller\Table;

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
 * Processes the table modification actions: create, update, delete
 *
 * @see Read
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 * @api
 */
class Modify extends \Nethgui\Controller\Table\RowAbstractAction
{
    /**
     * Values passed into the view in GET/create
     * @var array
     */
    private $createDefaults = array();

    public function __construct($identifier, $parameterSchema = NULL, $viewTemplate = NULL)
    {
        if ( ! in_array($identifier, array('create', 'delete', 'update'))) {
            throw new \InvalidArgumentException(sprintf('%s: Module identifier must be one of `create`, `delete`, `update` values.', get_class($this)), 1322149372);
        }

        parent::__construct($identifier);

        if ( ! is_null($viewTemplate)) {
            $this->setViewTemplate($viewTemplate);
        }

        if ( ! is_null($parameterSchema)) {
            $this->setSchema($parameterSchema);
        }
    }

    public function getKeyValue(\Nethgui\Controller\RequestInterface $request)
    {
        // The key value is assumed to be the first subpath segment of the request:
        $key = \Nethgui\array_head($request->getPath());
        $tableAdapter = $this->getAdapter();

        if ($this->getIdentifier() === 'create') {
            if ($request->isMutation()) {
                $key = $request->getParameter($this->getKey());
            }
        } elseif (is_null($tableAdapter) || ! isset($tableAdapter[$key])) {
            throw new \Nethgui\Exception\HttpException('Not found', 404, 1325672611);
        }

        return $key;
    }

    /**
     * We have to declare all the parmeters of parameterSchema here,
     * binding the actual key/row from tableAdapter.
     * @param \Nethgui\Controller\RequestInterface $request 
     */
    public function bind(\Nethgui\Controller\RequestInterface $request)
    {
        parent::bind($request);

        if ( ! $request->isMutation()
            && $this->getIdentifier() === 'create') {
            foreach ($this->createDefaults as $paramName => $paramValue) {
                $this->parameters[$paramName] = $paramValue;
            }
        }
    }

    public function validate(\Nethgui\Controller\ValidationReportInterface $report)
    {
        parent::validate($report);

        $request = $this->getRequest();
        if ($this->getIdentifier() === 'create' && $request->isMutation()) {
            $tableAdapter = $this->getAdapter();
            if (isset($tableAdapter[$this->parameters[$this->getKey()]])) {
                $report->addValidationErrorMessage($this, $this->getKey(), 'An object with the same key already exists');
            }
        }
    }

    public function process()
    {
        if ( ! $this->getRequest()->isMutation()) {
            return;
        }

        $action = $this->getIdentifier();
        $key = $this->parameters[$this->getKey()];

        if ($action == 'delete') {
            $this->processDelete($key);
        } elseif ($action == 'create') {
            $this->processCreate($key);
        } elseif ($action == 'update') {
            $this->processUpdate($key);
        } else {
            throw new \Nethgui\Exception\HttpException('Not found', 404, 1322148408);
        }

        // Transfer all parameters values into tableAdapter (and DB):
        $changes = $this->parameters->getModifiedKeys();

        $save1 = $this->parameters->save();
        $save2 = $this->getAdapter()->save();
        if ($save1 || $save2) {
            $this->onParametersSaved($changes);
            $this->getParent()->onParametersSaved($this, $changes, $this->parameters->getArrayCopy());
        }
    }

    protected function processDelete($key)
    {
        $tableAdapter = $this->getAdapter();
        if (isset($tableAdapter[$key])) {
            unset($tableAdapter[$key]);
        } else {
            throw new \RuntimeException(sprintf('%s: Cannot delete `%s`.', get_class($this), $key), 1322148216);
        }
    }

    protected function processCreate($key)
    {
        
    }

    protected function processUpdate($key)
    {
        
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);
        if ($view->getTargetFormat() === $view::TARGET_XHTML) {
            $view['__key'] = $this->getKey();
        }
    }

    /**
     * Set the default parameter values in "create" action
     *
     * Call before bind()
     *
     * @param array $defaultValues
     * @return Modify
     */
    public function setCreateDefaults($defaultValues)
    {
        $this->createDefaults = $defaultValues;
        return $this;
    }

}
