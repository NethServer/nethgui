<?php

namespace Nethgui\Authorization;

/*
 * Copyright (C) 2012 Nethesis S.r.l.
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
 * Basic user properties
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
class User implements \Nethgui\Authorization\UserInterface, \Serializable
{
    /**
     *  @var \Nethgui\Log\LogInterface
     */
    private $log;

    /**
     *
     * @var callable
     */
    private $authenticationProcedure;

    /**
     *
     * @var boolean
     */
    private $modified = NULL;

    /**
     *
     * @var \Nethgui\Utility\SessionInterface
     */
    private $session;

    /**
     *
     * @var \ArrayObject
     */
    private $state;

    public function __construct(\Nethgui\Utility\SessionInterface $session, \Nethgui\Log\LogInterface $log)
    {
        $this->state = new \ArrayObject(array(
            'credentials' => array(),
            'preferences' => array('lang' => ''),
            'authenticated' => FALSE
        ));
        $this->log = $log;
        $this->session = $session;
        $this->authenticationProcedure = function () {
            return FALSE;
        };
    }

    public function setAuthenticationProcedure($procedure)
    {
        $this->authenticationProcedure = $procedure;
        return $this;
    }

    public function hasCredential($credentialName)
    {
        if ($this->modified === NULL) {
            $this->retrieveFromSession();
        }
        return isset($this->state['credentials'][$credentialName]);
    }

    public function getCredential($credentialName)
    {
        if ($this->modified === NULL) {
            $this->retrieveFromSession();
        }
        if ( ! $this->hasCredential($credentialName)) {
            return NULL;
        }
        return $this->state['credentials'][$credentialName];
    }

    public function getLanguageCode()
    {
        return $this->getPreference('lang');
    }

    /**
     *
     * @param string $lang
     * @return User
     */
    public function setLanguageCode($lang)
    {
        $this->setPreference('lang', strtolower(substr($lang, 0, 2)));
        return $this;
    }

    public function authenticate()
    {
        $args = func_get_args();
        $args[] = &$this->state['credentials'];
        $this->state['authenticated'] = call_user_func_array($this->authenticationProcedure, $args);
        $this->modified = TRUE;
        return $this->state['authenticated'];
    }

    public function isAuthenticated()
    {
        if ($this->modified === NULL) {
            $this->retrieveFromSession();
        }
        return $this->state['authenticated'] === TRUE;
    }

    public function setPreference($name, $value)
    {
        if ($this->modified === NULL) {
            $this->retrieveFromSession();
        }
        $this->state['preferences'][$name] = $value;
        $this->modified = TRUE;
        return $this;
    }

    public function getPreference($name)
    {
        if ($this->modified === NULL) {
            $this->retrieveFromSession();
        }
        if ( ! isset($this->state['preferences'][$name])) {
            return NULL;
        }
        return $this->state['preferences'][$name];
    }

    public function asAuthorizationString()
    {
        return $this->isAuthenticated() ? $this->getCredential('username') : 'Anonymous';
    }

    public function getAuthorizationAttribute($attributeName)
    {
        if ($attributeName === 'authenticated') {
            return $this->isAuthenticated();
        }
        return $this->getCredential($attributeName);
    }

    private function retrieveFromSession()
    {
        $state = $this->session->retrieve(__CLASS__);
        if ($state instanceof \ArrayObject) {
            $this->state = $state;
        } else {
            // TODO: try to resume the session stored in old format:
            $u = $this->session->retrieve(\Nethgui\Authorization\UserInterface::ID);
            if ($u instanceof self) {
                $this->state = $u->state;
                $this->session->store(__CLASS__, $this->state);
            } else {
                $this->session->login()->store(__CLASS__, $this->state);
            }
        }
        $this->modified = FALSE;
    }

    public function unserialize($serialized)
    {
        list($authenticated, $credentials, $preferences, $php, $log) = unserialize($serialized);
        $this->modified = FALSE;
        $this->log = new \Nethgui\Log\Nullog();
        $this->state = new \ArrayObject(array(
            'credentials' => $credentials,
            'preferences' => $preferences,
            'authenticated' => $authenticated
        ));
    }

    public function serialize()
    {
        return serialize(array($this->state['authenticated'], $this->state['credentials'], $this->state['preferences'], NULL, NULL));
    }

}