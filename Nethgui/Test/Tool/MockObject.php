<?php
namespace Nethgui\Test\Tool;

/*
 * Copyright (C) 2012 Nethesis S.r.l.
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
 * TODO: write description here
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
class MockObject implements \PHPUnit_Framework_MockObject_Stub
{

    /**
     *
     * @var \Nethgui\Test\Tool\MockState
     */
    private $state = NULL;

    public function __construct(\Nethgui\Test\Tool\MockState $state)
    {
        $this->state = $state;
    }

    /**
     *
     * @return \Nethgui\Test\Tool\MockState
     */
    public function getState()
    {
        return $this->state;
    }

    public function invoke(\PHPUnit_Framework_MockObject_Invocation $invocation)
    {
        $methodName = $invocation->methodName;
        $parameters = $invocation->parameters;
        $returnValue = NULL;
        $what = array($methodName, $parameters);
        $this->state = $this->state->exec($what, $returnValue);
        return $returnValue;
    }

    public function toString()
    {
        return \PHPUnit_Util_Type::toString($this->state);
    }

}
