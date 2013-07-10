<?php
namespace Nethgui\Adapter;

/*
 * Copyright (C) 2013 Nethesis S.r.l.
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
 * Access to an array-like object on demand using the lazy loader pattern.
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
class LazyLoaderAdapter implements \Nethgui\Adapter\AdapterInterface, \ArrayAccess, \Countable, \IteratorAggregate
{
    /**
     *
     * @var \ArrayObject
     */
    protected $data;
    private $loader;

    /**
     *
     * @param callable $loader
     * @throws \InvalidArgumentException
     */
    public function __construct($loader = NULL)
    {
        if ( ! is_callable($loader) && $loader !== NULL) {
            throw new \InvalidArgumentException(sprintf("%s: must provide a callable argument", __CLASS__), 1373466604);
        }

        $this->loader = $loader;
    }

    public function isModified()
    {
        return FALSE;
    }

    public function get()
    {
        if ( ! isset($this->data)) {
            $this->lazyInitialization();
        }

        return $this->data;
    }

    public function offsetExists($offset)
    {
        if ( ! isset($this->data)) {
            $this->lazyInitialization();
        }

        return isset($this->data[$offset]);
    }

    public function offsetGet($offset)
    {
        if ( ! isset($this->data)) {
            $this->lazyInitialization();
        }

        return $this->data[$offset];
    }

    public function count()
    {
        if ( ! isset($this->data)) {
            $this->lazyInitialization();
        }

        return $this->data->count();
    }

    public function getIterator()
    {
        if ( ! isset($this->data)) {
            $this->lazyInitialization();
        }
        return $this->data->getIterator();
    }

    public function offsetSet($offset, $value)
    {
        throw new \LogicException(sprintf("%s: read-only adapter, %s() method is not allowed", __CLASS__, __METHOD__), 1373466605);
    }

    public function offsetUnset($offset)
    {
        throw new \LogicException(sprintf("%s: read-only adapter, %s() method is not allowed", __CLASS__, __METHOD__), 1373466606);
    }

    public function save()
    {
        throw new \LogicException(sprintf("%s: read-only adapter, %s() method is not allowed", __CLASS__, __METHOD__), 1373466607);
    }

    public function set($value)
    {
        throw new \LogicException(sprintf("%s: read-only adapter, %s() method is not allowed", __CLASS__, __METHOD__), 1373466608);
    }

    public function delete()
    {
        throw new \LogicException(sprintf("%s: read-only adapter, %s() method is not allowed", __CLASS__, __METHOD__), 1373466609);
    }

    private function lazyInitialization()
    {
        if ($this->loader === NULL) {
            $this->data = new \ArrayObject();
            return;
        }
        $this->data = call_user_func($this->loader);
    }

}