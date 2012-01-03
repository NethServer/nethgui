<?php
namespace Test\Unit\Nethgui\Client;

class CommandTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Nethgui\Client\Command
     */
    protected $object;

    /**
     *
     * @var \Nethgui\Core\ViewInterface
     */
    private $origin;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->origin = $this->getMockBuilder('Nethgui\Core\ViewInterface')->getMock();
        $this->object = new \Nethgui\Client\Command($this->origin, '../Id', 'test', array(1, 'A'));
    }

    public function testExecuteSuccess()
    {
        $receiver = $this->createReceiverMock();
        $this->object->setReceiver($receiver)->execute();
    }

    public function testExecuteAlreadyFail()
    {
        $this->testExecuteSuccess();
        try {
            $this->object->execute();
        } catch (\Exception $ex) {
            $this->assertInstanceOf('LogicException', $ex);
        }
    }

    private function createReceiverMock()
    {
        $receiver = $this->getMockBuilder('Nethgui\Core\CommandReceiverInterface')
            ->setMethods(array('executeCommand'))
            ->getMock();

        $receiver->expects($this->once())
            ->method('executeCommand')
            ->with($this->origin, '../Id', 'test', array(1, 'A'))
            ->will($this->returnValue('success'));

        return $receiver;
    }

    public function testGetOrigin()
    {
        $this->assertSame($this->origin, $this->object->getOrigin());
    }

    public function testGetSelector()
    {
        $this->assertEquals('../Id', $this->object->getSelector());
    }

    public function testSetSelector()
    {
        $this->object->setSelector('/Id2');
        $this->assertEquals('/Id2', $this->object->getSelector());
    }

    public function testExecuteFail()
    {
        try {
            $this->object->execute();
        } catch (\Exception $ex) {
            $this->assertInstanceOf('LogicException', $ex);
        }
    }

    public function testIsExecuted1()
    {
        $this->testExecuteSuccess();
        $this->assertTrue($this->object->isExecuted());
    }

    public function testIsExecuted2()
    {
        $this->testExecuteFail();
        $this->assertFalse($this->object->isExecuted());
    }

    public function testIsExecuted3()
    {
        $this->testExecuteSuccess();
        $this->assertTrue($this->object->isExecuted());
        try {
            $this->object->execute();
        } catch (\Exception $ex) {
            $this->assertInstanceOf('LogicException', $ex);
        }
    }

}

