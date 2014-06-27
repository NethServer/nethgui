<?php

namespace Nethgui\Utility;

/*
 * Copyright (C) 2014  Nethesis S.r.l.
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
 * TODO: add component description here
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.6 
 */
class HttpResponse
{
    /**
     *
     * @var array
     */
    private $httpStatusMessages = array(
        '200' => 'Success',
        '201' => 'Created',
        '302' => 'Found',
        '400' => 'Bad request',
        '403' => 'Forbidden',
        '500' => 'Internal server error',
    );

    public function __construct($content = '', $status = 200, $headers = array())
    {
        $this->content = $content;
        $this->headers = $headers;
        $this->status = $status;
        $this->eventHandlers = array(
            'post-response' => array(),
            'pre-response' => array(),
            );
    }

    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    public function setStatus($status, $message = NULL)
    {
        if(isset($message)) {
            $this->httpStatusMessages[$status] = $message;
        }
        $this->status = $status;
        return $this;
    }

    public function addHeader($header) {
        $this->headers[] = $header;
        return $this;
    }

    public function send()
    {
        $this->triggerEvent('pre-response');
        header(sprintf('HTTP/1.1 %d %s', $this->status, $this->httpStatusMessages[$this->status]));
        array_map('header', $this->headers);
        echo $this->content;
        flush();
        $this->triggerEvent('post-response');
    }

    private function triggerEvent($name)
    {
        foreach($this->eventHandlers[$name] as $f) {
            \call_user_func($f, $this);
        }
        return $this;
    }

    public function on($eventName, $handler)
    {
        $this->eventHandlers[$eventName][] = $handler;
        return $this;
    }
}