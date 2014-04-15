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
 * Table adapter provide an array like access to all database keys of a given type
 *
 */
class TableAdapter implements AdapterInterface, \ArrayAccess, \IteratorAggregate, \Countable
{
    /**
     *
     * @var \Nethgui\System\EsmithDatabase
     */
    private $database;
    private $type;

    /**
     *
     * @var ArrayObject
     */
    private $data;

    /**
     *
     * @var ArrayObject
     */
    private $changes;

    /**
     *
     * @var callable
     */
    private $filter = NULL;

    /**
     *
     * The $filter argument can be
     * - NULL, no filter is applied
     * - an array in the form ('prop1'=>'val1',...,'propN'=>'valN') where
     *   valN it's a regexp. In this case, the adapter will return only rows
     *   where all props match all associated regexp.
     * - a callable, filter is invoked on each row and must return a TRUE/FALSE
     *   value that determines if a record is accepted (TRUE) or not (FALSE).
     *   The filter prototype is (bool) filter($row) {};
     *
     * @param string $db used for table mapping
     * @param mixed $types The types of records to load. Can be a string or an array or NULL.
     * @param mixed $filter Can be NULL, an associative array, or callable.
     * @throws \InvalidArgumentException
     *
     */
    public function __construct(\Nethgui\System\DatabaseInterface $db, $types = NULL, $filter = NULL)
    {
        $this->database = $db;
        $this->type = $types;
        // Fix wrong filter datatype, for backward compatibility
        $filter = $filter === FALSE ? NULL : $filter;
        $this->filter = is_array($filter) ? array($this, 'defaultFilterMatch') : $filter;
        if( ! ($this->filter === NULL || is_callable($this->filter))) {
            throw new \InvalidArgumentException(sprintf("%s: filter argument must be an array or NULL, or a callable object.", __CLASS__), 1398679653);
        }
    }

    public function defaultFilterMatch($value)
    {
        foreach ($this->filter as $prop => $regexp) {
            if ( ! preg_match($regexp, $value[$prop])) {
                return false;
            }
        }
        return true;
    }

    private function lazyInitialization()
    {
        $this->data = new \ArrayObject();
        $this->changes = new \ArrayObject();
       
        $rawData = $this->database->getAll($this->type);
        if ( ! is_array($rawData)) {
            return;
        }
            // skip the first column, where getAll() returns the key type.
        foreach ($rawData as $key => $row) {
            if ($this->type !== NULL) {
                unset($row['type']);
            }
            if ($this->filter === NULL) {
                $this->data[$key] = new \ArrayObject($row);
            } elseif (call_user_func($this->filter, $row)) {
                $this->data[$key] = new \ArrayObject($row);
            }
        }
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

        foreach (array_keys($this->data->getArrayCopy()) as $key) {
            unset($this[$key]);
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
        if ( ! is_array($value) && ! $value instanceof \Traversable) {
            throw new \InvalidArgumentException(sprintf('%s: Value must be an array!', __CLASS__), 1322149788);
        }

        if ( ! isset($this->data)) {
            $this->lazyInitialization();
        }

        foreach ($value as $key => $props) {
            if (is_array($props)) {
                $props = new \ArrayObject($props);
            }
            $this[$key] = $props;
        }
    }

    public function save()
    {
        if ( ! $this->isModified()) {
            return 0;
        }

        $saveCount = 0;

        foreach ($this->changes as $args) {
            $method = array_shift($args);
            call_user_func_array(array($this->database, $method), $args);
            $saveCount ++;
        }

        $this->changes = new \ArrayObject();

        $this->modified = FALSE;

        return $saveCount;
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
        return $this->changes instanceof \ArrayObject && count($this->changes) > 0;
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

        if (is_array($value)) {
            $value = new \ArrayObject($value);
        } elseif ( ! $value instanceof \Traversable) {
            throw new \InvalidArgumentException(sprintf('%s: Value must be an array!', __CLASS__), 1322149789);
        }

        if (isset($this[$offset])) {
            $this->changes[] = array('setProp', $offset, iterator_to_array($value));
        } elseif (isset($this->type)) {
            $this->changes[] = array('setKey', $offset, $this->type, iterator_to_array($value));
        } elseif (isset($value['type'])) {
            $props = iterator_to_array($value);
            unset($props['type']);
            $this->changes[] = array('setKey', $offset, $value['type'], $props);
        } else {
            throw new \LogicException(sprintf('%s: New record `type` was not specified!', __CLASS__), 1397569441);
        }

        $this->data->offsetSet($offset, $value);
    }

    public function offsetUnset($offset)
    {
        if ( ! isset($this->data)) {
            $this->lazyInitialization();
        }

        unset($this->data[$offset]);
        $this->changes[] = array('deleteKey', $offset);
    }

}