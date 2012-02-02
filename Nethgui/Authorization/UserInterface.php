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
 * UserInterface implements the authentication procedure and
 * provides access to authentication credentials of the user
 * 
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 * @api
 */
interface UserInterface extends AuthorizationAttributesProviderInterface
{

    const ID = __CLASS__;

    /**
     * Authenticate the user through the given credentials.
     *
     * NOTE:
     * You can pass an arbitrary number of arguments to the
     * authentication procedure. The actual number of arguments depends on
     * the implementation.
     *
     * @api
     * @see isAuthenticated()
     * @return boolean TRUE if authentication is successful
     */
    public function authenticate();

    /**
     * The authentication state
     *
     * @api
     * @return boolean TRUE if authenticated, FALSE otherwise
     */
    public function isAuthenticated();

    /**
     * Authentication credentials are acquired during authentication and
     * provide the basic informations for authorization decisions.
     *
     * @api
     * @param string $credentialName
     * @return mixed
     */
    public function getCredential($credentialName);

    /**
     * Check whether $credentialName is present or not.
     *
     * @api
     * @return boolean
     */
    public function hasCredential($credentialName);

    /**
     * The language that was choosen by the user
     *
     * @api
     * @return string ISO 639-1 language code (2 characters).
     */
    public function getLanguageCode();

}

