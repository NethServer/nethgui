<?php
namespace Nethgui\Adapter;

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
 * Handles record-like operations for a database.
 * 
 * The internal value is an hash of values
 * 
 * States: 
 * NEW, CLEAN, MODIFIED:{DELETED, DIRTY}
 * 
 * Transactions:
 * CREATED -> CLEAN
 * CLEAN -> DIRTY -> DELETED
 * CLEAN -> DELETED
 * DIRTY -> CLEAN
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
class RecordAdapter implements \Nethgui\Adapter\AdapterInterface, \ArrayAccess, \IteratorAggregate
{
    const CREATED = 0;
    const CLEAN = 1;
    const DIRTY = 2;
    const DELETED = 3;

    /**
     *
     * @var \Nethgui\Adapter\TableAdapter
     */
    protected $tableAdapter;

    /**
     *
     * @var string
     */
    private $keyValue = NULL;

    /**
     *
     * @var bool
     */
    private $state = 0;

    /**
     *
     * @var \ArrayObject
     */
    private $data = NULL;

    public function __construct(\Nethgui\Adapter\TableAdapter $tableAdapter)
    {
        $this->tableAdapter = $tableAdapter;
    }

    public function setKeyValue($value)
    {
        if (isset($this->keyValue)) {
            throw new \LogicException(sprintf('%s: the record key is already set', __CLASS__), 1336463530);
        }

        $this->keyValue = $value;
        return $this;
    }

    public function getKeyValue()
    {
        return $this->keyValue;
    }

    public function delete()
    {
        $this->state = self::DELETED;
    }

    public function get()
    {
        if (is_null($this->getKeyValue())) {
            return NULL;
        }

        if ($this->state === self::CREATED) {
            $this->lazyInitialization();
        }

        return $this->data;
    }

    public function isModified()
    {
        return $this->state === self::DIRTY || $this->state === self::DELETED;
    }

    public function save()
    {
        if ( ! $this->isModified()) {
            return FALSE;
        }

        if (is_null($this->getKeyValue())) {
            throw new \LogicException(sprintf('%s: you must setKeyValue() before save().', __CLASS__), 1336388582);
        }

        if ($this->state === self::DELETED) {
            $this->tableAdapter->offsetUnset($this->getKeyValue());
        } else {
            $current = $this->tableAdapter->offsetGet($this->getKeyValue());

            if (is_null($current)) {
                $current = new \ArrayObject();
            }

            // transfer our field values into the table adapter:

            foreach ($this->data as $key => $value) {
                $current[$key] = $value;
            }

            $this->tableAdapter->offsetSet($this->getKeyValue(), $current);
        }
        
        $this->state = self::CLEAN;
    }

    public function set($value)
    {
        if (is_array($value)) {
            $value = new \ArrayObject($value);
        }

        if ( ! $value instanceof Traversable) {
            throw new \InvalidArgumentException(sprintf('%s: the $value must be an array or an instance of Traversable interface', __CLASS__), 1336388598);
        }

        foreach ($value as $propName => $propValue) {
            $this->offsetSet($propName, $propValue);
        }
    }

    public function offsetExists($offset)
    {
        if ($this->state === self::CREATED) {
            $this->lazyInitialization();
        }

        return $this->data->offsetExists($offset);
    }

    public function offsetGet($offset)
    {
        if ($this->state === self::CREATED) {
            $this->lazyInitialization();
        }

        return $this->data->offsetGet($offset);
    }

    public function offsetSet($offset, $value)
    {
        if ( ! $this->offsetExists($offset) ||
            $this->offsetGet($offset) !== $value) {
            $this->data->offsetSet($offset, $value);
            $this->state = self::DIRTY;
        }
    }

    public function offsetUnset($offset)
    {
        if (is_null($this->getKeyValue())) {
            return;
        }

        if ($this->state === self::CREATED) {
            $this->lazyInitialization();
        }

        $this->data->offsetUnset($offset);
    }

    public function getIterator()
    {
        if ($this->state === self::CREATED) {
            $this->lazyInitialization();
        }

        return $this->data->getIterator();
    }

    private function lazyInitialization()
    {
        $keyValue = $this->getKeyValue();

//        if ($keyValue === NULL) {
//            throw new \LogicException(sprintf('%s: the key value must be set before initializing the object', __CLASS__), 1336490797);
//        }

        if ( ! $keyValue === NULL && $this->tableAdapter->offsetExists($keyValue)) {
            $current = $this->tableAdapter->offsetGet($keyValue);
        } else {
            $current = NULL;
        }

        if (is_null($current)) {
            $current = new \ArrayObject();
        }

        if (is_null($this->data)) {
            $this->data = new \ArrayObject($current);
            $this->state = self::CLEAN;
        } else {
            // transfer DB fields into the object storage. Record state
            // is unchanged:
            foreach ($current as $key => $value) {
                if ( ! $this->data->offsetExists($key)) {
                    $this->data->offsetSet($key, $value);
                }
            }
        }
    }

}
