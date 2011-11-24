<?php
namespace Test\Unit\Nethgui\Adapter;
class ArrayAdapterTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Nethgui\Adapter\ArrayAdapter
     */
    protected $fixture;
    /**
     *
     * @var \Nethgui\Serializer\SerializerInterface
     */
    private $serializer;

    protected function setUp()
    {
        $this->serializer = $this->getMockBuilder('\Nethgui\Serializer\KeySerializer')
                ->disableOriginalConstructor()
                ->getMock();

        $this->fixture = new \Nethgui\Adapter\ArrayAdapter(',', $this->serializer);
    }

    public function testGetCsv()
    {
        $this->serializer->expects($this->once())
            ->method('read')
            ->will($this->returnValue('ONE,TWO,THREE'));

        $this->assertEquals('ONE', $this->fixture->get()->offsetGet(0));
        $this->assertEquals('TWO', $this->fixture->get()->offsetGet(1));
        $this->assertEquals('THREE', $this->fixture->get()->offsetGet(2));
        $this->assertFalse($this->fixture->isModified());

        return $this->fixture;
    }

    /**
     *
     */
    public function testGetNull()
    {
        $this->serializer->expects($this->once())
            ->method('read')
            ->will($this->returnValue(NULL));

        $this->assertNull($this->fixture->get());
    }

    public function testGetEmptyString()
    {
        $this->serializer->expects($this->once())
            ->method('read')
            ->will($this->returnValue(''));

        $this->assertEquals(0, $this->fixture->count());
    }

    public function testSetArray()
    {
        $this->fixture->set(array('A', 'B', 'C'));
        $this->assertEquals('A', $this->fixture[0]);
        $this->assertEquals('B', $this->fixture[1]);
        $this->assertEquals('C', $this->fixture[2]);
        $this->assertTrue($this->fixture->isModified());
    }

    public function testSetNull()
    {
        $this->fixture->set(NULL);
        $this->assertEmpty($this->fixture->get());
        $this->fixture[0] = 'MODIFIED';
        $this->assertEquals('MODIFIED', $this->fixture[0]);
    }

    /**
     * @expectedException \Nethgui\Exception\Adapter
     */
    public function testSetFail()
    {
        $this->fixture->set('FAIL');
    }

    public function testDelete()
    {
        $this->serializer->expects($this->once())
            ->method('read')
            ->will($this->returnValue('A,B,C'));

        $this->fixture->delete();
        $this->assertTrue($this->fixture->isModified());
        $this->assertNull($this->fixture[0]);
    }

    public function testIsModified()
    {
        $this->serializer->expects($this->once())
            ->method('read');

        $this->assertFalse($this->fixture->isModified());
        $this->fixture[0] = 'UNO';
        $this->assertTrue($this->fixture->isModified());
    }

    public function testSaveModified()
    {
        $this->serializer->expects($this->exactly(1))
            ->method('read')
            ->will($this->returnValue('ONE,TWO,THREE'));

        $this->serializer->expects($this->once())
            ->method('write')
            ->with('UNO,DUE,THREE');

        $this->fixture[0] = 'UNO';
        $this->fixture[1] = 'DUE';
        $this->assertEquals(1, $this->fixture->save());
        $this->assertEquals('THREE', $this->fixture[2]);
    }

    public function testSaveDeleted()
    {
        $this->serializer->expects($this->once())
            ->method('read')
            ->will($this->returnValue('ONE,TWO,THREE'));

        $this->fixture->delete();

        $this->serializer->expects($this->once())
            ->method('write')
            ->with(NULL);

        $this->assertEquals(1, $this->fixture->save());
    }

    public function testSaveNotModified()
    {
        $this->serializer->expects($this->once())
            ->method('read')
            ->will($this->returnValue('ONE,TWO,THREE'));

        $this->serializer->expects($this->never())
            ->method('write');

        $this->assertEquals('ONE', $this->fixture[0]);

        $this->assertEquals(0, $this->fixture->save());
    }

    public function testSaveUninitialized()
    {
        $this->serializer->expects($this->never())
            ->method('read');

        $this->serializer->expects($this->never())
            ->method('write');

        $this->assertEquals(0, $this->fixture->save());
    }

    public function testCountFull()
    {
        $this->serializer->expects($this->once())
            ->method('read')
            ->will($this->returnValue('ONE,TWO,THREE'));

        $this->assertEquals(3, count($this->fixture));
        $this->assertFalse($this->fixture->isModified());
        $this->fixture[] = 'FOUR';
        $this->assertEquals(4, count($this->fixture));
        $this->assertTrue($this->fixture->isModified());
    }

    public function testCountNull()
    {
        $this->serializer->expects($this->once())
            ->method('read')
            ->will($this->returnValue(NULL));

        $this->assertEquals(0, count($this->fixture));
    }

    public function testGetIteratorFull()
    {
        $this->serializer->expects($this->once())
            ->method('read')
            ->will($this->returnValue('ONE,TWO,THREE'));

        $a = array('ONE', 'TWO', 'THREE');

        foreach ($this->fixture as $key => $value) {
            $this->assertEquals($a[$key], $value);
        }
    }

    public function testGetIteratorEmpty()
    {
        $this->serializer->expects($this->once())
            ->method('read')
            ->will($this->returnValue(''));

        $i = 0;

        foreach ($this->fixture as $value) {
            $i ++;
        }

        $this->assertEquals(0, $i, 'OK: no loops.');
    }

    public function testGetIteratorNull()
    {
        $this->serializer->expects($this->once())
            ->method('read')
            ->will($this->returnValue(NULL));

        $i = 0;

        foreach ($this->fixture as $value) {
            $i ++;
        }

        $this->assertEquals(0, $i, 'OK: no loops.');
    }

    public function testOffsetExistsTrue()
    {
        $this->serializer->expects($this->once())
            ->method('read')
            ->will($this->returnValue('ONE,TWO,THREE'));

        $this->assertTrue(isset($this->fixture[0]));
    }

    public function testOffsetExistsFalse()
    {
        $this->serializer->expects($this->once())
            ->method('read')
            ->will($this->returnValue('ONE,TWO,THREE'));

        $this->assertFalse(isset($this->fixture[3]));
    }

    public function testOffsetGet()
    {
        $this->serializer->expects($this->once())
            ->method('read')
            ->withAnyParameters()
            ->will($this->returnValue('ONE,TWO,THREE'));

        $this->assertEquals('TWO', $this->fixture[1]);
    }

    public function testOffsetSet()
    {
        $this->serializer->expects($this->once())
            ->method('read')
            ->will($this->returnValue('ONE,TWO,THREE'));

        for ($i = 0; $i < count($this->fixture); $i ++ ) {
            $this->fixture[$i] = 'MODIFIED' . $i;
            $this->assertEquals('MODIFIED' . $i, $this->fixture[$i]);
        }

        $this->assertTrue($this->fixture->isModified());
    }

    /**
     * Test count 0
     */
    public function testOffsetUnsetAll()
    {
        $this->serializer->expects($this->once())
            ->method('read')
            ->will($this->returnValue('ONE,TWO,THREE'));

        $n = count($this->fixture);
        for ($i = 0; $i < $n; $i ++ ) {
            unset($this->fixture[$i]);
        }
        $this->assertEquals(0, count($this->fixture));
        $this->assertTrue($this->fixture->isModified());
    }

    /**
     * Test read
     */
    public function testOffsetUnsetOnce()
    {
        $this->serializer->expects($this->once())
            ->method('read')
            ->will($this->returnValue('ONE,TWO,THREE'));

        unset($this->fixture[0]);
        $this->assertEquals(2, count($this->fixture));
        $this->assertTrue($this->fixture->isModified());
    }

}

