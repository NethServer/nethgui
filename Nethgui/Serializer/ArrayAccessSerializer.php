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
 * Serialize a scalar value into a N dimensional array.
 * 
 * The first dimension must be an object with ArrayAccess interface.
 *
 * @see \Nethgui\Controller\Table\Modify
 */
class ArrayAccessSerializer implements SerializerInterface
{
    /**
     *
     * @var \ArrayAccess
     */
    private $data;

    /**
     * @var mixed 
     */
    private $arr;

    /**
     * 
     * 
     * @var array
     */
    private $subscriptList = array();

    /**
     *
     * @param \ArrayAccess $data
     * @param string $... subscripts
     * @throws \InvalidArgumentException 
     */
    public function __construct(\ArrayAccess $data)
    {
        $arguments = func_get_args();

        // skip the first $arr argument:
        array_shift($arguments);

        // subsequent arguments are the subscripts:
        foreach ($arguments as $s) {
            // stop on first NULL subscript, skip the followers.
            if (is_null($s)) {
                break;
            } elseif ( ! is_string($s)) {
                throw new \InvalidArgumentException(sprintf('%s: subscript parameter must be a string', __CLASS__), 1322148741);
            }
            $this->subscriptList[] = $s;
        }

        if (empty($this->subscriptList)) {
            throw new \InvalidArgumentException(sprintf('%s: you must provide at least one subscript argument', __CLASS__), 1336638118);
        }

        $this->data = $data;
    }

    private function lazyInitialization()
    {
        $arr = $this->data;

        $subscriptList = $this->subscriptList;
        array_pop($subscriptList);

        foreach ($subscriptList as $s) {
            if ( ! isset($arr[$s])) {
                $arr[$s] = array();
            }

            if ( ! ($arr[$s] instanceof \ArrayAccess || is_array($arr[$s]))) {
                throw new \LogicException(sprintf('%s: unexpected type %s. ', __CLASS__, gettype($arr[$s])), 1336398755);
            }

            $arr = &$arr[$s];
        }

        $this->arr = &$arr;
    }

    public function read()
    {
        if ( ! isset($this->arr)) {
            $this->lazyInitialization();
        }

        $last = \Nethgui\array_end($this->subscriptList);

        return isset($this->arr[$last]) ? $this->arr[$last] : NULL;
    }

    public function write($value)
    {
        $arr = $this->data;

        $currentValue = $this->read();

        if ($currentValue === $value) {
            return;
        }

        $this->arr[\Nethgui\array_end($this->subscriptList)] = $value;
    }

}
