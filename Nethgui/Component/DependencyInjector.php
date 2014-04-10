<?php

namespace Nethgui\Component;

/*
 * Copyright (C) 2014  Nethesis S.r.l.
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
 * Set dependencies into target object
 * 
 * Register callbacks, parameters and dependenencies with a specific key into
 * this array.  When the inject() method is invoked any registered callback function
 * is invoked passing the ArrayObject.
 *
 * Deleting or overriding an existing key is forbidden!
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
class DependencyInjector extends \ArrayObject implements \Nethgui\Component\DependencyInjectorInterface
{
    public function inject($object)
    {
        $callbacks = array_filter(\iterator_to_array($this), 'is_callable');

        if($object instanceof \Nethgui\Component\DependencyInjectorAggregate) {
            $object->setDependencyInjector($this);
        }

        foreach($callbacks as $f) {
            call_user_func($f, $object, $this->getArrayCopy());
        }
    }

    public function offsetSet($index, $newval)
    {
        if($this->offsetExists($index)) {
            // Forbid to replace existing offsets:
            throw new \LogicException(sprintf("%s: item `%s` replacement is forbidden!", __CLASS__, $index), 1397143248);
        }
        return parent::offsetSet($index, $newval);
    }

    public function offsetUnset($index)
    {
        throw new \LogicException(sprintf("%s: item `%s` deletion is forbidden", __CLASS__, $index), 1397143249);
    }

}