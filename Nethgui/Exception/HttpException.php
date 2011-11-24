<?php
namespace Nethgui\Exception;

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
 */
class HttpException extends \RuntimeException
{

    private $httpStatusCode;

    public function getHttpStatusCode()
    {
        return $this->httpStatusCode;
    }

    /**
     *
     * @param string $message
     * @param integer $httpStatusCode Valid HTTP 1.1 status code
     * @param integer $code Unique Unix timestamp identifier of the exception
     * @param Exception $previous Optional
     */
    public function __construct($message, $httpStatusCode, $code, $previous = NULL)
    {
        parent::__construct($message, $code, $previous);
        $this->httpStatusCode = $httpStatusCode;
    }

}
