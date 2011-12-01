<?php
namespace Nethgui\Client;

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
 * UserInterface provides access to the login information of the current user.
 *
 * @todo Move into Core package
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 * @api
 */
interface UserInterface 
{

    /**
     * @return boolean
     * @api
     */
    public function isAuthenticated();

    /**
     * @param bool $status
     * @return UserInterface
     * @api
     */
    public function setAuthenticated($status);

    /**
     * @param string $credentialName
     * @param mixed $credentialValue
     * @return UserInterface
     * @api
     */
    public function setCredential($credentialName, $credentialValue);

    /**
     * @param string $credentialName
     * @return mixed
     * @api
     */
    public function getCredential($credentialName);

    /**
     * @return array
     * @api
     */
    public function getCredentials();

    /**
     * @return boolean
     * @api
     */
    public function hasCredential($credentialName);

    /**
     * Get the current language code
     * @return string ISO 639-1 language code (2 characters).
     * @api
     */
    public function getLanguageCode();

    /**
     * @return \Nethgui\Core\SessionInterface
     * @api
     */
    public function getSession();

}

