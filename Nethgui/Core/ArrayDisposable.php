<?php
namespace Nethgui\Core;

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
 * An array of DisposableInterface objects
 *
 * When an array item is disposed it will no longer be serialized by this object
 *
 * @see \Nethgui\Core\DisposableInterface
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
class ArrayDisposable extends \ArrayObject
{

    public function offsetSet($index, $newval)
    {
        if ( ! $newval instanceof DisposableInterface) {
            throw new \InvalidArgumentException(sprintf('%s: only DisposableInterface objects can be stored in this array', __CLASS__), 1326101771);
        }
        parent::offsetSet($index, $newval);
    }

    public function serialize()
    {       
        foreach (new \ArrayIterator($this) as $index => $object) {
            if ($object instanceof DisposableInterface && $object->isDisposed()) {
                $this->offsetUnset($index);
            }
        }
        return parent::serialize();
    }

}
