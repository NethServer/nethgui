<?php
namespace Nethgui\Test\Unit\Nethgui\System;

/**
 *
 * @covers \Nethgui\System\EsmithDatabase
 */
class EsmithDatabaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Nethgui\System\EsmithDatabase
     */
    protected $object;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $execw;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {

        $this->execw = new PhpWrapperExec();
        $this->object = new \Nethgui\System\EsmithDatabase('MOCKDB', $this->getMock('\Nethgui\Authorization\UserInterface'));
        $this->object
            ->setPolicyDecisionPoint(new \Nethgui\Test\Tool\PermissivePolicyDecisionPoint())
            ->setPhpWrapper($this->execw)
        ;
    }

    public function testFailPdp()
    {
        $object = new \Nethgui\System\EsmithDatabase('MOCKDB', $this->getMock('\Nethgui\Authorization\UserInterface'));
        $this->object->setPolicyDecisionPoint(new \Nethgui\Test\Tool\StaticPolicyDecisionPoint(FALSE))
            ->setPhpWrapper($this->execw);

        foreach (array(
        array('delProp', array('K', array('pi'))),
        array('deleteKey', array('K')),
        array('getAll', array()),
        array('getKey', array('K')),
        array('getType', array('K')),
        array('setKey', array('K', 'T', array('p1', 'v1'))),
        array('setProp', array('K', array())),
        array('setType', array('K', 'T')),
        ) as $method) {
            try {
                call_user_func_array(array($this->object, $method[0]), $method[1]);
            } catch (\Nethgui\Exception\AuthorizationException $e) {
                continue;
            }
            $this->fail($method[0]);
        }
    }

    public function testGetAll1()
    {
        $this->execw
            ->setCommandMatcher('print', array())
            ->setExecImplementation(array($this, 'exec_getAllCallback'));

        $expected = array();
        for ($i = 0; $i < 5; $i ++ ) {
            $expected['K' . $i] = array('type' => ($i == 3 ? 'F' : 'T'), 'PA' . $i => 'VA' . $i, 'PB' . $i => 'VB' . $i, 'PC' . $i => 'VC' . $i);
        }

        $ret = $this->object->getAll();

        $this->assertEquals($expected, $ret);
    }

    public function testGetAll2()
    {
        $this->execw
            ->setCommandMatcher('print', array())
            ->setExecImplementation(array($this, 'exec_getAllCallback'));


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
        $ret = $this->object->getAll('T', 'VA2');
    }

    public function exec_getAllCallback($command, &$output, &$retval)
    {
        if ( ! isset($output)) {
            $output = array();
        }

        for ($i = 0; $i < 5; $i ++ ) {
            $output[] = strtr('Ki=T|PAi|VAi|PBi|VBi|PCi|VCi', array('i' => $i, 'T' => ($i == 3 ? 'F' : 'T')));
        }

        $retval = 0;
        return array_slice($output, -1, 1);
    }

    public function testGetKey1()
    {
        $this->execw
            ->setCommandMatcher('get', explode(' ', 'K'))
            ->setExecImplementation(array($this, 'exec_getKeyCallback'));

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
        $this->execw
            ->setCommandMatcher('set', explode(' ', 'K T p1 v1 p2 v2'))
            ->setExecImplementation(array($this, 'exec_success'));


        $ret = $this->object->setKey('K', 'T', array('p1' => 'v1', 'p2' => 'v2'));

        $this->assertEquals(TRUE, $ret);
    }

    /**
     * Implement testDeleteKey()
     */
    public function testDeleteKey()
    {
        $this->execw
            ->setCommandMatcher('delete', array('K'))
            ->setExecImplementation(array($this, 'exec_success'));


        $ret = $this->object->deleteKey('K');

        $this->assertEquals(TRUE, $ret);
    }

    /**
     * Implement testGetKey()
     * @depends testSetKey
     */
    public function testGetType()
    {
        $this->execw
            ->setCommandMatcher('gettype', explode(' ', 'K'))
            ->setExecImplementation(array($this, 'exec_getTypeCallback'));

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
        $this->execw
            ->setCommandMatcher('settype', explode(' ', 'K T'))
            ->setExecImplementation(array($this, 'exec_success'));

        $ret = $this->object->setType('K', 'T');

        $this->assertEquals(TRUE, $ret);
    }

    public function testGetProp1()
    {
        $this->execw
            ->setCommandMatcher('getprop', explode(' ', 'K P'))
            ->setExecImplementation(array($this, 'exec_getpropCallback'));

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
        $this->execw
            ->setCommandMatcher('setprop', explode(' ', 'K p1 1 p2 2'));

        $ret = $this->object->setProp("K", array('p1' => '1', 'p2' => '2'));

        $this->assertEquals(TRUE, $ret);
    }

    public function testDelProp()
    {
        $this->execw
            ->setCommandMatcher('delprop', explode(' ', 'K A B C'))
            ->setExecImplementation(array($this, 'exec_success'));

        $ret = $this->object->delProp('K', array('A', 'B', 'C'));

        $this->assertEquals(TRUE, $ret);
    }

    public function exec_success($command, &$output, &$retval)
    {
        $output = array('');
        $retval = 0;
        return array_slice($output, -1, 1);
    }

}

class PhpWrapperExec extends \Nethgui\Utility\PhpWrapper
{
    /**
     * @var callable
     */
    private $execImplementation;
    private $commandLine;

    public function setExecImplementation($execImplementation)
    {
        $this->execImplementation = $execImplementation;
        return $this;
    }

    public function setCommandMatcher($command, $args)
    {
        array_unshift($args, 'MOCKDB', $command);
        $commandLine = "/usr/bin/sudo /sbin/e-smith/db " . implode(' ', array_map('escapeshellarg', $args));
        $this->commandLine = $commandLine;
        return $this;
    }

    public function exec($command, &$output, &$retval)
    {
        \PHPUnit_Framework_Assert::assertEquals($this->commandLine, $command);
        if ( ! is_callable($this->execImplementation)) {
            return 0;
        }
        return call_user_func_array($this->execImplementation, array($command, &$output, &$retval));
    }

}