<?php
namespace Nethgui\Test\Unit\Nethgui\System;
class NethPlatformTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Nethgui\System\NethPlatform
     */
    protected $object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $globalsMock;

    /**
     * Sets up the fixture
     */
    protected function setUp()
    {
        $mockUser = $this->getMockBuilder('\Nethgui\Authorization\UserInterface')
            ->disableOriginalConstructor()
            ->setMethods(array('authenticate', 'getAuthorizationAttribute', 'asAuthorizationString','isAuthenticated', 'getCredential', 'getCredentials', 'hasCredential', 'getLanguageCode'))
            ->getMock();

        $mockSession = $this->getMockBuilder('\Nethgui\Utility\SessionInterface')
            ->disableOriginalConstructor()
            ->setMethods(array('retrieve', 'store', 'login', 'logout'))
            ->getMock();

        $mockSession->expects($this->atLeastOnce())
            ->method('retrieve')
            ->with('Nethgui\System\NethPlatform')
            ->will($this->returnValue(new \ArrayObject()));


        $pdpMock = $this->getMock('Nethgui\Authorization\PolicyDecisionPointInterface');

        $pdpMock->expects($this->any())
            ->method('authorize')
            ->will($this->returnValue(\Nethgui\Authorization\LazyAccessControlResponse::createSuccessResponse()));

        $this->globalsMock = $this->getMock('\Nethgui\Utility\PhpWrapper');
        $this->object = new \Nethgui\System\NethPlatform($mockUser);
        $this->object->setSession($mockSession);
        $this->object->setPhpWrapper($this->globalsMock);
        $this->object->setPolicyDecisionPoint($pdpMock);
    }

    public function exec_successCallback($command, &$output, &$retval)
    {
        $output = array('');
        $retval = 0;
        return array_slice($output, -1, 1);
    }

    public function exec_failureCallback($command, &$output, &$retval)
    {
        $output = array('');
        $retval = 128;
        return array_slice($output, -1, 1);
    }


    /**
     * Asserts a database object interface has the same PDP of the fixture.
     */
    public function testGetDatabase()
    {
        $db = $this->object->getDatabase('testdb');
        $this->assertInstanceOf('\Nethgui\System\ConfigurationDatabase', $db);
    }

    public function testSignalEventSuccess()
    {
        $this->globalsMock
            ->expects($this->once())
            ->method('exec')
            ->with(new \PHPUnit_Framework_Constraint_StringEndsWith("'myEventName'"))
            ->will($this->returnCallBack(array($this, 'exec_successCallback')));

        $process = $this->object->signalEvent("myEventName");
        $this->assertEquals(0, $process->getExitCode());
    }

    public function testSignalEventFail()
    {
        $this->globalsMock
            ->expects($this->once())
            ->method('exec')
            ->with(new \PHPUnit_Framework_Constraint_StringEndsWith("'myEventName'"))
            ->will($this->returnCallBack(array($this, 'exec_failureCallback')));

        $process = $this->object->signalEvent("myEventName");
        $this->assertEquals(128, $process->getExitCode());
    }

    public function testGetMapAdapter()
    {
        $adapter = $this->object->getMapAdapter(
            array($this, 'readCallback'), array($this, 'writeCallback'), array(
            array('testdb', 'testkey1'),
            array('testdb', 'testkey2', 'testpropA'),
            array('testdb', 'testkey3', 'testpropB'),
            )
        );
        $this->assertInstanceOf('\Nethgui\Adapter\AdapterInterface', $adapter);
    }

    public function readCallback($key1, $propA, $propB)
    {
        return implode(',', array($key1, $propA, $propB));
    }

    public function writeCallback($value)
    {
        return explode(',', $value);
    }

    public function testGetIdentityAdapter()
    {
        $this->assertInstanceOf('\Nethgui\Adapter\AdapterInterface', $this->object->getIdentityAdapter('testdb', 'testkey'));
        $this->assertInstanceOf('ArrayAccess', $this->object->getIdentityAdapter('testdb', 'testkey', NULL, ','));
    }

}

