<?php
namespace Test\Unit\Nethgui\Serializer;
class PropSerializerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Nethgui\Serializer\PropSerializer
     */
    protected $object;

    protected function setUp()
    {
        $this->database = $this->getMockBuilder('\Nethgui\System\ConfigurationDatabase')
                ->disableOriginalConstructor()
                ->getMock();

        $this->object = new \Nethgui\Serializer\PropSerializer($this->database, 'TestKey', 'TestProp');
    }

    public function testRead()
    {
        $this->database->expects($this->once())
            ->method('getProp')
            ->with('TestKey', 'TestProp')
            ->will($this->returnValue('OK'));


        $this->assertEquals('OK', $this->object->read());
    }

    public function testWriteValue()
    {
        $this->database->expects($this->once())
            ->method('setProp')
            ->with('TestKey', array('TestProp' => 'VALUE'))
            ->will($this->returnValue(TRUE));

        $this->object->write('VALUE');
    }

    public function testWriteDelete()
    {
        $this->database->expects($this->once())
            ->method('delProp')
            ->with('TestKey', array('TestProp'))
            ->will($this->returnValue(TRUE));

        $this->object->write(NULL);
    }

}

