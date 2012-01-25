<?php
namespace Nethgui\Authorization;

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
 * Lazy authorizations
 *
 * @see GroupBasedPolicyDecisionPoint
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
class LazyAccessControlResponse implements AccessControlResponseInterface
{

    private $message;
    private $code;
    private $closure;

    public function __construct($closure)
    {
        $this->closure = $closure;
    }

    public function getMessage()
    {
        if ( ! isset($this->message)) {
            $this->authorize();
        }
        return $this->message;
    }

    public function isGranted()
    {
        return $this->getCode() === 0;
    }

    public function isDenied()
    {
        return ! $this->isGranted();
    }

    public function asException($identifier)
    {
        return new \Nethgui\Exception\AuthorizationException($this->getMessage(), $this->getCode(), $identifier, NULL);
    }

    public function getCode()
    {
        if ( ! isset($this->code)) {
            $this->code = $this->authorize();
        }
        return $this->code;
    }

    protected function authorize()
    {
        $f = $this->closure;
        return $f($this->message);
    }

    /**
     * @return AccessControlResponseInterface
     */
    public static function createDenyResponse()
    {
        return new LazyAccessControlResponse(function (&$message) {
                    $message = 'ALWAYSFAIL';
                    return 1;
                });
    }

    /**
     * @return AccessControlResponseInterface
     */
    public static function createSuccessResponse()
    {
        return new LazyAccessControlResponse(function (&$message) {
                    $message = '';
                    return 0;
                });
    }

}