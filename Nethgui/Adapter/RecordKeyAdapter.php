<?php
namespace Nethgui\Adapter;

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
 * Bind a record key value to a parameter.
 * 
 * - Gives an AdapterInterface to RecordAdapter getKeyValue/setKeyValue methods
 * - Collaborates with RecordAdapter and ParameterSet
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
class RecordKeyAdapter implements \Nethgui\Adapter\AdapterInterface
{
    /**
     * @var \Nethgui\Adapter\RecordAdapter 
     */
    private $recordAdapter;

    public function __construct(\Nethgui\Adapter\RecordAdapter $recordAdapter)
    {
        $this->recordAdapter = $recordAdapter;
    }

    public function delete()
    {
        throw new \LogicException(sprintf('%s: you cannot delete the record key', __CLASS__), 1336644672);
    }

    public function get()
    {
        return $this->recordAdapter->getKeyValue();
    }

    public function isModified()
    {
        return FALSE;
    }

    public function save()
    {
        return FALSE;
    }

    public function set($value)
    {
        $this->recordAdapter->setKeyValue($value);
    }

}