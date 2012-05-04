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
 * Table action that works on a database record identified by a key
 * 
 * Parameters are decleared in bind(), after the key value is known. Clients
 * must provide a parameter schema, before bind() is called. See setSchema().
 * 
 * Implement the way a key value is obtained from the request context.
 * See getKeyValue().
 *
 * @api
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
abstract class RowAbstractAction extends \Nethgui\Controller\Table\AbstractAction
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
     * The name of the key parameter to identify the table adapter record
     * 
     * @param string $parameterName
     * @return \Nethgui\Controller\Table\RowAbstractAction The object itself
     */
    private function setKey($parameterName)
    {
        $this->key = $parameterName;
        return $this;
    }

    /**
     *
     * @api
     * @return string The key parameter name
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Calculate the key value according to the given $request object
     * 
     * @api
     * @param \Nethgui\Controller\RequestInterface $request the incoming request
     * @return string the key parameter value
     */
    abstract public function getKeyValue(\Nethgui\Controller\RequestInterface $request);

    public function bind(\Nethgui\Controller\RequestInterface $request)
    {
        $tableAdapter = $this->getAdapter();

        if (is_null($tableAdapter)) {
            throw new \LogicException(sprintf('%s: you must setTableAdapter() on %s before bind().', __CLASS__, $this->getParent()->getIdentifier()), 1325673694);
        }

        if (is_null($this->getKey())) {
            throw new \LogicException(sprintf('%s: unknown key field name.', get_class($this)), 1325673717);
        }

        $keyValue = $this->getKeyValue($request);

        foreach ($this->parameterSchema as $parameterDeclaration) {
            $parameterName = array_shift($parameterDeclaration);
            $validator = array_shift($parameterDeclaration);
            $valueProvider = array_shift($parameterDeclaration);

            if ($valueProvider === self::KEY) {
                $valueProvider = function () use ($keyValue) {
                        return $keyValue;
                    };
            } elseif ($valueProvider === self::FIELD) {
                $prop = array_shift($parameterDeclaration);
                $separator = array_shift($parameterDeclaration);
                // Null prop name falls back into parameterName:
                if (is_null($prop)) {
                    $prop = $parameterName;
                }

                $valueProvider = array($tableAdapter, $keyValue, $prop, $separator);
            }

            $this->declareParameter($parameterName, $validator, $valueProvider);
        }

        parent::bind($request);
    }

    /**
     * Set the mapping between the view parameters and the underlying datasource
     * 
     * The array is a list of array of arguments to declareParameter(). A 
     * special processing is added to the third parameter, $valueProvider:
     * 
     * - the KEY const is replaced with an anonymous function providing the 
     *   actual key value;
     * 
     * - the FIELD const binds the parameter with the corresponding prop. If 
     *   the parameter name differs from the prop name add a 4th, corresponding
     *   to the prop name.
     * 
     * @api
     * @param array $parameterSchema
     * @return \Nethgui\Controller\Table\RowAbstractAction The object itself
     * @throws \LogicException 
     */
    public function setSchema($parameterSchema)
    {
        $this->parameterSchema = array();

        foreach ($parameterSchema as $parameterDeclaration) {
            if (isset($parameterDeclaration[0], $parameterDeclaration[2]) && $parameterDeclaration[2] === self::KEY) {
                $this->setKey($parameterDeclaration[0]);
                $this->parameterSchema = $parameterSchema;
                return $this;
            }
        }

        throw new \LogicException(sprintf('%s: invalid schema. You must declare a KEY field.', __CLASS__), 1325671156);
    }

}
