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
    private $arr;

    /**
     * 
     * 
     * @var array
     */
    private $subscript;

    public function __construct(\ArrayAccess $arr, $subscript)
    {
        if ( ! is_string($subscript)) {
            throw new \InvalidArgumentException(sprintf('%s: $subscript parameter must be a string', get_class($this)), 1322148741);
        }

        $arguments = func_get_args();

        array_shift($arguments);

        $this->arr = $arr;
        $this->subscript = $arguments;
    }

    public function read()
    {
        $arr = $this->arr;

        $subscript = $this->subscript;
        $last = array_pop($subscript);

        foreach ($subscript as $s) {
            if ( ! ($arr instanceof \ArrayAccess || is_array($arr))) {
                throw new \LogicException(sprintf('%s: unexpected type %s. ', __CLASS__, gettype($arr)), 1336398755);
            }

            if ( ! isset($arr[$s])) {
                return NULL;
            }

            $arr = $arr[$s];
        }

        if ( ! isset($arr[$last])) {
            return NULL;
        }

        return $arr[$last];
    }

    public function write($value)
    {
        $arr = $this->arr;

        $currentValue = $this->read();

        if ($currentValue === $value) {
            return;
        }

        $subscript = $this->subscript;
        $last = array_pop($subscript);

        foreach ($subscript as $s) {
            if ( ! isset($arr[$s])) {
                $arr[$s] = array();
            }

            if ($arr[$s] instanceof \ArrayAccess) {
                $arr = $arr[$s];
            } elseif (is_array($arr[$s])) {
                $arr = &$arr[$s];
            } else {
                throw new \LogicException(sprintf('%s: unexpected type %s. ', __CLASS__, gettype($arr)), 1336398755);
            }
        }

        $arr[$last] = $value;
    }

}
