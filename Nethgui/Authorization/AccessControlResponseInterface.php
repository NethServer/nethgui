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
 * @see AccessControlRequestInterface
 */
interface AccessControlResponseInterface
{

    /**
     * Get a reference to the original Request.
     * @return AccessControlRequestInterface The original Request.
     */
    public function getRequest();

    /**
     * @return bool TRUE, if granted, FALSE otherwise.
     */
    public function isAccessGranted();

    /**
     * Can contain a message explaining the response state.
     * @return string
     */
    public function getMessage();
}
