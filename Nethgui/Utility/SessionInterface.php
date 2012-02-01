<?php
namespace Nethgui\Utility;

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
 * Access to the session storage
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 * @api
 */
interface SessionInterface
{

    /**
     * Put a Serializable object into the session storage
     *
     * @api
     * @param string $key
     * @param \Serializable $object
     * @return SessionInterface The same SessionInterface object
     */
    public function store($key, \Serializable $object);

    /**
     * Get a stored Serializable object
     *
     * @api
     * @param string $key
     * @return \Serializable The stored object
     */
    public function retrieve($key);

    /**
     * @api
     * @return SessionInterface
     */
    public function login();

    /**
     * @api
     * @return SessionInterface
     */
    public function logout();
}