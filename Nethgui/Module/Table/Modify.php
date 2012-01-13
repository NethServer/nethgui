<?php
namespace Nethgui\Module\Table;

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
class Modify extends Action
{
    const KEY = 1325671618;
    const FIELD = 1325671619;

    /**
     *
     * @var array
     */
    private $parameterSchema = array();

    /**
     * The name of the key parameter to identify the table adapter record
     * @var string
     */
    private $key;

    /**
     * Values passed into the view in GET/create
     * @var array
     */
    private $createDefaults = array();
    private $template;

    public function __construct($identifier, $parameterSchema = NULL, $viewTemplate = NULL)
    {
        if ( ! in_array($identifier, array('create', 'delete', 'update'))) {
            throw new \InvalidArgumentException(sprintf('%s: Module identifier must be one of `create`, `delete`, `update` values.', get_class($this)), 1322149372);
        }

        parent::__construct($identifier);

        $this->template = $viewTemplate;

        if ( ! is_null($parameterSchema)) {
            $this->setSchema($parameterSchema);
        }
    }

    public function setSchema($parameterSchema)
    {
        $this->parameterSchema = array();
        $this->key = NULL;

        foreach ($parameterSchema as $parameterDeclaration) {
            if (isset($parameterDeclaration[0], $parameterDeclaration[2]) && $parameterDeclaration[2] === self::KEY) {
                $this->key = $parameterDeclaration[0];
                $this->parameterSchema = $parameterSchema;
                return $this;
            }
        }

        throw new \LogicException(sprintf('%s: invalid schema. You must declare a KEY field.', __CLASS__), 1325671156);
    }

    /**
     * We have to declare all the parmeters of parameterSchema here,
     * binding the actual key/row from tableAdapter.
     * @param \Nethgui\Core\RequestInterface $request 
     */
    public function bind(\Nethgui\Core\RequestInterface $request)
    {
        if (is_null($this->tableAdapter)) {
            throw new \LogicException(sprintf('%s: you must setTableAdapter() before bind()', get_class($this)), 1325673694);
        }

        if (is_null($this->key)) {
            throw new \LogicException(sprintf('%s: you must setSchema() before bind()', get_class($this)), 1325673717);
        }

        // The key value is assumed to be the first subpath segment of the request:
        $key = \Nethgui\array_head($request->getPath());

        if ($this->getIdentifier() === 'create') {
            if ($request->isSubmitted()) {
                $key = $request->getParameter($this->key);
                if (isset($this->tableAdapter[$key])) {
                    throw new \Nethgui\Exception\HttpException('Conflict', 409, 1325685280);
                }
            }
        } elseif ( ! isset($this->tableAdapter[$key])) {
            throw new \Nethgui\Exception\HttpException('Not found', 404, 1325672611);
        }

        foreach ($this->parameterSchema as $declarationIndex => $parameterDeclaration) {
            $parameterName = array_shift($parameterDeclaration);
            $validator = array_shift($parameterDeclaration);
            $valueProvider = array_shift($parameterDeclaration);

            if ($valueProvider === self::KEY) {
                $valueProvider = function () use ($key) {
                        return $key;
                    };
            } elseif ($valueProvider === self::FIELD) {
                $prop = array_shift($parameterDeclaration);
                $separator = array_shift($parameterDeclaration);
                // Null prop name falls back into parameterName:
                if (is_null($prop)) {
                    $prop = $parameterName;
                }

                $valueProvider = array($this->tableAdapter, $key, $prop, $separator);
            }

            $this->declareParameter($parameterName, $validator, $valueProvider);
        }

        parent::bind($request);

        if ( ! $request->isSubmitted()
            && $this->getIdentifier() === 'create') {
            foreach ($this->createDefaults as $paramName => $paramValue) {
                $this->parameters[$paramName] = $paramValue;
            }
        }
    }

    public function process()
    {
        if ( ! $this->getRequest()->isSubmitted()) {
            return;
        }

        $action = $this->getIdentifier();
        $key = $this->parameters[$this->key];

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
        $save2 = $this->tableAdapter->save();
        if ($save1 || $save2) {
            $this->onParametersSaved($changes);
            $this->getParent()->onParametersSaved($this, $changes);
        }
    }

    protected function processDelete($key)
    {
        if (isset($this->tableAdapter[$key])) {
            unset($this->tableAdapter[$key]);
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
        if (isset($this->template)) {
            $view->setTemplate($this->template);
        }
        if ($view->getTargetFormat() === $view::TARGET_XHTML) {
            $view['__key'] = $this->key;
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
