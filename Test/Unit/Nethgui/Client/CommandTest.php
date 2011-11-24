<?php
namespace Test\Unit\Nethgui\Client;
class CommandTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Nethgui\Client\Command
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new \Nethgui\Client\Command('test', array(1, 'A'));
    }

    public function testExecuteSuccess()
    {
        $receiver = $this->createReceiverMock();

        $retval = $this->object->setReceiver($receiver)->execute();

        $this->assertEquals('success', $retval);
    }

    public function testExecuteAlreadyFail()
    {
        $this->testExecuteSuccess();
        try {
            $this->object->execute();
        } catch (Exception $ex) {
            $this->assertInstanceOf('LogicException', $ex);
        }
    }

    private function createReceiverMock()
    {
        $receiver = $this->getMockBuilder('\Nethgui\Core\CommandReceiverInterface')
            ->setMethods(array('executeCommand'))
            ->getMock();

        $receiver->expects($this->once())
            ->method('executeCommand')
            ->with('test', array(1, 'A'))
            ->will($this->returnValue('success'));

        return $receiver;
    }

    private function createReceiverAggregateMock()
    {
        $aggregate = $this->getMockBuilder('\Nethgui\Core\CommandReceiverAggregateInterface')
            ->setMethods(array('getCommandReceiver'))
            ->getMock();

        $aggregate->expects($this->once())
            ->method('getCommandReceiver')
            ->will($this->returnValue($this->createReceiverMock()));

        return $aggregate;
    }

    public function testExecuteFail()
    {
        try {
            $this->object->execute();
        } catch (Exception $ex) {
            $this->assertInstanceOf('LogicException', $ex);
        }
    }

    public function testSetReceiver()
    {
        $retval = $this->object->setReceiver($this->createReceiverAggregateMock());
        $this->assertSame($this->object, $retval);
        $this->object->execute();
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

}

