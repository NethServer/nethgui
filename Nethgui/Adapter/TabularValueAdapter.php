<?php
namespace Nethgui\Adapter;

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
 * The TabularValueAdapter provide an array interface to access tabular data 
 * encoded into a value stored in a key or prop.
 * 
 * The encoding uses two distinct string separators to divide the logical rows and
 * split each row into logical columns.
 * 
 * Note implementation applies Decorator Pattern to ArrayAdapter
 * 
 * @api
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
class TabularValueAdapter implements \Nethgui\Adapter\AdapterInterface, \ArrayAccess, \IteratorAggregate, \Countable
{

    /**
     * @var ArrayAdapter
     */
    private $innerAdapter;

    /**
     *
     * @var ArrayObject
     */
    private $data;
    private $modified;
    private $columnSeparator;

    public function __construct(ArrayAdapter $innerAdapter, $columnSeparator)
    {
        $this->innerAdapter = $innerAdapter;
        $this->columnSeparator = $columnSeparator;
    }

    private function lazyInitialization()
    {
        $this->data = new \ArrayObject();
        
        foreach ($this->innerAdapter as $rawRow) {
            $row = explode($this->columnSeparator, $rawRow);
            $key = array_shift($row);
            $this->data[$key] = $row;
        }

        $this->modified = FALSE;
    }

    public function count()
    {
        if ( ! isset($this->data)) {
            $this->lazyInitialization();
        }

        return $this->data->count();
    }

    public function delete()
    {
        if ( ! isset($this->data)) {
            $this->lazyInitialization();
        }

        if (count($this->data) != 0) {
            $this->modified = TRUE;
            $this->data = new \ArrayObject();
        }
    }

    public function get()
    {
        if ( ! isset($this->data)) {
            $this->lazyInitialization();
        }

        return $this->data;
    }

    public function set($value)
    {
        $this->data = new \ArrayObject();
        $this->modified = TRUE;

        if ( ! is_array($value) && ! $value instanceof \Traversable) {
            throw new \InvalidArgumentException(sprintf('%s: Value must be an array or a Traversable object!', __CLASS__), 1322149790);
        }

        foreach ($value as $key => $row) {
            if ( ! is_array($row)) {
                throw new \InvalidArgumentException(sprintf('%s: Value must be an array of arrays!', __CLASS__), 1322149791);
            }
            $this[$key] = $row;
        }
    }

    public function save()
    {
        if ( ! $this->isModified()) {
            return FALSE;
        }

        $value = array();

        foreach ($this->data as $key => $row) {
            $value[] = implode($this->columnSeparator, array_merge(array($key), $row));
        }

        $this->innerAdapter->set($value);

        $saved = $this->innerAdapter->save();

        $this->modified = FALSE;

        return $saved;
    }

    public function getIterator()
    {
        if ( ! isset($this->data)) {
            $this->lazyInitialization();
        }

        return $this->data->getIterator();
    }

    public function isModified()
    {
        return $this->modified === TRUE;
    }

    public function offsetExists($offset)
    {
        if ( ! isset($this->data)) {
            $this->lazyInitialization();
        }

        return $this->data->offsetExists($offset);
    }

    public function offsetGet($offset)
    {
        if ( ! isset($this->data)) {
            $this->lazyInitialization();
        }

        return $this->data->offsetGet($offset);
    }

    public function offsetSet($offset, $value)
    {
        if ( ! isset($this->data)) {
            $this->lazyInitialization();
        }

        if ( ! is_array($value) && ! $value instanceof \Traversable) {
            throw new \InvalidArgumentException(sprintf('%s: Value must be an array or a Traversable object!', __CLASS__), 1322149888);
        }

        $this->modified = TRUE;

        $this->data->offsetSet($offset, $value);
    }

    public function offsetUnset($offset)
    {
        if ( ! isset($this->data)) {
            $this->lazyInitialization();
        }

        if ($this->data->offsetExists($offset)) {
            unset($this->data[$offset]);
            $this->modified = TRUE;
        }
    }

}

