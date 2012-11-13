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
 * Array adapter maps a PHP array-like interface to a key or prop value
 * where values are separated by a separator character.
 *
 * @api
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
class ArrayAdapter implements AdapterInterface, \ArrayAccess, \IteratorAggregate, \Countable
{
    /**
     * The character used as separator to encode/decode the array string value.
     * @var string
     */
    private $separator;

    /**
     * This boolean is indeed a tri-state value, where NULL indicates
     * that object state is uninitialized.
     * @var boolean
     */
    private $modified;

    /**
     * Keeps the array values.
     * @var ArrayObject
     */
    private $data;

    /**
     *
     * @var \Nethgui\Serializer\SerializerInterface
     */
    private $serializer;

    public function __construct($separator, \Nethgui\Serializer\SerializerInterface $serializer)
    {
        $this->separator = $separator;
        $this->serializer = $serializer;
    }

    public function get()
    {
        if (is_null($this->modified)) {
            $this->lazyInitialization();
        }

        if (is_null($this->data)) {
            return NULL;
        }

        return $this->data;
    }

    public function set($value)
    {

        if ( ! (is_array($value) || empty($value))) {
            throw new \InvalidArgumentException(sprintf('%s: Invalid data type. Expected `array` or `EMPTY`, was `%s`', get_class($this), gettype($value)), 1322148826);
        }

        if (is_null($this->modified)) {
            $this->lazyInitialization();
        }

        // handle set-NULL
        if ($value === NULL) {
            if ($this->data === NULL) {
                // nothing to do
            } else {
                $this->modified = TRUE;
                $this->data = NULL;
            }
            return;
        }

        // empty string translated to empty array
        if ($value === '') {
            $value = array();
        }

        if (is_null($this->data)) {
            $this->data = new \ArrayObject($value);
            $this->modified = TRUE;
        } elseif ($this->data->getArrayCopy() !== $value) {
            $this->data->exchangeArray($value);
            $this->modified = TRUE;
        }
    }

    public function delete()
    {
        $this->set(NULL);
    }

    public function isModified()
    {
        return $this->modified === TRUE;
    }

    public function save()
    {
        if ( ! $this->isModified()) {
            return FALSE;
        }

        if (is_object($this->data)) {
            $value = implode($this->separator, $this->data->getArrayCopy());
        } else {
            $value = NULL;
        }

        $this->serializer->write($value);

        $this->modified = FALSE;

        return TRUE;
    }

    public function count()
    {
        if (is_null($this->modified)) {
            $this->lazyInitialization();
        }

        if (is_null($this->data)) {
            return 0;
        }

        return count($this->data);
    }

    public function getIterator()
    {
        if (is_null($this->modified)) {
            $this->lazyInitialization();
        }

        if (is_null($this->data)) {
            return new \ArrayIterator(array());
        }

        return $this->data->getIterator();
    }

    public function offsetExists($offset)
    {
        if (is_null($this->modified)) {
            $this->lazyInitialization();
        }
        return is_object($this->data) && $this->data->offsetExists($offset);
    }

    public function offsetGet($offset)
    {
        if (is_null($this->modified)) {
            $this->lazyInitialization();
        }

        if ($this->offsetExists($offset)) {
            return $this->data[$offset];
        }

        return NULL;
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($this->modified)) {
            $this->lazyInitialization();
        }

        if (is_null($this->data)) {
            $this->data = new \ArrayObject();
        }

        $this->data[$offset] = $value;
        $this->modified = TRUE;
    }

    public function offsetUnset($offset)
    {
        if (is_null($this->modified)) {
            $this->lazyInitialization();
        }

        if (isset($this->data[$offset])) {
            unset($this->data[$offset]);
            $this->modified = TRUE;
        }
    }

    private function lazyInitialization()
    {
        $value = $this->serializer->read();

        if (is_null($value)) {
            $this->data = NULL;
        } elseif ($value === '') {
            $this->data = new \ArrayObject();
        } else {
            $this->data = new \ArrayObject(explode($this->separator, $value));
        }

        $this->modified = FALSE;
    }

}
