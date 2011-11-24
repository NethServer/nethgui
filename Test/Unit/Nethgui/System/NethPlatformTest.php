<?php
namespace Test\Unit\Nethgui\System;
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
        $this->globalsMock = $this->getMock('\Nethgui\Core\GlobalFunctionWrapper', array('exec'));
        $this->object = new \Nethgui\System\NethPlatform($this->getMock('\Nethgui\Client\UserInterface'));
        $this->object->setGlobalFunctionWrapper($this->globalsMock);
        $this->object->setPolicyDecisionPoint(new \Nethgui\Authorization\PermissivePolicyDecisionPoint());
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

    public function testPdp()
    {
        $db = $this->object->getDatabase('testdb');
        $this->assertSame($this->object->getPolicyDecisionPoint(), $db->getPolicyDecisionPoint());
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

        $exitStatusInfo = $this->object->signalEvent("myEventName");
        $this->assertEquals(0, $exitStatusInfo->getExitStatus());
    }

    public function testSignalEventFail()
    {
        $this->globalsMock
            ->expects($this->once())
            ->method('exec')
            ->with(new \PHPUnit_Framework_Constraint_StringEndsWith("'myEventName'"))
            ->will($this->returnCallBack(array($this, 'exec_failureCallback')));

        $exitStatusInfo = $this->object->signalEvent("myEventName");
        $this->assertEquals(128, $exitStatusInfo->getExitStatus());
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

