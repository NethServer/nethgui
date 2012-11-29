<?php
namespace Nethgui\Test\Unit\Nethgui\Utility;

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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NethServer. If not, see <http://www.gnu.org/licenses/>.
 */

class PamAuthenticatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Nethgui\Utility\PamAuthenticator
     */
    protected $object;

    protected function setUp()
    {
        $this->object = new \Nethgui\Utility\PamAuthenticator();
        $this->log = new \Nethgui\Log\Nullog();
    }

    private function addPasswordChatExpectations(\PHPUnit_Framework_MockObject_MockObject $mock)
    {

        $mock->expects($this->once())
            ->method('popen')
            ->with('/usr/bin/sudo /sbin/e-smith/pam-authenticate-pw >/dev/null 2>&1', 'w')
            ->will($this->returnValue('PipeDescriptor'));

        $mock->expects($this->once())
            ->method('fwrite')
            ->with('PipeDescriptor', "user\npass")
            ->will($this->returnValue(9));

        $mock->expects($this->once())
            ->method('pclose')
            ->with('PipeDescriptor')
            ->will($this->returnValue(0));
    }

    public function testPam0()
    {
        $phpwrapper = $this->getMock('Nethgui\Utility\PhpWrapper', array('popen', 'error_get_last'));

        $phpwrapper->expects($this->once())
            ->method('popen')
            ->with('/usr/bin/sudo /sbin/e-smith/pam-authenticate-pw >/dev/null 2>&1', 'w')
            ->will($this->returnValue(FALSE));

        $phpwrapper->expects($this->once())
            ->method('error_get_last')
            ->withAnyParameters()
            ->will($this->returnValue(array('type' => 'test', 'message' => 'expected failure', 'file' => __FILE__, 'line' => __LINE__)));

        $object = new \Nethgui\Authorization\User($phpwrapper, $this->log);
        $this->assertFalse($object->authenticate('user', 'pass'));
    }

    /**
     * @fixme This test will fail until https://github.com/sebastianbergmann/phpunit-mock-objects/issues/81 is fixed
     */
    public function testPam1()
    {

        $phpwrapper = $this->getMockBuilder('Nethgui\Utility\PhpWrapper')
            ->disableArgumentCloning()
            ->setMethods(array('popen', 'fwrite', 'pclose', 'exec'))
            ->getMock();

        $this->addPasswordChatExpectations($phpwrapper);

        $phpwrapper->expects($this->once())
            ->method('exec')
            ->with($this->stringStartsWith('/usr/bin/id'), $this->anything(), $this->anything())
            ->will($this->returnCallback(function($cmd, &$output, &$exitCode) {
                        $exitCode = 0;
                        $output = array('g1 g2 g3');
                    }));

        $object = new \Nethgui\Authorization\User($phpwrapper, $this->log);

        $this->assertTrue($object->authenticate('user', 'pass'));
        $this->assertEquals(array('g1', 'g2', 'g3'), $object->getCredential('groups'));
    }

    /**
     * @fixme This test will fail until https://github.com/sebastianbergmann/phpunit-mock-objects/issues/81 is fixed
     */
    public function testPam2()
    {
        $phpwrapper = $this->getMock('Nethgui\Utility\PhpWrapper', array('popen', 'fwrite', 'pclose', 'exec'));

        $this->addPasswordChatExpectations($phpwrapper);

        $phpwrapper->expects($this->once())
            ->method('exec')
            ->withAnyParameters()
            ->will($this->returnCallback(function($cmd, &$output, &$exitCode) {
                        $exitCode = 1;
                        $output = array('error');
                    }));

        $log = $this->getMock('Nethgui\Log\Nullog', array('warning'));
        $log->expects($this->once())
            ->method('warning')
            ->withAnyParameters()
            ->will($this->returnValue($log))
        ;

        $object = new \Nethgui\Authorization\User($phpwrapper, $log);

        $this->assertTrue($object->authenticate('user', 'pass'));
        $this->assertEquals(array(), $object->getCredential('groups'));
    }

    /**
     * @covers Nethgui\Utility\PamAuthenticator::authenticate
     * @todo   Implement testAuthenticate().
     */
    public function testAuthenticate()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Nethgui\Utility\PamAuthenticator::getLog
     * @todo   Implement testGetLog().
     */
    public function testGetLog()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Nethgui\Utility\PamAuthenticator::setLog
     * @todo   Implement testSetLog().
     */
    public function testSetLog()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Nethgui\Utility\PamAuthenticator::setPhpWrapper
     * @todo   Implement testSetPhpWrapper().
     */
    public function testSetPhpWrapper()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

}
