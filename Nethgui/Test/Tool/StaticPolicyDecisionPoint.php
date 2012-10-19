<?php
namespace Nethgui\Test\Tool;

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
 * A PDP implementation that returns a custom response. For testing
 * purposes
 *
 * @codeCoverageIgnore
 * @since 1.0
 * @author Davide Principi <davide.principi@nethesis.it>
 */
class StaticPolicyDecisionPoint implements \Nethgui\Authorization\PolicyDecisionPointInterface, \Nethgui\Authorization\AccessControlResponseInterface
{
    private $response;

    /**
     *
     * @param boolean $response
     */
    public function __construct($response)
    {
        $this->response = $response;
    }

    public function authorize($subject, $resource, $action)
    {
        return $this;
    }

    public function asException($identifier)
    {
        return new \Nethgui\Exception\AuthorizationException('', 0, 1350662831, NULL);
    }

    public function getCode()
    {
        return $this->response ? 0 : 1234567890;
    }

    public function getMessage()
    {
        return $this->response ? "Always ALLOW" : "Always DENY";
    }

    public function isDenied()
    {
        return ! $this->response;
    }

    public function isAllowed()
    {
        return $this->response;
    }

}
