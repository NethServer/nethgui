<?php
namespace Nethgui\Controller;

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
 * Default implementation of RequestInterface
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 * @internal
 */
class Request implements \Nethgui\Controller\RequestInterface, \Nethgui\Utility\SessionConsumerInterface
{

    /**
     * @var array
     */
    private $postData;

    /**
     * @var array
     */
    private $getData;

    /**
     *
     * @var \Nethgui\Utility\SessionInterface
     */
    private $session;

    /**
     * @var array
     */
    private $path;

    /**
     *
     * @var \ArrayAccess
     */
    private $attributes;

    public function __construct($postData, $getData, $path, \ArrayAccess $attributes)
    {
        if ( ! is_array($postData) && ! is_array($getData)) {
            throw new \InvalidArgumentException(sprintf("%s: parameters and data must be of type `array`.", get_class($this)), 1325242431);
        }
        $this->postData = $postData;
        $this->getData = $getData;
        $this->path = $path;
        $this->attributes = $attributes;
    }

    public function setSession(\Nethgui\Utility\SessionInterface $session)
    {
        $this->session = $session;
        return $this;
    }

    public function hasParameter($parameterName)
    {
        return array_key_exists($parameterName, $this->postData);
    }

    public function isEmpty()
    {
        return empty($this->postData) && empty($this->getData);
    }

    public function isSubmitted()
    {
        return $this->getAttribute('submitted') === TRUE;
    }

    public function getParameterNames()
    {
        return array_keys($this->postData);
    }

    public function getParameter($parameterName)
    {
        if ( ! isset($this->postData[$parameterName])) {
            return NULL;
        }
        return $this->postData[$parameterName];
    }

    public function spawnRequest($subsetName, $path = array())
    {
        $parameterSubset = $this->getParameter($subsetName);
        if ( ! is_array($parameterSubset)) {
            $parameterSubset = array();
        }
        $argumentSubset = $this->getArgument($subsetName);
        if ( ! is_array($argumentSubset)) {
            $argumentSubset = array();
        }

        $instance = new static($parameterSubset, $argumentSubset, $path, $this->attributes);

        if (isset($this->session)) {
            $instance->setSession($this->session);
        }
        
        return $instance;
    }

    public function getUser()
    {
        $key = \Nethgui\Authorization\UserInterface::ID;

        $user = $this->session->retrieve($key);

        if (isset($this->session) && $user instanceof \Nethgui\Authorization\UserInterface) {
            return $user;
        }
        
        return \Nethgui\Authorization\User::getAnonymousUser();
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getAttribute($name)
    {
        if ( ! isset($this->attributes[$name])) {
            return NULL;
        }

        return $this->attributes[$name];
    }

    public function setAttribute($name, $value)
    {
        if ( ! isset($this->attributes[$name])) {
            throw new \LogicException(sprintf("%s: Cannot change the unknown attribute `%s`", get_class($this), $name), 1325237327);
        }

        $this->attributes[$name] = $value;
        return $this;
    }

    public function getExtension()
    {
        return $this->getAttribute('extension');
    }

    public function isValidated()
    {
        return $this->getAttribute('validated') === TRUE;
    }

    public function getArgument($argumentName)
    {
        if ( ! isset($this->getData[$argumentName])) {
            return NULL;
        }
        return $this->getData[$argumentName];
    }

    public function getArgumentNames()
    {
        return array_keys($this->getData);
    }

    public function hasArgument($argumentName)
    {
        return array_key_exists($argumentName, $this->getData);
    }

}

