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
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
class AuthorizationException extends \RuntimeException
{


    /**
     *
     * @var integer
     */
    private $authorizationCode;

    /**
     * @param string $message
     * @param integer $authorizationCode
     * @param integer $code
     * @param \Exception $previous
     */
    public function __construct($message, $authorizationCode, $code, $previous)
    {
        $this->authorizationCode = $authorizationCode;
        parent::__construct($message, $code, $previous);
    }

    /**
     *
     * @return integer
     */
    public function getAuthorizationCode()
    {
        return $this->authorizationCode;
    }

    public function __toString()
    {
        return sprintf('%s [%d]', parent::__toString(), $this->authorizationCode);
    }
}
