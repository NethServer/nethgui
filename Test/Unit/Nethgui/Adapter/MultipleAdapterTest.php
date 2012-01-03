<?php
namespace Test\Unit\Nethgui\Adapter;

/**
 * @covers \Nethgui\Adapter\MultipleAdapter
 */
class MultipleAdapterTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Nethgui\Adapter\MultipleAdapter
     */
    protected $object;
    /**
     *
     * @var array
     */
    private $serializers;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->serializers = array(
                $this->getMockBuilder('\Nethgui\Serializer\PropSerializer')
                ->disableOriginalConstructor()
                ->getMock(),

                $this->getMockBuilder('\Nethgui\Serializer\PropSerializer')
                ->disableOriginalConstructor()
                ->getMock(),
        );

        $this->object = new \Nethgui\Adapter\MultipleAdapter(
                array($this, 'readerCallback'),
                array($this, 'writerCallback'),
                $this->serializers
        );
    }

    public function readerCallback($check, $v)
    {
        if (empty($check) || $check == 'disabled') {
            return 'disabled';
        }

        return $v;
    }

    public function writerCallback($v)
    {
        if ($v === NULL ||
            $v == 'disabled') {
            return array('disabled', NULL);
        }

        return array('enabled', $v);
    }

    private function returnValues($v1, $v2)
    {
        $this->serializers[0]->expects($this->once())
            ->method('read')
            ->will($this->returnValue($v1));

        $this->serializers[1]->expects($this->once())
            ->method('read')
            ->will($this->returnValue($v2));
    }

    private function expectWrites($v1, $v2)
    {
        $this->serializers[0]->expects($this->once())
            ->method('write')
            ->with($v1);

        $this->serializers[1]->expects($this->once())
            ->method('write')
            ->with($v2);
    }

    public function testGet1()
    {
        $this->returnValues('enabled', '99');
        $this->assertEquals('99', $this->object->get());
    }

    public function testGet2()
    {
        $this->returnValues('disabled', '99');
        $this->assertEquals('disabled', $this->object->get());
    }

    public function testSetUnchanged()
    {
        $this->returnValues('enabled', '99');
        $this->object->set('99');
        $this->assertFalse($this->object->isModified());
    }

    public function testSetChanged()
    {
        $this->returnValues('disabled', '');
        $this->object->set('100');
        $this->assertTrue($this->object->isModified());
    }

    public function testDelete()
    {
        $this->returnValues('enabled', '99');
        $this->object->delete();
        $this->assertTrue($this->object->isModified());
    }

    public function testSaveChanged()
    {
        $this->returnValues('disabled', '');
        $this->object->set('100');
        $this->expectWrites('enabled', '100');
        $this->assertTrue($this->object->save());
    }

    public function testSaveUnchanged()
    {
        $this->returnValues('enabled', '99');
        $this->object->set('99');

        $this->serializers[0]->expects($this->never())
            ->method('write');


        $this->serializers[1]->expects($this->never())
            ->method('write');

        $this->assertFalse($this->object->save());
    }

    public function testSaveNull()
    {
        $this->returnValues('enabled', '99');
        $this->object->delete();
        $this->expectWrites('disabled', NULL);
        $this->assertTrue($this->object->save());
    }

}

