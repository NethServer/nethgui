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
 */
interface AdapterAggregationInterface {

    /**
     * Return the list of registered parameter names
     * @return array
     */
    public function getKeys();
    
    /**
     * @param AdapterInterface $adapter
     * @param string $key
     */
    public function register(AdapterInterface $adapter, $key);
    
    /**
     * @return AdapterInterface
     */
    public function query($key);
    
    /**
     * Check if a member is modified from its initial value.
     * 
     * If the member to check is not specified (NULL) the method checks if any
     * of its member is modified and returns TRUE on this case.
     * 
     * @param string $key Optional The member to check. 
     * @return bool
     */
    public function isModified($key = NULL);
    
    /**
     * Saves aggregated values into database,
     * forwarding the call to Adapters and Sets..
     * 
     * @return integer The number of saved parameters. A zero value indicates that nothing has been saved.
     */
    public function save();
}
