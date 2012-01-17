<?php

namespace Nethgui\Test\Unit\Nethgui\System;

class ConfigurationDatabaseTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Nethgui\System\ConfigurationDatabase
     */
    protected $object;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $globalsMock;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->globalsMock = $this->getMock('\Nethgui\Utility\PhpWrapper', array('exec'));
        $this->object = new \Nethgui\System\ConfigurationDatabase('MOCKDB', $this->getMock('\Nethgui\Authorization\UserInterface'));
        $this->object->setPhpWrapper($this->globalsMock);
        $this->object->setPolicyDecisionPoint(new \Nethgui\Authorization\PermissivePolicyDecisionPoint());
    }

    public function testGetAll1()
    {
        $this->globalsMock
            ->expects($this->once())
            ->method('exec')
            ->with($this->getCommandMatcher('print', array()))
            ->will($this->returnCallBack(array($this, 'exec_getAllCallback')));

        $expected = array();
        for ($i = 0; $i < 5; $i ++ ) {
            $expected['K' . $i] = array('type' => ($i == 3 ? 'F' : 'T'), 'PA' . $i => 'VA' . $i, 'PB' . $i => 'VB' . $i, 'PC' . $i => 'VC' . $i);
        }

        $ret = $this->object->getAll();

        $this->assertEquals($expected, $ret);
    }

    public function testGetAll2()
    {
        $this->globalsMock
            ->expects($this->once())
            ->method('exec')
            ->with($this->getCommandMatcher('print', array()))
            ->will($this->returnCallBack(array($this, 'exec_getAllCallback')));

        $expected = array();
        $i = 3;
        $expected['K' . $i] = array('type' => 'F', 'PA' . $i => 'VA' . $i, 'PB' . $i => 'VB' . $i, 'PC' . $i => 'VC' . $i);

        $ret = $this->object->getAll('F');

        $this->assertEquals($expected, $ret);
    }

    /**
     * Currently the $filter argument is not implemented
     * @expectedException \InvalidArgumentException
     */
    public function testGetAll3()
    {
//        $this->globalsMock
//            ->expects($this->once())
//            ->method('exec')
//            ->with($this->getCommandMatcher('print', array()))
//            ->will($this->returnCallBack(array($this, 'exec_getAllCallback')));
//
//        $expected = array();
//        $i = 2;
//        $expected['K' . $i] = array('type' => 'T', 'PA' . $i => 'VA' . $i, 'PB' . $i => 'VB' . $i, 'PC' . $i => 'VC' . $i);

        $ret = $this->object->getAll('T', 'VA2');

        //$this->assertEquals($expected, $ret);
    }

    public function exec_getAllCallback($command, &$output, &$retval)
    {
        $output = array();

        for ($i = 0; $i < 5; $i ++ ) {
            $output[] = strtr('Ki=T|PAi|VAi|PBi|VBi|PCi|VCi', array('i' => $i, 'T' => ($i == 3 ? 'F' : 'T')));
        }

        $retval = 0;
        return array_slice($output, -1, 1);
    }

    public function testGetKey1()
    {
        $this->globalsMock
            ->expects($this->once())
            ->method('exec')
            ->with($this->getCommandMatcher('get', explode(' ', 'K')))
            ->will($this->returnCallBack(array($this, 'exec_getKeyCallback')));

        $ret = $this->object->getKey('K');

        $this->assertEquals(array('p1' => 'v1', 'p2' => 'v2'), $ret);
    }

    public function exec_getKeyCallback($command, &$output, &$retval)
    {
        $output = array('T|p1|v1|p2|v2', '');
        $retval = 0;
        return array_slice($output, -1, 1);
    }

    /**
     * Implement testSetKey().
     */
    public function testSetKey()
    {
        $this->globalsMock
            ->expects($this->once())
            ->method('exec')
            ->with($this->getCommandMatcher('set', explode(' ', 'K T p1 v1 p2 v2')))
            ->will($this->returnCallBack(array($this, 'exec_success')));

        $ret = $this->object->setKey('K', 'T', array('p1' => 'v1', 'p2' => 'v2'));

        $this->assertEquals(TRUE, $ret);
    }

    /**
     * Implement testDeleteKey()
     */
    public function testDeleteKey()
    {
        $this->globalsMock
            ->expects($this->once())
            ->method('exec')
            ->with($this->getCommandMatcher('delete', array('K')))
            ->will($this->returnCallBack(array($this, 'exec_success')));

        $ret = $this->object->deleteKey('K');

        $this->assertEquals(TRUE, $ret);
    }

    /**
     * Implement testGetKey()
     * @depends testSetKey
     */
    public function testGetType()
    {
        $this->globalsMock
            ->expects($this->once())
            ->method('exec')
            ->with($this->getCommandMatcher('gettype', explode(' ', 'K')))
            ->will($this->returnCallBack(array($this, 'exec_getTypeCallback')));

        $ret = $this->object->getType('K');

        $this->assertEquals('T', $ret);
    }

    public function exec_getTypeCallback($command, &$output, &$retval)
    {
        $output = array('T', '');
        $retval = 0;
        return array_slice($output, -1, 1);
    }

    public function testSetType()
    {
        $this->globalsMock
            ->expects($this->once())
            ->method('exec')
            ->with($this->getCommandMatcher('settype', explode(' ', 'K T')))
            ->will($this->returnCallBack(array($this, 'exec_success')));

        $ret = $this->object->setType('K', 'T');

        $this->assertEquals(TRUE, $ret);
    }

    public function testGetProp1()
    {
        $this->globalsMock
            ->expects($this->once())
            ->method('exec')
            ->with($this->getCommandMatcher('getprop', explode(' ', 'K P')))
            ->will($this->returnCallback(array($this, 'exec_getpropCallback')));

        $ret = $this->object->getProp("K", "P");

        $this->assertEquals('V', $ret);
    }

    public function exec_getpropCallback($command, &$output, &$retval)
    {
        $output = array('V', '');
        $retval = 0;
        return array_slice($output, -1, 1);
    }

    public function testSetProp1()
    {
        $this->globalsMock
            ->expects($this->once())
            ->method('exec')
            ->with($this->getCommandMatcher('setprop', explode(' ', 'K p1 1 p2 2')))
            ->will($this->returnValue(''));

        $ret = $this->object->setProp("K", array('p1' => '1', 'p2' => '2'));

        $this->assertEquals(TRUE, $ret);
    }

    public function testDelProp()
    {
        $this->globalsMock
            ->expects($this->once())
            ->method('exec')
            ->with($this->getCommandMatcher('delprop', explode(' ', 'K A B C')))
            ->will($this->returnCallBack(array($this, 'exec_success')));

        $ret = $this->object->delProp('K', array('A', 'B', 'C'));

        $this->assertEquals(TRUE, $ret);
    }

    public function exec_success($command, &$output, &$retval)
    {
        $output = array('');
        $retval = 0;
        return array_slice($output, -1, 1);
    }

    private function getCommandMatcher($command, $args)
    {
        array_unshift($args, 'MOCKDB', $command);
        $commandLine = "/usr/bin/sudo /sbin/e-smith/db " . implode(' ', array_map('escapeshellarg', $args));
        return new \PHPUnit_Framework_Constraint_StringMatches($commandLine);
    }

}
