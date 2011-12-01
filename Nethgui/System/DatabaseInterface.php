<?php
namespace Nethgui\System;

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
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 * @api
 */
interface DatabaseInterface
{

    /**
     * Retrieve all keys from the database. If needed, you can use filter the results by type and key name.
     *
     * @param string $type (optional) type of the key
     * @param string $filter (optional) case insensitive fulltext search on key value
     * @return array associative array in the form "[KeyName] => array( [type] => [TypeValue], [PropName1] => [PropValue1], [PropName2] => [PropValue2], ...)
     * @api
     */
    public function getAll($type = NULL, $filter = NULL);

    /**
     * Retrieve a key from the database.
     * Act like : /sbin/e-smith/db dbfile get key
     *
     * @param string $key the key to read
     * @return array associative array in the form [PropName] => [PropValue]
     * @api
     */
    public function getKey($key);

    /**
     * Set a database key with type and properties.
     * Act like: /sbin/e-smith/db dbfile set key type [prop1 val1] [prop2 val2] ...
     *
     * @param string $key Key to write
     * @param string $type Type of the key
     * @param string $props Array of properties in the form [PropName] => [PropValue]
     *
     * @return bool TRUE on success, FALSE otherwise
     * @api
     */
    public function setKey($key, $type, $props);

    /**
     * Delete a key and all its properties
     * Act like: /sbin/e-smith/db dbfile delete key
     *
     * @param mixed $key
     * @return void
     * @api
     */
    public function deleteKey($key);

    /**
     * Return the type of a key
     * Act like: /sbin/e-smith/db dbfile gettype key
     *
     * @param string $key the key to retrieve
     * @return string the type of the key
     * @api
     */
    public function getType($key);

    /**
     * Set the type of a key
     * Act like: /sbin/e-smith/db dbfile settype key type
     *
     * @param string $key the key to change
     * @param string $type the new type
     * @return bool true on success, FALSE otherwise
     * @api
     */
    public function setType($key, $type);

    /**
     * Read the value of the given property
     * Act like: /sbin/e-smith/db dbfile getprop key prop
     *
     * @param string $key the parent property key
     * @param string $prop the name of the property
     * @return string the value of the property
     * @api
     */
    public function getProp($key, $prop);

    /**
     * Set one or more properties under the given key
     *
     * Act like: /sbin/e-smith/db dbfile setprop key prop1 val1 [prop2 val2] [prop3 val3] ...
     *
     * @param string $key the property parent key
     * @param array $props an associative array in the form [PropName] => [PropValue]
     * @return bool TRUE on success, FALSE otherwise
     * @api
     */
    public function setProp($key, $props);

    /**
     * Delete one or more properties under the given key
     * Act like: sbin/e-smith/db dbfile delprop key prop1 [prop2] [prop3] ...
     *
     * @param string $key the property parent key
     * @param array $props a simple array containg the properties to be deleted
     * @return bool TRUE on success, FALSE otherwise
     * @api
     */
    public function delProp($key, $props);
}
