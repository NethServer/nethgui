<?php
namespace Nethgui\Test\Unit\Nethgui\Authorization;

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
 * LazyAccessControlResponse Unit test case
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 * @covers \Nethgui\Authorization\LazyAccessControlResponse
 * @covers \Nethgui\Authorization\StringAttributesProvider
 */
class LazyAccessControlResponseTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Nethgui\Authorization\LazyAccessControlResponse
     */
    protected $object;

    protected function setUp()
    {
        $f = function($request, &$message) {
                $message = 'L-A-Z-Y';
                return 0;
            };

        $request = array(
            'X' => \Nethgui\Controller\NullRequest::getInstance()->getUser(),
            'Y' => 'RX',
            'Z' => 'AX'
        );

        $this->object = new \Nethgui\Authorization\LazyAccessControlResponse($f, $request);
    }

    public function testGetMessage()
    {
        $this->assertEquals('L-A-Z-Y', $this->object->getMessage());
    }

    public function testIsAllowed()
    {
        $this->assertTrue($this->object->isAllowed());
    }

    public function testIsDenied()
    {
        $this->assertFalse($this->object->isDenied());
    }

    /**
     * @todo Implement testAsException().
     */
    public function testAsException()
    {
        $this->assertInstanceOf('Exception', $this->object->asException(123));
    }

    /**
     * @todo Implement testGetCode().
     */
    public function testGetCode()
    {
        $this->assertEquals(0, $this->object->getCode());
    }

    public function testCreateDenyResponse()
    {
        $i = \Nethgui\Authorization\LazyAccessControlResponse::createDenyResponse();
        $this->assertTrue($i->isDenied());
    }

    public function testCreateSuccessResponse()
    {
        $i = \Nethgui\Authorization\LazyAccessControlResponse::createSuccessResponse();
        $this->assertTrue($i->isAllowed());
    }

}

