<?php

namespace Nethgui\Utility;

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
 * Store data in php $_SESSION variable.
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 * @internal
 */
class Session implements \Nethgui\Utility\SessionInterface, \Nethgui\Utility\PhpConsumerInterface, \Nethgui\Log\LogConsumerInterface
{
    const SESSION_NAME = 'nethgui';

    /**
     *
     * @var \Nethgui\Utility\PhpWrapper
     */
    private $phpWrapper;

    /**
     *
     * @var ArrayObject
     */
    private $data;

    /**
     *
     * @var \Nethgui\Log\LogInterface
     */
    private $log;

    public function __construct(\Nethgui\Utility\PhpWrapper $phpWrapper = NULL)
    {
        $this->phpWrapper = $phpWrapper === NULL ? new \Nethgui\Utility\PhpWrapper(__CLASS__) : $phpWrapper;
        $this->data = new \ArrayObject();
        $this->phpWrapper->session_name(self::SESSION_NAME);
    }

    public function isStarted()
    {
        return $this->getSessionIdentifier() !== '';
    }

    public function start()
    {
        if ($this->isStarted()) {
            throw new \LogicException(sprintf('%s: cannot start an already started session!', __CLASS__), 1327397142);
        }

        $this->phpWrapper->session_start();

        $this->data = $this->phpWrapper->phpReadGlobalVariable('_SESSION', self::SESSION_NAME);
        if (is_null($this->data)) {
            $this->data = new \ArrayObject();
        } elseif ( ! $this->data instanceof \ArrayObject) {
            throw new \UnexpectedValueException(sprintf('%s: session data must be enclosed into an \ArrayObject', __CLASS__), 1322738011);
        }
        return $this;
    }

    public function unlock()
    {
        static $unlocked;
        if ($this->isStarted() && $unlocked !== TRUE) {
            $key = get_class($this);
            if (isset($this->data[$key]) && $this->data[$key] === TRUE) {
                $this->phpWrapper->phpWriteGlobalVariable($this->data, '_SESSION', self::SESSION_NAME);
            }
            $this->phpWrapper->session_write_close();
            $unlocked = TRUE;
        }
        return $this;
    }

    public function setPhpWrapper(\Nethgui\Utility\PhpWrapper $object)
    {
        $this->phpWrapper = $object;
        return $this;
    }

    private function getSessionIdentifier()
    {
        return $this->phpWrapper->session_id();
    }

    public function retrieve($key)
    {
        if ( ! $this->isStarted()) {
            $this->start();
        }

        if ( ! isset($this->data[$key]) || $this->data[$key] === NULL) {
            return NULL;
        }

        $object = $this->data[$key];

        if ( ! $object instanceof \Serializable) {
            throw new \UnexpectedValueException(sprintf('%s: only \Serializable implementors can be stored in this collection!', __CLASS__), 1322738020);
        }

        return $object;
    }

    public function store($key, \Serializable $object = NULL)
    {
        if ( ! $this->isStarted()) {
            $this->start();
        }
        $this->data[$key] = $object;
        return $this;
    }

    public function login()
    {
        $this->phpWrapper->session_regenerate_id(TRUE);
        $this->data[get_class($this)] = TRUE;
        return $this;
    }

    public function logout()
    {
        $this->phpWrapper->session_destroy();
        $this->data[get_class($this)] = FALSE;
        return $this;
    }

    public function __destruct()
    {
        $this->unlock();
    }

    public function getLog()
    {
        if ( ! isset($this->log)) {
            $this->log = new \Nethgui\Log\Nullog();
        }
        return $this->log;
    }

    public function setLog(\Nethgui\Log\LogInterface $log)
    {
        $this->log = $log;
        return $log;
    }

}