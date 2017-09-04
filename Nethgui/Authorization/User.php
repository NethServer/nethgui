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
     * @var \Nethgui\System\ValidatorInterface
     */
    private $authenticationValidator;

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
            'preferences' => array('lang' => 'en', 'locale' => 'en-US'),
            'authenticated' => FALSE
        ));
        $this->log = $log;
        $this->session = $session;
        $this->authenticationValidator = new \Nethgui\System\AlwaysFailValidator();
    }

    public function setAuthenticationValidator(\Nethgui\System\ValidatorInterface $v)
    {
        $this->authenticationValidator = $v;
        return $this;
    }

    /**
     * @deprecated since version 1.6.1
     * @param callable $procedure
     * @return \Nethgui\Authorization\User
     */
    public function setAuthenticationProcedure($procedure)
    {
        $this->authenticationValidator = new \Nethgui\System\CallbackValidator(function($args) use ($procedure) {
            return \call_user_func_array($procedure, $args);
        });
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
    
    public function getLocale()
    {
        return $this->getPreference('locale');
    }
    /**
     *
     * @param string $lang
     * @deprecated since version 1.7.0
     * @return User
     */
    public function setLanguageCode($lang)
    {
        $this->log->deprecated();
        return $this;
    }

    /**
     * @since 1.7.0
     * @param string $locale
     * @return \Nethgui\Authorization\User
     */
    public function setLocale($locale)
    {
        $this->setPreference('locale', $locale);
        $this->setPreference('lang', substr($locale, 0, 2));
        return $this;
    }

    public function authenticate()
    {
        $args = func_get_args();
        $args[] = &$this->state['credentials'];
        $this->state['authenticated'] = $this->authenticationValidator->evaluate($args);
        $this->modified = TRUE;
        if($this->state['authenticated'] === TRUE) {
            $this->log->notice(sprintf("%s: user `%s` authenticated", __CLASS__, $args[0]));
            $this->session->login();
        }
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
            $this->session->store(__CLASS__, $this->state);
        }
        $this->modified = FALSE;
    }

    public function unserialize($serialized)
    {
        list($authenticated, $credentials, $preferences) = unserialize($serialized);
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