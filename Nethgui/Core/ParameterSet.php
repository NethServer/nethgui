<?php
namespace Nethgui\Core;

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
 * Holds primitive and adapter-embedded values.
 * 
 * It propagates the save() message to all the members of the set.  
 * Inside a ParameterSet you can store:
 *
 * - Primitive values
 * - Adapter objects
 * - Other objects implementing AdapterAggregationInterface
 *
 */
class ParameterSet implements \Nethgui\Adapter\AdapterAggregationInterface, \ArrayAccess, \Iterator, \Countable
{

    private $data = array();

    /**
     * The number of members of this set.
     * @return integer
     */
    public function count()
    {
        return count($this->data);
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->data);
    }

    public function getKeys()
    {
        return array_keys($this->data);
    }

    public function offsetGet($offset)
    {
        if ( ! $this->offsetExists($offset)) {
            trigger_error('Undefined offset `' . $offset . '`', E_USER_NOTICE);
            return NULL;
        }

        if ($this->data[$offset] instanceof \Nethgui\Adapter\AdapterInterface) {
            $value = $this->data[$offset]->get();
        } else {
            $value = $this->data[$offset];
        }

        return $value;
    }

    public function offsetSet($offset, $value)
    {
        if (isset($this->data[$offset]) && $this->data[$offset] instanceof \Nethgui\Adapter\AdapterInterface) {
            $this->data[$offset]->set($value);
        } else {
            $this->data[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        if ($this->data[$offset] instanceof \Nethgui\Adapter\AdapterInterface) {
            $this->data[$offset]->delete();
        }
        unset($this->data[$offset]);
    }

    /**
     * Saves aggregated values into database, 
     * forwarding the call to Adapters and Sets.
     *
     * This is an helper function.
     * @see \Nethgui\Adapter\AdapterAggregationInterface::save()
     * @return integer The number of saved parameters. A zero value indicates that nothing has been saved.
     */
    public function save()
    {
        $saveCounter = 0;

        foreach ($this->data as $value) {
            if ($value instanceof \Nethgui\Adapter\AdapterInterface) {
                $saveCounter += $value->save();
            } elseif ($value instanceof \Nethgui\Adapter\AdapterAggregationInterface) {
                $saveCounter += $value->save();
            }
        }

        return $saveCounter;
    }

    public function register(\Nethgui\Adapter\AdapterInterface $adapter, $key)
    {
        $this->data[$key] = $adapter;
    }

    public function query($key)
    {
        if ( ! $this->data[$key] instanceof \Nethgui\Adapter\AdapterInterface) {
            return NULL;
        }
        return $this->data[$key];
    }

    public function isModified($key = NULL)
    {
        if (is_null($key)) {
            $keys = array_keys($this->data);
        } else {
            $keys = array($key);
        }

        foreach ($keys as $key) {
            $value = $this->data[$key];

            if ($value instanceof \Nethgui\Adapter\AdapterInterface) {
                $modified = $value->isModified();
            } elseif ($value instanceof \Nethgui\Adapter\AdapterAggregationInterface) {
                $modified = $value->isModified(NULL);
            }

            if ($modified === TRUE) {
                return TRUE;
            }
        }

        return FALSE;
    }

    public function current()
    {
        return $this->offsetGet(key($this->data));
    }

    public function key()
    {
        return key($this->data);
    }

    public function next()
    {
        next($this->data);
    }

    public function rewind()
    {
        reset($this->data);
    }

    public function valid()
    {
        return key($this->data) !== NULL;
    }

    /**
     * Converts the current instance to an array in the form key => value.
     * @return array
     */
    public function getArrayCopy()
    {
        $a = array();

        foreach ($this as $key => $value) {
            $a[$key] = $value;
        }

        return $a;
    }

}
