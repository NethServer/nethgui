<?php
namespace Nethgui\Serializer;

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
 * Transfers a prop value to/from an object implementing ArrayAccess interface
 *
 * @see \Nethgui\Module\Table\Modify
 */
class ArrayAccessSerializer implements SerializerInterface
{

    private $prop;
    private $key;
    /**
     *
     * @var \ArrayAccess
     */
    private $table;

    public function __construct(\ArrayAccess $table, $key, $prop)
    {
        $this->table = $table;
        $this->key = $key;

        if (is_null($prop) || $prop == '' || $prop === FALSE) {
            throw new InvalidArgumentException('The `prop` argument is invalid');
        }

        $this->prop = strval($prop);
    }

    public function read()
    {
        if ( ! $this->table->offsetExists($this->key)) {
            return NULL;
        }

        $record = $this->table->offsetGet($this->key);
        if ( ! isset($record[$this->prop])) {
            return NULL;
        }
        return $record[$this->prop];
    }

    public function write($value)
    {
        if ( ! isset($this->key)) {
            throw new \Nethgui\Exception\Serializer('The TablePropSerializer `key` is not missing.');
        }

        // update or append ?
        if ($this->table->offsetExists($this->key)) {
            $record = $this->table->offsetGet($this->key);
        } else {
            $record = array();
        }

        $record[$this->prop] = $value;
        $this->table->offsetSet($this->key, $record);
    }

}
