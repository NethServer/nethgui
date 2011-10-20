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
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->arguments = array('arg; ls1', 'arg&2', 'a(r)g3');
        $this->outputFile = tempnam('/tmp', 'ngtest-');
        $this->object = new Nethgui_Core_SystemCommandDetached('${1} ${2} ${3} ${@}', $this->arguments);
        $this->object->setGlobalFunctionWrapper(new Nethgui_Core_SystemCommandDetachedTest_Core_GlobalFunctionWrapper());
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

    /**
     * @todo Implement testGetExitStatus().
     */
    public function testGetExitStatus()
    {
        $this->object->exec();
        $this->assertEquals(FALSE, $this->object->getExitStatus());
    }

    public function testGetOutput()
    {
        $this->object->exec();
        $output = $this->object->getOutput();
        $this->assertRegExp('/^contents of /', $output);
    }

    public function testGetOutputArray()
    {
        $this->object->exec();
        $output = $this->object->getOutputArray();
        $this->assertInternalType('array', $output);
        $this->assertRegExp('/^contents of /', $output[0]);
    }

    public function testGetExecutionState1()
    {
        $this->assertEquals(Nethgui_Core_SystemCommandInterface::STATE_NEW, $this->object->getExecutionState());
        $this->object->exec();
        $this->assertEquals(Nethgui_Core_SystemCommandInterface::STATE_RUNNING, $this->object->getExecutionState());
    }

}

class Nethgui_Core_SystemCommandDetachedTest_Core_GlobalFunctionWrapper extends Nethgui_Core_GlobalFunctionWrapper
{

    public function file_get_contents($fileName)
    {
        return "contents of " . $fileName;
    }

    public function exec($command, &$output, &$exitCode)
    {
        if (preg_match('/^\/bin\/kill 999/', $command) > 0) {
            $output[] = '';
            $exitCode = 0;
        } else if (preg_match('/^nohup .*/', $command) > 0) {
            $output[] = '999';
            $exitCode = 0;
        } else {
            throw new InvalidArgumentException(sprintf('Unknown command "%s"', $command));
        }

        return $output[0];
    }

}