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
 * TODO: describe class
 *
 * @deprecated Substitute with the complete implementation in version Sigma.
 */
class AlwaysAuthenticatedUser implements UserInterface
{

    /**
     * User authentication credentials
     * @var array
     */
    private $credentials;

    /**
     * @var string
     */
    private $languageCode;

    /**
     *
     * @var \Nethgui\Core\SessionInterface
     */
    private $session;

    public function __construct(\Nethgui\Core\SessionInterface $session)
    {
        $this->session = $session;

        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $this->setLanguageCode($_SERVER['HTTP_ACCEPT_LANGUAGE']);
        } else {
            $this->setLanguageCode('en');
        }

        $key = get_class($this);

        if ($this->session->hasElement($key)) {
            $this->credentials = $this->session->retrieve($key);
        } else {
            $this->credentials = new \ArrayObject();
        }
    }

    public function __destruct()
    {
        $this->session->store(get_class($this), $this->credentials);
    }

    public function getCredential($credentialName)
    {
        if ( ! isset($this->credentials[$credentialName])) {
            return NULL;
        }
        return $this->credentials[$credentialName];
    }

    public function getCredentials()
    {
        return array_keys($this->credentials);
    }

    public function isAuthenticated()
    {
        return TRUE;
    }

    public function setAuthenticated($status)
    {
        return $this;
    }

    public function setCredential($credentialName, $credentialValue)
    {
        $this->credentials[$credentialName] = $credentialValue;
        return $this;
    }

    public function hasCredential($credentialName)
    {
        return isset($this->credentials[$credentialName]);
    }

    /**
     * Set the current language code
     * @param string $code ISO 639-1 language code (2 characters).
     */
    private function setLanguageCode($languageCode)
    {
        if ($languageCode) {
            $this->languageCode = strtolower(substr($languageCode, 0, 2));
        }
    }

    public function getLanguageCode()
    {
        return $this->languageCode;
    }

    public function getSession()
    {
        return $this->session;
    }

}
