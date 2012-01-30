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
 * JsonPolicyDecisionPoint Unit test case
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 * @covers \Nethgui\Authorization\JsonPolicyDecisionPoint
 */
class JsonPolicyDecisionPointTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Nethgui\Authorization\JsonPolicyDecisionPoint
     */
    protected $object;

    /**
     *
     * @var Mock
     */
    private $phpMock;

    protected function setUp()
    {
        $this->phpMock = $this->getMockBuilder('Nethgui\Utility\PhpWrapper')
            ->setMethods(array('file_get_contents'))
            ->getMock()
        ;

        $this->object = new \Nethgui\Authorization\JsonPolicyDecisionPoint(function($name) {
                    return '/prefix/' . str_replace('\\', '/', $name);
                }, $this->phpMock);
    }

    private function getSubject($username = FALSE)
    {
        return \Nethgui\Test\Tool\MockFactory::getAuthenticationSubject($this, $username);
    }

    private function loadPolicy($policy)
    {
        $this->phpMock->expects($this->once())
            ->method('file_get_contents')
            ->with('/prefix/Nethgui/Authorization/BasicPolicy.json')
            ->will($this->returnValue($policy))
        ;
    }

    public function testAuthorizeLogin1()
    {
        $this->loadPolicy('[

            
            {
                "Id": 2,
                "Final": true,
                "Effect": "ALLOW",
                "Subject": "admin",
                "Action": "*",
                "Resource": "*",

                "Description":
                    "Admin has the full powa"
            }
            ,
            {
                "Id": 3,
                "Effect": "DENY",
                "Subject": "*",
                "Action": "*",
                "Resource": "PROCESSOR*",
                "Description":
                    "Unauthenticated users cannot access any PROCESSOR"
            }
            ,
            {
                "Id": 2,
                "Effect": "DENY",
                "Subject": "admin",
                "Action": "*",
                "Resource": "*",

                "Description":
                    "Try to override rule#2"
            }
            ,
            {
                "Id": 1,
                "Effect": "ALLOW",
                "Subject": ".authenticated IS TRUE",
                "Action": "USE || SUSPEND || RESUME",
                "Resource": "PROCESSOR1",
                "Description":
                    "Authenticated users have access to PROCESSOR1"
            }
            ,
            {
                "Id": 3,
                "Effect": "DENY",
                "Subject": "*",
                "Action": "*",
                "Resource": "PROC*",
                "Description":
                    "Unauthenticated users cannot access any PROCESSOR (Override)"
            }
            ]');



        $assertions = array(
            0 => array($this->object->authorize($this->getSubject('admin'), 'PROCESSOR2', 'HALT'), TRUE),
            1 => array($this->object->authorize($this->getSubject('dude'), 'PROCESSOR1', 'USE'), TRUE),
            2 => array($this->object->authorize($this->getSubject('dude'), 'PROCESSOR1', 'HALT'), FALSE),
            3 => array($this->object->authorize($this->getSubject('dude'), 'PROCESSOR2', 'USE'), FALSE),
            4 => array($this->object->authorize($this->getSubject(FALSE), 'PROCESSOR3', 'USE'), FALSE)
        );

        foreach ($assertions as $index => $assertion) {
            list($auth, $pass) = $assertion;

            if ( ! $auth instanceof \Nethgui\Authorization\AccessControlResponseInterface)
                continue;


            $cond = $pass ? $auth->isAllowed() : $auth->isDenied();
            $failMsg = sprintf('assertion[%d]: Rule#%d - %s', $index, $auth->getCode(), $auth->getMessage());
            $this->assertTrue($cond, $failMsg);
            
        }
    }

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testAuthorizeLogin2()
    {
        // invalid json:
        $this->loadPolicy('error');

        $subject = $this->getSubject();
        $resource = 'Nethgui\Module\Login';
        $action = \Nethgui\Authorization\PolicyDecisionPointInterface::QUERY;

        $this->object->authorize($subject, $resource, $action)->isAllowed();
    }

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testAuthorizeLogin3()
    {
        // valid json, invalid policy
        $this->loadPolicy('"error"');

        $subject = $this->getSubject();
        $resource = 'Nethgui\Module\Login';
        $action = \Nethgui\Authorization\PolicyDecisionPointInterface::QUERY;

        $this->object->authorize($subject, $resource, $action)->isAllowed();
    }

    public function testAuthorizeLogin4()
    {
        // empty policy
        $this->loadPolicy('[]');

        $subject = $this->getSubject('admin');
        $resource = 'None';
        $action = 'None';

        $this->object->authorize($subject, $resource, $action)->isAllowed();
    }

    public function testSetPhpWrapper()
    {
        $php = new \Nethgui\Utility\PhpWrapper();
        $this->assertSame($this->object, $this->object->setPhpWrapper($php));
    }

    public function testGetLog()
    {
        $this->assertInstanceOf('Nethgui\Log\LogInterface', $this->object->getLog());
    }

    public function testSetLog()
    {
        $log = new \Nethgui\Log\Nullog();
        $this->assertSame($this->object, $this->object->setLog($log));
        $this->assertInstanceOf('Nethgui\Log\LogInterface', $this->object->getLog());
    }

}
