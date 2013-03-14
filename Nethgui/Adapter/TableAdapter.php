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
     * @param string $db used for table mapping
     * @param string $type of the key for mapping
     * @param mixed $filter Can be a string or an associative array. When using a string, filter is a fulltext search on db keys, otherwise it's an array in the form ('prop1'=>'val1',...,'propN'=>'valN') where valN it's a regexp. In this case, the adapter will return only rows where all props match all associated regexp.
     *
     */
    public function __construct(\Nethgui\System\DatabaseInterface $db, $type, $filter = FALSE)
    {
        $this->database = $db;
        $this->type = $type;
        $this->filter = $filter;
    }

    private function filterMatch($value)
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

        if (is_array($this->filter)) { #apply simple filter only if filter is a string
            $rawData = $this->database->getAll($this->type);
            if (is_array($rawData)) {
                // skip the first column, where getAll() returns the key type.
                foreach ($rawData as $key => $row) {
                    unset($row['type']);
                    if ($this->filterMatch($row)) {
                        $this->data[$key] = new \ArrayObject($row);
                    }
                }
            }
        } else {
            $rawData = $this->database->getAll($this->type);
            foreach ($rawData as $key => $row) {
                unset($row['type']);
                $this->data[$key] = new \ArrayObject($row);
            }
        }

        $this->changes = new \ArrayObject();
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

        if ( ! is_array($value) && ! $value instanceof \Traversable) {
            throw new \InvalidArgumentException(sprintf('%s: Value must be an array!', __CLASS__), 1322149789);
        }

        if (isset($this[$offset])) {
            $this->changes[] = array('setProp', $offset, $value);
        } else {
            $this->changes[] = array('setKey', $offset, $this->type, $value);
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
