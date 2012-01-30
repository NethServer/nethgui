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

    /**
     * 
     * @var array
     */
    private $request;

    /**
     *
     * @param Closure $closure
     * @param array $request
     */
    public function __construct($closure, $request)
    {
        $this->request = $request;
        $this->closure = $closure;
    }

    public function getMessage()
    {
        if ( ! isset($this->message)) {
            $this->authorize();
        }
        return $this->message;
    }

    public function isAllowed()
    {
        return $this->getCode() === 0;
    }

    public function isDenied()
    {
        return ! $this->isAllowed();
    }

    /**
     * Convert the given $value to a String
     *
     * @param mixed $value
     * @return string
     */
    protected function asString($value)
    {
        $converter = new StringAttributesProvider($value);
        return $converter->asAuthorizationString();
    }

    public function asException($identifier)
    {
        $originalRequest = '';

        foreach ($this->request as $name => $value) {
            $originalRequest .= sprintf('%s `%s` ', $name, $this->asString($value));
        }

        return new \Nethgui\Exception\AuthorizationException($this->getMessage() . " :: AppliedTo :: " . trim($originalRequest), $this->getCode(), $identifier, NULL);
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
        return $f($this->request, $this->message);
    }

    /**
     * @return AccessControlResponseInterface
     */
    public static function createDenyResponse()
    {
        $request = array(
            'subject' => 'None',
            'resource' => 'None',
            'action' => 'None'
        );

        return new LazyAccessControlResponse(function (&$message) {
                    $message = 'ALWAYSFAIL';
                    return 1;
                }, $request);
    }

    /**
     * @return AccessControlResponseInterface
     */
    public static function createSuccessResponse()
    {
        $request = array(
            'subject' => 'None',
            'resource' => 'None',
            'action' => 'None'
        );

        return new LazyAccessControlResponse(function (&$message) {
                    $message = '';
                    return 0;
                }, $request);
    }

}