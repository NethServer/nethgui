<?php
namespace Nethgui\Test\Tool;

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
 * Mock database object
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
class DB extends \Nethgui\Test\Tool\MockState
{

    public static function getType($key)
    {
        return array('getType', array($key));
    }

    public static function setType($key, $value)
    {
        return array('setType', array($key, $value));
    }

    public static function getKey($key)
    {
        return array('getKey', array($key));
    }

    public static function setKey($key, $type, $props)
    {
        return array('setKey', array($key, $type, $props));
    }

    public static function getProp($key, $prop)
    {
        return array('getProp', array($key, $prop));
    }

    public static function setProp($key, $prop, $value = NULL)
    {
        if (is_string($prop)) {
            return array('setProp', array($key, array($prop => $value)));
        }
        return array('setProp', array($key, $prop));
    }

    public static function getAll($type = NULL, $filter = NULL)
    {
        return array('getAll', array($type, $filter));
    }

    public static function deleteKey($key)
    {
        return array('deleteKey', array($key));
    }

    public static function delProp($key, $prop)
    {
        if (is_string($prop)) {
            $prop = array($prop);
        }
        return array('delProp', array($prop));
    }

}
