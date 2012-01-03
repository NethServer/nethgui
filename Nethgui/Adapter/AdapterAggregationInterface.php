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
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 * @api
 */
interface AdapterAggregationInterface extends ModifiableInterface {

    /**
     * The list of registered adapter names
     *
     * @api
     * @return array
     */
    public function getKeys();

    /**
     * The list of modified adapters in this aggregation
     *
     * @api
     * @see getKeys()
     * @return array An array of keys
     */
    public function getModifiedKeys();
    
    /**
     * Register the given adapter with the given $key identity
     * 
     * @api
     * @param AdapterInterface $adapter
     * @param string $key
     */
    public function addAdapter(AdapterInterface $adapter, $key);
    
    /**
     * Obtain the adapter registered with the given identity
     *
     * @api
     * @return AdapterInterface
     */
    public function getAdapter($key);
   
}
