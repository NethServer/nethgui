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
     * Create a Modify instance that realizes one of the allowed behaviours:
     * 
     * - create
     * - delete
     * - update
     * 
     * @param string $identifier One of 'create', 'delete', 'update'
     * @param array $parameterSchema - DEPRECATED
     * @param mixed $viewTemplate - DEPRECATED 
     * @throws \InvalidArgumentException 
     */
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

    /**
     * Establish what is the key value, then invoke setKeyValue() on the 
     * RecordAdapter BEFORE parent::bind()
     * 
     * @param \Nethgui\Controller\RequestInterface $request 
     */
    public function bind(\Nethgui\Controller\RequestInterface $request)
    {
        if ($this->getIdentifier() === 'create') {
            if ($request->isMutation()) {
                $keyValue = $this->calculateKeyFromRequest($request);
            } else {
                $keyValue = FALSE;
            }
        } else {
            $keyValue = \Nethgui\array_head($request->getPath());
        }

        if (is_null($this->getAdapter()->getKeyValue())) {
            $this->getAdapter()->setKeyValue($keyValue);
        }

        parent::bind($request);

        // ensure Path and Parameter value are the same
        $this->getValidator($this->getKey())->equalTo($keyValue);
    }

    /**
     * Calculate the key value for a new record from the given $request 
     * object.
     * 
     * Override this function whenever the key value requires some kind of 
     * processing.  
     * 
     * This implementation returns the value of the key parameter.
     * 
     * @api
     * @param \Nethgui\Controller\RequestInterface $request
     * @return string
     */
    protected function calculateKeyFromRequest(\Nethgui\Controller\RequestInterface $request)
    {
        return $request->getParameter($this->getKey());
    }

    public function process()
    {
        if ( ! $this->getRequest()->isMutation()) {
            return;
        }

        $action = $this->getIdentifier();
        $key = $this->getAdapter()->getKeyValue();

        if ($action == 'delete') {
            $this->processDelete($key);
        } elseif ($action == 'create') {
            $this->processCreate($key);
        } elseif ($action == 'update') {
            $this->processUpdate($key);
        } else {
            throw new \Nethgui\Exception\HttpException('Not found', 404, 1322148408);
        }

        $changes = $this->parameters->getModifiedKeys();

        if ($this->saveParameters()) {
            $this->onParametersSaved($changes);
            $this->getParent()->onParametersSaved($this, $changes, $this->parameters->getArrayCopy());
        }
    }

    protected function processDelete($key)
    {
        $this->getAdapter()->delete();
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
        foreach ($defaultValues as $parameterName => $value) {
            $this->setDefaultValue($parameterName, $value);
        }
        return $this;
    }

    /**
     * Return to default "read" action
     *
     * @return string 'read'
     */
    public function nextPath()
    {
        return 'read';
    }

}
