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
 * A Multiple adapter maps a scalar value to multiple keys or props through
 * a "reader" and a "writer" callback function.
 * 
 * The reader function is mandatory, the writer is optional. If you set to NULL the writer
 * callback you get a read-only adapter, but save() still returns the number of changes.
 * 
 * If the adapter has an empty set of serializers the callback function will 
 * still be called with no arguments.
 *
 */
class MultipleAdapter implements AdapterInterface
{

    private $innerAdapters = array();
    private $readerCallback;
    private $writerCallback;
    private $modified;

    /**
     * @see \Nethgui\Serializer\SerializerInterface
     * @param callback $readerCallback The reader PHP callback function: (p1, ..., pN) -> V
     * @param callback $writerCallback The writer PHP callback function: V -> (p1, ..., pN)
     * @param array $serializers An array of \Nethgui\Serializer\SerializerInterface objects
     */
    public function __construct($readerCallback, $writerCallback = NULL, $serializers = array())
    {
        if ( ! is_callable($readerCallback)) {
            throw new \InvalidArgumentException(sprintf('%s: Must provide a Reader callback function', get_class($this)), 1322149372);
        }

        $this->readerCallback = $readerCallback;
        $this->writerCallback = $writerCallback;

        foreach ($serializers as $serializer) {
            if ( ! $serializer instanceof \Nethgui\Serializer\SerializerInterface) {
                throw new \InvalidArgumentException(sprintf('%s: Invalid serializer instance. A serializer must implement \Nethgui\Serializer\SerializerInterface.', get_class($this)), 1322149373);
            }

            $this->innerAdapters[] = new ScalarAdapter($serializer);
        }
    }

    public function get()
    {
        if (is_null($this->modified)) {
            $this->lazyInitialization();
        }

        return $this->value;
    }

    public function set($value)
    {
        if (is_null($this->modified)) {
            $this->lazyInitialization();
        }

        if ($this->value !== $value) {
            $this->value = $value;
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

        $saved = FALSE;

        if (is_callable($this->writerCallback)) {
            $values = call_user_func($this->writerCallback, $this->value);

            $index = 0;
            $changes = 0;

            if (is_array($values)) {
                foreach ($values as $value) {
                    $this->innerAdapters[$index]->set($value);
                    $saved = $this->innerAdapters[$index]->save() ? TRUE : $saved;
                    $index ++;
                }
            }
        } else {
            $saved = TRUE;
        }

        $this->modified = FALSE;

        return $saved;
    }

    private function lazyInitialization()
    {
        $values = array();
        foreach ($this->innerAdapters as $innerAdapter) {
            $values[] = $innerAdapter->get();
        }

        $this->value = call_user_func_array($this->readerCallback, $values);
        $this->modified = FALSE;
    }

}
