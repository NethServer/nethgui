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
class Request implements \Nethgui\Controller\RequestInterface, \Nethgui\Log\LogConsumerInterface
{
    /**
     *
     * @var \Nethgui\Authorization\UserInterface
     */
    private $user;

    /**
     *
     * @var array
     */
    private $attributes = array(
        'format' => 'xhtml',
        'locale' => '',
        'localeDefault' => 'en-US',
        'isValidated' => FALSE,
        'isMutation' => FALSE,
        'originalRequest' => FALSE,
        'userClosure' => FALSE
    );

    /**
     *
     * @var array
     */
    private $data = array();

    /**
     *
     * @var \Nethgui\Log\LogInterface
     */
    private $log;

    public function __construct($data = array())
    {
        $this->data = $data;
        $this->setAttribute('originalRequest', $this);
    }

    public function setParameter($name, $value)
    {
        if ( ! isset($this->data[$name])) {
            $this->data[$name] = $value;
        }
        return $this;
    }

    public function hasParameter($parameterName)
    {
        return array_key_exists($parameterName, $this->data);
    }

    public function isEmpty()
    {
        return empty($this->data);
    }

    public function isMutation()
    {
        return $this->getAttribute('isMutation') === TRUE;
    }

    public function getParameterNames()
    {
        return array_keys($this->data);
    }

    public function getParameter($parameterName)
    {
        if ( ! isset($this->data[$parameterName])) {
            return NULL;
        }
        return $this->data[$parameterName];
    }

    public function spawnRequest($subsetName, $path = array())
    {
        $parameterSubset = $this->getParameter($subsetName);
        if ( ! is_array($parameterSubset)) {
            $parameterSubset = array();
        }

        $instance = new static($parameterSubset);
        $instance->attributes = &$this->attributes;
        $instance->user = $this->user;

        if (isset($this->log)) {
            $instance->setLog($this->getLog());
        }

        if (count($path) > 0) {
            $this->getLog()->deprecated("%s: %s, \$path argument is DEPRECATED");
        }

        return $instance;
    }

    public function createSecondaryRequest($path, $requestData = array())
    {
        $r = $this->spawnRequest('');

        $pathInfo = explode('/', $path);
        $pathInfoMod = array();
        $cur = &$pathInfoMod;
        foreach ($pathInfo as $pathPart) {
            $cur[$pathPart] = array();
            $cur = &$cur[$pathPart];
        }

        $r->data = array_replace_recursive($pathInfoMod, $requestData);
        $r
            ->setAttribute('originalRequest', $this)
            ->setAttribute('isMutation', FALSE)
            ->setAttribute('isValidated', FALSE)
        ;
        return $r;
    }

    public function getUser()
    {
        if(isset($this->attributes['userClosure'])) {
            return \call_user_func($this->attributes['userClosure']);
        }
        return \Nethgui\Authorization\User::getAnonymousUser();
    }

    public function getPath()
    {
        $arr = &$this->data;
        $path = array();
        while (TRUE) {
            reset($arr);
            $part = key($arr);
            if ($part === NULL || ! is_array($arr[$part])) {
                break;
            }
            $path[] = $part;
            $arr = &$arr[$part];
        };

        return $path;
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
            throw new \LogicException(sprintf("%s: Cannot change the unknown attribute `%s`", __CLASS__, $name), 1325237327);
        }

        $this->attributes[$name] = $value;
        return $this;
    }

    public function getExtension()
    {
        return $this->getFormat();
    }

    public function getFormat()
    {
        return $this->getAttribute('format');
    }

    public function getLanguageCode()
    {
        return substr($this->getLocale(), 0, 2);
    }

    public function getLocale()
    {
        $locale = $this->getAttribute('locale');
        if ( ! $locale && $this->getUser()->isAuthenticated() ) {
            $locale = $this->getUser()->getLocale();
        }
        if ( ! $locale) {
            $locale = $this->getAttribute('localeDefault');
        }
        return $locale;
    }

    public function isValidated()
    {
        return $this->getAttribute('isValidated') === TRUE;
    }

    /**
     * Experimental method that returns the original request path
     *
     * @return array
     */
    public function getOriginalPath()
    {
        return $this->getAttribute('originalRequest')->getPath();
    }

    public function getArgument($argumentName)
    {
        $this->getLog()->deprecated();
        if ( ! isset($this->data[$argumentName])) {
            return NULL;
        }
        return $this->data[$argumentName];
    }

    public function getArgumentNames()
    {
        $this->getLog()->deprecated();
        return array_keys($this->data);
    }

    public function hasArgument($argumentName)
    {
        $this->getLog()->deprecated();
        return array_key_exists($argumentName, $this->data);
    }

    public function getLog()
    {
        if ( ! isset($this->log)) {
            return new \Nethgui\Log\Nullog();
        }
        return $this->log;
    }

    public function setLog(\Nethgui\Log\LogInterface $log)
    {
        $this->log = $log;
        return $this;
    }

    public function toArray()
    {
        return $this->data;
    }

}