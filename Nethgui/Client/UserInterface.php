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
     * @api
     * @param bool $status
     * @return UserInterface
     */
    public function setAuthenticated($status);

    /**
     * @api
     * @param string $credentialName
     * @param mixed $credentialValue
     * @return UserInterface
     */
    public function setCredential($credentialName, $credentialValue);

    /**
     * @api
     * @param string $credentialName
     * @return mixed
     */
    public function getCredential($credentialName);

    /**
     * @api
     * @return array
     */
    public function getCredentials();

    /**
     * @api
     * @return boolean
     */
    public function hasCredential($credentialName);

    /**
     * Get the current language code
     *
     * @api
     * @return string ISO 639-1 language code (2 characters).
     *
     */
    public function getLanguageCode();

    /**
     * 
     * @api
     * @return \Nethgui\Core\SessionInterface     
     */
    public function getSession();

}

