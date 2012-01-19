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
class Session implements \Nethgui\Utility\SessionInterface, \Nethgui\Utility\PhpConsumerInterface
{

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

    public function __construct(\Nethgui\Utility\PhpWrapper $gfw = NULL)
    {
        if (isset($gfw)) {
            $this->phpWrapper = $gfw;
        } else {
            $this->phpWrapper = new \Nethgui\Utility\PhpWrapper();
        }

        $this->phpWrapper->session_name(get_class($this));
        if ($this->getSessionIdentifier() == '') {
            $this->phpWrapper->session_start();
        }

        $this->data = $this->phpWrapper->phpReadGlobalVariable('_SESSION', get_class($this));

        if (is_null($this->data)) {
            $this->data = new \ArrayObject();
        } elseif ( ! $this->data instanceof \ArrayObject) {
            throw new \UnexpectedValueException(sprintf('%s: session data must be enclosed into an \ArrayObject', __CLASS__), 1322738011);
        }
    }

    public function setPhpWrapper(\Nethgui\Utility\PhpWrapper $object)
    {
        $this->phpWrapper = $object;
    }

    public function getSessionIdentifier()
    {
        return $this->phpWrapper->session_id();
    }

    public function retrieve($key)
    {
        $object = $this->data[$key];

        if ( ! $object instanceof \Serializable) {
            throw new \UnexpectedValueException(sprintf('%s: only \Serializable implementors can be stored in this collection!', get_class($this)), 1322738020);
        }

        return $object;
    }

    public function store($key, \Serializable $object)
    {
        $this->data[$key] = $object;
        return $this;
    }

    public function hasElement($key)
    {
        return isset($this->data[$key]);
    }

    public function __destruct()
    {
        $this->phpWrapper->phpWriteGlobalVariable($this->data, '_SESSION', get_class($this));
    }

}