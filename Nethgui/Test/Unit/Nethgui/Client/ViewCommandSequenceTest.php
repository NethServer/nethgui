<?php
namespace Test\Unit\Nethgui\Client;

/**
 * @covers \Nethgui\View\ViewCommandSequence
 */
class ViewCommandSequenceTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Nethgui\View\ViewCommandSequence
     */
    protected $object;

    /**
     *
     * @var \Nethgui\View\ViewInterface
     */
    private $origin;

    protected function setUp()
    {
        $this->origin = $this->getMock('Nethgui\View\ViewInterface');
        $selector = 'Selector';

        $this->object = new \Nethgui\View\ViewCommandSequence($this->origin, $selector);
    }

    public function test__call1()
    {
        $this->object
            ->__call('method1', array(1, 2, 3))
            ->__call('method2', array(1, 'a', 'b'))
            ->setReceiver($this->getMock('Nethgui\Core\CommandReceiverInterface'));
        ;

        $this->object->execute();
    }

    public function test__call2()
    {
        $this->object->method3('M', 3)->method4('M', 5)->setReceiver($this->getMock('Nethgui\Core\CommandReceiverInterface'));

        $this->object->execute();
    }

    public function testAddCommand()
    {
        $this->assertSame($this->object, $this->object->addCommand($this->getMock('Nethgui\Core\CommandInterface')));
    }

    public function testExecuteEmpty()
    {
        $this->object->execute();
    }

    public function testIsExecutedTrue()
    {
        $this->object->execute();
        $this->assertTrue($this->object->isExecuted());
    }

    public function testIsExecutedFalse()
    {
        $this->assertFalse($this->object->isExecuted());
    }

    public function testSetReceiver()
    {
        $this->assertSame($this->object, $this->object->setReceiver($this->getMock('Nethgui\Core\CommandReceiverInterface')));
    }

    public function testGetOrigin()
    {
        $this->assertSame($this->origin, $this->object->getOrigin());
    }

    public function testGetSelector()
    {
        $this->assertEquals('Selector', $this->object->getSelector());
    }

}

