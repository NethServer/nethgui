<?php
namespace Test\Unit\Nethgui\Serializer;
class KeySerializerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Nethgui\Serializer\KeySerializer
     */
    protected $object;

    protected function setUp()
    {
        $this->database = $this->getMockBuilder('\Nethgui\System\ConfigurationDatabase')
                ->disableOriginalConstructor()
                ->getMock();

        $this->object = new \Nethgui\Serializer\KeySerializer($this->database, 'TestKey');
    }

    public function testRead()
    {
        $this->database->expects($this->once())
            ->method('getType')
            ->with('TestKey')
            ->will($this->returnValue('VALUE'));

        $this->assertEquals('VALUE', $this->object->read());
    }

    public function testWriteValue()
    {
        $this->database->expects($this->once())
            ->method('setType')
            ->with('TestKey', 'VALUE')
            ->will($this->returnValue(TRUE));

        $this->object->write('VALUE');
    }

    public function testWriteDelete()
    {
        $this->database->expects($this->once())
            ->method('deleteKey')
            ->with('TestKey')
            ->will($this->returnValue(TRUE));

        $this->object->write(NULL);
    }

}

