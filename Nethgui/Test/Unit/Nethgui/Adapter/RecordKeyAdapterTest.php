<?php
namespace Nethgui\Test\Unit\Nethgui\Adapter;

/**
 * @covers Nethgui\Adapter\RecordKeyAdapter
 */
class RecordKeyAdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Nethgui\Adapter\RecordKeyAdapter
     */
    protected $object;

    /**
     *
     * @var \PHPUnit_Framework_MockObject_MockObject;
     */
    protected $recordMock;

    protected function setUp()
    {
        $this->recordMock = $this->getMockBuilder('Nethgui\Adapter\RecordAdapter')
            ->disableOriginalConstructor()
            ->setMethods(array('setKeyValue', 'getKeyValue'))
            ->getMock();

        $this->object = new \Nethgui\Adapter\RecordKeyAdapter($this->recordMock);
    }

    /**
     * @expectedException \LogicException 
     */
    public function testDelete()
    {
        $this->object->delete();
    }

    /**
     * covers \Nethgui\Adapter\RecordKeyAdapter::get()
     */
    public function testGet1()
    {
        $this->recordMock->expects($this->once())
            ->method('getKeyValue')
            ->will($this->returnValue(NULL));

        $this->assertNull($this->object->get());
    }

    /**
     * Never report modifications, as RecordAdapter allow the key value to 
     * be set one time only.
     * covers \Nethgui\Adapter\RecordKeyAdapter::isModified()
     */
    public function testIsModified()
    {
        $this->assertFalse($this->object->isModified());
    }

    /**
     * covers \Nethgui\Adapter\RecordKeyAdapter::save()
     */
    public function testSave()
    {
        $this->assertFalse($this->object->save());
    }

    /**
     * covers \Nethgui\Adapter\RecordKeyAdapter::set()
     */
    public function testSet1()
    {
        $this->recordMock->expects($this->at(0))
            ->method('setKeyValue')
            ->with('K');

        $this->recordMock->expects($this->at(1))
            ->method('getKeyValue')
            ->will($this->returnValue('K'));

        $this->object->set('K');
        $this->assertEquals('K', $this->object->get());
    }

}
