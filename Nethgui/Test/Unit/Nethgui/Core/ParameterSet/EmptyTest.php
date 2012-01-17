<?php
namespace Nethgui\Test\Unit\Nethgui\Adapter\ParameterSet;

/**
 * @covers \Nethgui\Adapter\ParameterSet
 */
class EmptyTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Nethgui\Adapter\ParameterSet
     */
    protected $object;

    protected function setUp()
    {
        $this->object = new \Nethgui\Adapter\ParameterSet;
    }

    public function testCount()
    {
        $this->assertEquals(0, $this->object->count());
    }

    public function offsetProvider()
    {
        return array(
            array('a'),
            array('b'),
            array('c'),
            array('d'),
        );
    }

    /**
     * @dataProvider offsetProvider
     */
    public function testOffsetExists($offset)
    {
        $this->assertFalse($this->object->offsetExists($offset));
    }

    /**
     * @dataProvider offsetProvider
     * @expectedException PHPUnit_Framework_Error_Notice
     */
    public function testOffsetGet($offset)
    {
        $this->object->offsetGet($offset);
    }

    public function valuesProvider()
    {
        return array(
            array('a', '10'),
            array('b', '20'),
            array('c', '30'),
            array('d', '40'),
        );
    }

    /**
     * @dataProvider valuesProvider
     */
    public function testOffsetSet($offset, $value)
    {
        $this->object->offsetSet($offset, $value);
        $this->assertEquals($this->object->offsetGet($offset), $value);
    }

    /**
     * @dataProvider offsetProvider
     */
    public function testOffsetUnset($offset)
    {
        $this->object[$offset] = 'VALUE';
        unset($this->object[$offset]);
        $this->assertFalse(isset($this->object[$offset]));
    }

    public function testSave()
    {
        $this->assertEquals(FALSE, $this->object->save());
    }

}

