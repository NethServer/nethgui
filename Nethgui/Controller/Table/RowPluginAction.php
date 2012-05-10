<?php
namespace Nethgui\Controller\Table;

/*
 * Copyright (C) 2012 Nethesis S.r.l.
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
 * Extends an action parameter schema with additional parameters, mapped
 * to the record adapter props.
 * 
 * Collaborates with PluginCollector, 
 * - by extending the original action parameter schema.
 * - by receiving the actual record key value from it.
 * 
 * Usage info:
 * implement this class and invoke setSchemaAddition() before the parent class 
 * initialization.
 *
 * @api
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
abstract class RowPluginAction extends RowAbstractAction
{
    /**
     *
     * @var array
     */
    private $schemaAddition = array();

    /**
     * Extend the pluggable parent's parameter schema
     * 
     * @api 
     * @param array $parameterSchema
     * @return \Nethgui\Controller\Table\RowPluginAction 
     */
    protected function setSchemaAddition($parameterSchema)
    {
        $this->schemaAddition = $parameterSchema;
        return $this;
    }

    /**
     * Get the parents' paramter extension applied by this plugin
     * 
     * @api
     * @return array
     */
    protected function getSchemaAddition()
    {
        return $this->schemaAddition;
    }

    /**
     * Return the extended parameter schema used by this plugin
     * 
     * @api
     * @see getSchemaAddition()
     * @return array
     */
    public function getSchema()
    {
        return array_merge(parent::getSchema(), $this->getSchemaAddition());
    }

    /**
     * The module identifier of the action which we are adding plugins to
     *      
     * @api
     * @return string
     */
    public function getPluggableActionIdentifier()
    {
        return $this->getParent()->getParent()->getIdentifier();
    }
    
    /**
     * Get the parameter name containing the record key, querying the 
     * pluggable parent
     * 
     * @see \Nethgui\Controller\Table\PluggableAction
     * @api
     * @return string
     */
    public function getKey() {
        return $this->getParent()->getParent()->getKey();
    }

}