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
 * Transfer a value to/from a database Key.
 *
 */
class KeySerializer implements SerializerInterface
{

    private $key;
    /**
     *
     * @var \Nethgui\System\ConfigurationDatabase
     */
    private $database;

    public function __construct(\Nethgui\System\ConfigurationDatabase $database, $key)
    {
        $this->database = $database;
        $this->key = $key;
    }

    /**
     * XXX: Calling "getType" for reading key value (?)
     * @return string
     */
    public function read()
    {
        return $this->database->getType($this->key);
    }

    /**
     * XXX: Calling "setType" for writing key value (?)
     * @return string
     */
    public function write($value)
    {
        if($value === NULL){
            $this->database->deleteKey($this->key);
        } else {
            $this->database->setType($this->key, strval($value));
        }
    }

}
