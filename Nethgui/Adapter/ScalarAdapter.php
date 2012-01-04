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
 * Scalar adapter maps a scalar value to a key or prop value through a Serializer.
 *
 */
class ScalarAdapter implements AdapterInterface
{

    protected $modified;
    protected $value;
    /**
     *
     * @var \Nethgui\Serializer\SerializerInterface
     */
    private $serializer;

    public function __construct(\Nethgui\Serializer\SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function delete()
    {
        $this->set(NULL);
    }

    public function get()
    {
        if (is_null($this->modified)) {
            $this->modified = FALSE;
            $this->value = $this->serializer->read();            
        }
        return $this->value;
    }

    public function set($value)
    {
        if (is_null($this->modified)) {
            $this->modified = FALSE;
            $this->value = $this->serializer->read();
        }

        if ($this->value !== $value) {
            $this->value = $value;
            $this->modified = TRUE;
        }
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

        $this->serializer->write($this->value);
        $this->modified = FALSE;
        
        return TRUE;
    }

}
