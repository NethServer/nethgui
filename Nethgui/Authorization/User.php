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
class User implements \Nethgui\Authorization\UserInterface, \Serializable, \Nethgui\Utility\PhpConsumerInterface, \Nethgui\Log\LogConsumerInterface
{
    /**
     *  @var \Nethgui\Log\LogInterface
     */
    private $log;

    /**
     *
     * @var bool
     */
    private $authenticated = FALSE;

    /**
     *
     * @var callable
     */
    private $authenticationProcedure;

    /**
     *
     * @var \Nethgui\Utility\PhpWrapper
     */
    protected $php;

    /**
     *
     * @var array
     */
    private $credentials = array();

    /**
     *
     * @var array
     */
    private $preferences = array();

    /**
     * 
     * @return \Nethgui\Authorization\UserInterface
     */
    public static function getAnonymousUser()
    {
        static $anonymous = NULL;

        // @codeCoverageIgnoreStart
        if ( ! isset($anonymous)) {
            $anonymous = new static();
            $anonymous->setAuthenticationProcedure(function() {
                    return FALSE;
                });
        }
        // @codeCoverageIgnoreEnd

        return $anonymous;
    }

    public function __construct(\Nethgui\Utility\PhpWrapper $php = NULL, \Nethgui\Log\LogInterface $log = NULL)
    {
        if (is_null($php)) {
            $php = new \Nethgui\Utility\PhpWrapper();
        }
        $this->setPhpWrapper($php);

        if (is_null($log)) {
            $log = new \Nethgui\Log\Nullog();
        }
        $this->setLog($log);

        // The default PAM based authentication procedure:
        $this->authenticationProcedure = array(new \Nethgui\Utility\PamAuthenticator($php, $log), 'authenticate');
    }

    public function setAuthenticationProcedure($procedure)
    {
        $this->authenticationProcedure = $procedure;
        return $this;
    }

    public function getCredential($credentialName)
    {
        if ( ! $this->hasCredential($credentialName)) {
            return NULL;
        }
        return $this->credentials[$credentialName];
    }

    public function getLanguageCode()
    {
        if ( ! isset($this->preferences['lang'])) {
            return '';
        }       
        return $this->preferences['lang'];
    }

    /**
     *
     * @param string $lang
     * @return User
     */
    public function setLanguageCode($lang)
    {
        $this->preferences['lang'] = strtolower(substr($lang, 0, 2));
        return $this;
    }

    public function hasCredential($credentialName)
    {
        return isset($this->credentials[$credentialName]);
    }

    public function authenticate()
    {
        $args = func_get_args();
        $args[] = &$this->credentials;
        $this->authenticated = call_user_func_array($this->authenticationProcedure, $args);
        return $this->authenticated;
    }

    public function isAuthenticated()
    {
        return $this->authenticated === TRUE;
    }

    public function serialize()
    {
        return serialize(array($this->authenticated, $this->credentials, $this->preferences, $this->php, $this->log));
    }

    public function unserialize($serialized)
    {
        list($this->authenticated, $this->credentials, $this->preferences, $this->php, $this->log) = unserialize($serialized);
    }

    public function setPhpWrapper(\Nethgui\Utility\PhpWrapper $object)
    {
        $this->php = $object;
        return $this;
    }

    public function setPreference($name, $value)
    {
        $this->preferences[$name] = $value;
        return $this;
    }

    public function getPreference($name)
    {
        if ( ! isset($this->preferences[$name])) {
            return NULL;
        }
        return $this->preferences[$name];
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

    public function getLog()
    {
        return $this->log;
    }

    public function setLog(\Nethgui\Log\LogInterface $log)
    {
        $this->log = $log;
        return $this;
    }

}