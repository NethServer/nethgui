<?php
namespace Nethgui\Authorization;

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
 * Implementors encapsulate the authorization response,
 * that can allow or deny the requested action.
 *
 * @api
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
interface AccessControlResponseInterface
{

    /**
     * TRUE if the access is granted.
     *
     * @api
     * @return bool TRUE, if granted, FALSE otherwise.
     */
    public function isAllowed();

    /**
     * This is the dual of isAllowed().
     *
     * @api
     * @see isGranted()
     * @return bool TRUE, if access is denied FALSE otherwise
     */
    public function isDenied();

    /**
     * Response explanation.
     *
     * @api
     * @return string
     */
    public function getMessage();

    /**
     * Response numeric code.
     *
     * @api
     * @return integer 0 if granted, positive otherwise
     */
    public function getCode();

    /**
     * Prepare an exception object with the response informations.
     *
     * @api
     * @param integer $identifier The exception unique identifier
     * @return \Nethgui\Exception\AuthorizationException
     */
    public function asException($identifier);
}
