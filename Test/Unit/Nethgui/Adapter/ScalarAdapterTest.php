<?php
namespace Test\Unit\Nethgui\Adapter;

/**
 * @covers \Nethgui\Adapter\ScalarAdapter
 */
class ScalarAdapterTest extends \PHPUnit_Framework_TestCase
{

    /**
     *
     * @var \Nethgui\Serializer\SerializerInterface
     */
    private $serializer;
    /**
     *
     * @var \Nethgui\Adapter\ScalarAdapter
     */
    private $fixture;

    protected function setUp()
    {
        $this->serializer = $this->getMockBuilder('\Nethgui\Serializer\KeySerializer')
                ->disableOriginalConstructor()
                ->getMock();

        $this->serializer->expects($this->any())
            ->method('read')
            ->withAnyParameters()
            ->will($this->returnValue('ORIGINAL'));

        $this->fixture = new \Nethgui\Adapter\ScalarAdapter($this->serializer);
    }

    public function testGet()
    {
        $this->serializer->expects($this->once())
            ->method('read')
            ->withAnyParameters()
            ->will($this->returnValue('ORIGINAL'));

        $this->assertEquals('ORIGINAL', $this->fixture->get());
        $this->assertFalse($this->fixture->isModified());

        return $this->fixture;
    }

    public function testSet()
    {
        $this->fixture->set('MODIFIED');
        $this->assertEquals('MODIFIED', $this->fixture->get());
        $this->assertTrue($this->fixture->isModified());

        return $this->fixture;
    }

    /**
     *
     * @depends testSet
     * @param \Nethgui\Adapter\ScalarAdapter $changedFixture
     */
    public function testSaveModified($changedFixture)
    {
        $this->serializer->expects($this->never())
            ->method('write')
            ->withAnyParameters();

        $this->assertEquals(1, $changedFixture->save());
    }

    /**
     * @depends testGet
     * @param \Nethgui\Adapter\ScalarAdapter $unchangedFixture
     */
    public function testSaveNotModified($unchangedFixture)
    {
        $this->serializer->expects($this->never())
            ->method('write')
            ->withAnyParameters();

        $this->assertEquals(0,$unchangedFixture->save());
    }

    public function testSaveUninitialized()
    {
        $this->serializer->expects($this->never())
            ->method('write')
            ->withAnyParameters();

        $this->assertEquals(0,$this->fixture->save());
    }

    public function testDelete()
    {
        $this->assertFalse($this->fixture->isModified());
        $this->fixture->delete();
        $this->assertTrue($this->fixture->isModified());
        $this->assertNull($this->fixture->get());
    }

}

