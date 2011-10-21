<?php
/**
 * @package Tests
 * @subpackage Unit
 */

/**
 * Test class for Nethgui_Core_SystemCommandTest.
 * @package Tests
 * @subpackage Unit
 */
class Nethgui_Core_SystemCommandDetachedTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var Nethgui_Core_SystemCommand
     */
    protected $object;
    protected $outputFile;

    /**
     *
     * @var Test_Tool_GlobalFunctionWrapperTimedForDetachedCommand
     */
    private $simulation;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->arguments = array('arg; ls1', 'arg&2', 'a(r)g3');
        $this->outputFile = tempnam('/tmp', 'ngtest-');
        $this->object = new Nethgui_Core_SystemCommandDetached('${1} ${2} ${3} ${@}', $this->arguments);
        $this->simulation = new Test_Tool_GlobalFunctionWrapperTimedForDetachedCommand();
        $this->object->setGlobalFunctionWrapper($this->simulation);
    }

    public function testGetExecutionState1()
    {
        $this->assertEquals(Nethgui_Core_SystemCommandInterface::STATE_NEW, $this->object->readExecutionState());
        $this->object->exec();
        $this->assertEquals(Nethgui_Core_SystemCommandInterface::STATE_RUNNING, $this->object->readExecutionState());
        $this->simulation->timeStep();
        $this->assertEquals(Nethgui_Core_SystemCommandInterface::STATE_EXITED, $this->object->readExecutionState());
    }

    public function testKill1()
    {
        $this->assertFalse($this->object->kill());
    }

    public function testKill2()
    {
        $this->object->exec();
        $this->assertTrue($this->object->kill());
    }

    public function testExec1()
    {
        $this->assertEquals(Nethgui_Core_SystemCommandInterface::STATE_RUNNING, $this->object->exec());
    }

    public function test__clone1()
    {
        $c = clone $this->object;
        $this->assertEquals(Nethgui_Core_SystemCommandInterface::STATE_RUNNING, $c->exec());
    }

    public function test__clone2()
    {
        $this->object->exec();
        $c = clone $this->object;
        $this->assertEquals(Nethgui_Core_SystemCommandInterface::STATE_RUNNING, $c->exec());
        $this->assertFalse($this->object->exec());
    }

    public function testGetExitStatus1()
    {
        $this->object->exec();
        $this->assertEquals(FALSE, $this->object->getExitStatus());
    }

    public function testGetExitStatus2()
    {
        $this->object->exec();
        $this->simulation->timeStep();
        $this->assertEquals(0, $this->object->getExitStatus());
    }

    public function testGetOutput()
    {
        $this->object->exec();
        $this->simulation->timeStep();
        $this->assertRegExp('/^contents of /', $this->object->getOutput());
    }

    public function testGetOutputArray()
    {
        $this->object->exec();
        $this->simulation->timeStep();
        $output = $this->object->getOutputArray();
        $this->assertInternalType('array', $output);
        $this->assertRegExp('/^contents of /', $output[0]);
    }

    /**
     * Serialize a new process
     */
    public function testSerialize1()
    {
        $data = unserialize($this->object->serialize());

        $this->assertRegExp('#^/tmp/.*$#', array_shift($data)); // errorFile
        $this->assertRegExp('#^/tmp/.*$#', array_shift($data)); // outputFile
        $this->assertNull(array_shift($data)); // processId
        $this->assertNull(array_shift($data)); // exitStatus
        $this->assertInstanceOf('Nethgui_Core_GlobalFunctionWrapper', array_shift($data)); // globalFunctionWrapper
    }

    /**
     * Serialize a running process
     */
    public function testSerialize2()
    {
        $this->object->exec();

        $data = unserialize($this->object->serialize());

        $this->assertRegExp('#^/tmp/.*$#', array_shift($data)); // errorFile
        $this->assertRegExp('#^/tmp/.*$#', array_shift($data)); // outputFile
        $this->assertGreaterThan(0, array_shift($data)); // processId
        $this->assertNull(array_shift($data)); // exitStatus
        $this->assertInstanceOf('Nethgui_Core_GlobalFunctionWrapper', array_shift($data)); // globalFunctionWrapper
    }

    /**
     * Serialize an exited process
     */
    public function testSerialize3()
    {
        $this->object->exec();
        $this->assertEquals(Nethgui_Core_SystemCommandInterface::STATE_RUNNING, $this->object->readExecutionState());
        $this->simulation->timeStep();

        // Force an internal object state update before serializing:
        $this->assertEquals(Nethgui_Core_SystemCommandInterface::STATE_EXITED, $this->object->readExecutionState());

        $data = unserialize($this->object->serialize());

        $this->assertRegExp('#^/tmp/.*$#', array_shift($data)); // errorFile
        $this->assertRegExp('#^/tmp/.*$#', array_shift($data)); // outputFile
        $this->assertGreaterThan(0, array_shift($data)); // processId
        $this->assertEquals(0, array_shift($data)); // exitStatus
        $this->assertInstanceOf('Nethgui_Core_GlobalFunctionWrapper', array_shift($data)); // globalFunctionWrapper
    }

}

class Test_Tool_GlobalFunctionWrapperTimedForDetachedCommand extends Nethgui_Core_GlobalFunctionWrapper
{

    private $instantNames = array('START', 'COMMAND_RUNNING', 'COMMAND_COMPLETED');
    private $currentInstant = 0;

    public function timeStep($units = 1)
    {
        $this->currentInstant += $units;
    }

    public function getInstantName()
    {
        if ( ! isset($this->instantNames[$this->currentInstant])) {
            return '<UNDEFINED>';
        }

        return $this->instantNames[$this->currentInstant];
    }

    public function file_get_contents($fileName)
    {
        if ($this->getInstantName() == 'COMMAND_COMPLETED') {
            return "contents of " . $fileName;
        } else {
            return '';
        }
    }

    public function exec($command, &$output, &$exitCode)
    {
        if (preg_match('/^\/bin\/kill 1234/', $command) > 0) {
            if ($this->currentInstant > 0) {
                $output[] = '';
                $exitCode = 0;
                return $output[0];
            } else {
                $output[] = 'No such process';
                $exitCode = 0;
                return $output[1];
            }
        } else if (preg_match('/^nohup .*/', $command) > 0) {
            if ($this->getInstantName() == 'START') {
                $output[] = '1234';
                $exitCode = 0;
                $this->timeStep();
                return $output[0];
            }
        }

        throw new InvalidArgumentException(sprintf('Command `%s` not defined at instant "%s"', $command, $this->getInstantName()));
    }

    public function is_readable($file)
    {
        if ($this->getInstantName() == 'COMMAND_RUNNING') {
            return TRUE;
        }
        return FALSE;
    }

}