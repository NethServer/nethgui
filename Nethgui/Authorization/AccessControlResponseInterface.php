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
 * AccessControlRequestInterface.
 *
 * An AccessControlRequestInterface implementing object encapsulates the authorization
 * response that can be ``GRANTED`` or ``NOT GRANTED``.
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @see AccessControlRequestInterface
 * @api
 * @since 1.0
 */
interface AccessControlResponseInterface
{

    /**
     * TRUE if the access is granted
     *
     * @return bool TRUE, if granted, FALSE otherwise.
     */
    public function isGranted();

    /**
     * This is the dual of isGranted()
     *
     * @see isGranted()
     * @return bool TRUE, if access is denied FALSE otherwise
     */
    public function isDenied();

    /**
     * Response explanation
     *
     * @return string
     */
    public function getMessage();

    /**
     * Response code
     * 
     * @return integer 0 if granted, positive otherwise
     */
    public function getCode();

    /**
     * @param integer $identifier
     * @return \Nethgui\Exception\AuthorizationException
     */
    public function asException($identifier);
}
