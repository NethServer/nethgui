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
 * Transfers a value to/from a database Prop.
 *
 */
class PropSerializer implements SerializerInterface
{
    private $key;
    private $prop;

    /**
     *
     * @var \Nethgui\System\DatabaseInterface
     */
    private $database;

    public function __construct(\Nethgui\System\DatabaseInterface $database, $key, $prop)
    {
        $this->database = $database;
        $this->key = $key;
        $this->prop = $prop;
    }

    public function read()
    {
        return $this->database->getProp($this->key, $this->prop);
    }

    public function write($value)
    {
        if($value === NULL) {
            $this->database->delProp($this->key, array($this->prop));
        } else {
            $this->database->setProp($this->key, array($this->prop => $value));
        }
    }

}
