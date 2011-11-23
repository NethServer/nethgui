<?php
/**
 * @package Tests
 * @subpackage Unit
 */

/**
 * Test class for Nethgui\System\ProcessTest.
 * @package Tests
 * @subpackage Unit
 */
class Nethgui\System\ProcessTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var Nethgui\System\Process
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->arguments = array('arg; ls1', 'arg&2', 'a(r)g3');
        $this->object = new Nethgui\System\Process('echo ${1} ${2} ${3} ${@}', $this->arguments);
        $this->object->setGlobalFunctionWrapper(new MockGlobalFunctionWrapper());
    }

    public function testKill1()
    {
        $this->assertFalse($this->object->kill());
    }

    public function testKill2()
    {
        $this->object->exec();
        $this->assertFalse($this->object->kill());
    }

    public function testExec()
    {
        $this->assertEquals(Nethgui\System\ProcessInterface::STATE_EXITED, $this->object->exec());
    }

    public function test__clone1()
    {
        $c = clone $this->object;
        $this->assertEquals(Nethgui\System\ProcessInterface::STATE_EXITED, $c->exec());
    }

    public function test__clone2()
    {
        $this->object->exec();
        $c = clone $this->object;
        $this->assertEquals(Nethgui\System\ProcessInterface::STATE_EXITED, $c->exec());
        $this->assertFalse($this->object->exec());
    }

    public function testAddArgument()
    {
        $this->object->exec();
        $c = clone $this->object;
        $c->addArgument('added');
        $c->exec();

        $expected = implode(' ', $this->arguments) . ' ' . implode(' ', $this->arguments) . ' added';

        $this->assertEquals($expected, $c->getOutput());
    }

    /**
     * @todo Implement testGetExitStatus().
     */
    public function testGetExitStatus()
    {
        $this->object->exec();
        $this->assertEquals(0, $this->object->getExitStatus());
    }

    public function testGetOutput()
    {
        $this->object->exec();
        $output = $this->object->getOutput();
        $this->assertEquals($output, implode(' ', $this->arguments) . ' ' . implode(' ', $this->arguments));
    }

    public function testGetOutputArray()
    {
        $this->object->exec();
        $output = $this->object->getOutputArray();
        $this->assertEquals($output, array(implode(' ', $this->arguments) . ' ' . implode(' ', $this->arguments)));
    }

    public function testReadExecutionState()
    {
        $this->assertEquals(Nethgui\System\ProcessInterface::STATE_NEW, $this->object->readExecutionState());
        $this->object->exec();
        $this->assertEquals(Nethgui\System\ProcessInterface::STATE_EXITED, $this->object->readExecutionState());
    }

    public function testReadOutput()
    {
        $this->object->exec();
        $output = $this->object->getOutput();
        $buffer = '';

        $buffer = $this->object->readOutput();
        $this->assertEquals($output, $buffer);

        $buffer = $this->object->readOutput();
        $this->assertFalse($buffer);
    }

}

class MockGlobalFunctionWrapper extends Nethgui\Core\GlobalFunctionWrapper
{

    public function exec($command, &$output, &$exitCode)
    {
        $command = substr($command, 5);
        $csv = str_getcsv($command, ' ', "'", '\\');
        $line = implode(' ', $csv);
        $output[] = $line;
        $exitCode = 0;
        return $line;
    }

}