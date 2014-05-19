<?php

namespace Nethgui\System;

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
 * Database with a Session persistent storage
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.4
 */
class SessionDatabase implements \Nethgui\System\DatabaseInterface, \Nethgui\Utility\SessionConsumerInterface
{
    /**
     *
     * @var \ArrayObject
     */
    private $data;

    public function __construct()
    {
        $this->data = new \ArrayObject();
    }

    public function delProp($key, $props)
    {
        $k = isset($this->data[$key]) ? $this->data[$key] : NULL;
        if ($k) {
            foreach ($props as $p) {
                unset($k[$p]);
            }
        }
        return TRUE;
    }

    public function deleteKey($key)
    {
        unset($this->data[$key]);
        return TRUE;
    }

    public function getAll($type = NULL)
    {
        if ($type === NULL) {
            return $this->data->getArrayCopy();
        }

        $records = array();
        foreach ($this->data as $key => $value) {
            if (isset($value['type']) && $value['type'] === $type) {
                $records[$key] = $value;
            }
        }
        return $records;
    }

    public function getKey($key)
    {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }
        return NULL;
    }

    public function getProp($key, $prop)
    {
        if ( ! isset($this->data[$key]) || ! isset($this->data[$key][$prop])) {
            return NULL;
        }
        return $this->data[$key][$prop];
    }

    public function getType($key)
    {
        return $this->getProp($key, 'type');
    }

    public function setKey($key, $type, $props)
    {
        $this->data[$key] = $props;
        $this->data[$key]['type'] = $type;
        return TRUE;
    }

    public function setProp($key, $props)
    {
        if ( ! isset($this->data[$key])) {
            $this->data[$key] = array();
        }
        foreach ($props as $pk => $pv) {
            $this->data[$key][$pk] = $pv;
        }
        return TRUE;
    }

    public function setType($key, $type)
    {
        return $this->setProp($key, array('type' => $type));
    }

    public function setSession(\Nethgui\Utility\SessionInterface $session)
    {
        /* @var $data \ArrayObject */
        $data = $session->retrieve(__CLASS__);
        if ($data !== NULL) {
            /* @var $data \ArrayObject */
            $this->data->exchangeArray(array_replace_recursive($data->getArrayCopy(),
                    $this->data->getArrayCopy()));
        }
        $session->store(__CLASS__, $this->data);
        return $this;
    }

}