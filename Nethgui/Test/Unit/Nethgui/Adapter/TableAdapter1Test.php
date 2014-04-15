<?php
namespace Nethgui\Test\Unit\Nethgui\Adapter;

use \Nethgui\Test\Tool\DB;
use \Nethgui\Test\Tool\MockFactory;

/**
 * @covers \Nethgui\Adapter\TableAdapter
 */
class TableAdapter1Test extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TableAdapter
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new \Nethgui\Adapter\TableAdapter(MockFactory::getMockDatabase($this, $this->getDB()), 'T');
    }

    /**
     * @return \Nethgui\Test\Tool\DB
     */
    protected function getDB()
    {
        $db = new \Nethgui\Test\Tool\DB;
        $type = 'T';
        $initialTable = array();
        foreach (array(1, 2, 3) as $i) {
            $initialTable[$i . 'K'] = array(
                'type' => $type,
                $i . 'P' => $i . 'V',
                $i . 'Q' => $i . 'W',
            );
        }

        return $db->set(DB::getAll('T'), $initialTable);
    }

    public function testCount()
    {
        $this->assertEquals(3, $this->object->count());
    }

    public function testDelete()
    {
        $this->object->delete();
        $this->assertEquals(0, $this->object->count());
    }

    public function testGet()
    {
        $e = new \ArrayObject(array(
            '1K' => new \ArrayObject(array('1P' => '1V', '1Q' => '1W')),
            '2K' => new \ArrayObject(array('2P' => '2V', '2Q' => '2W')),
            '3K' => new \ArrayObject(array('3P' => '3V', '3Q' => '3W')),
        ));

        $v = $this->object->get();

        $this->assertInstanceOf("ArrayObject", $v);
        $this->assertEquals($e, $v);
    }

    public function testSet()
    {
        $e = array(
            '1K' => array('1P' => '1V', '1Q' => '1W'),
            '2K' => array('2P' => '2V', '2Q' => '2W'),
            '3K' => array('3P' => '3V', '3Q' => '3W'),
        );

        $o = new \ArrayObject();

        foreach ($e as $k => $r) {
            $o[$k] = new \ArrayObject($r);
        }

        $this->object->set($e);

        $this->assertEquals($o, $this->object->get());
    }

    public function testGetIterator()
    {
        $it = $this->object->getIterator();
        $this->assertEquals(3, $it->count());
    }

    public function testIsModified1()
    {
        $this->assertFalse($this->object->isModified());
        $this->object[2] = array('0', '0', '0');
        $this->assertTrue($this->object->isModified());
    }

    public function testOffsetExists()
    {
        $this->assertTrue($this->object->offsetExists('1K'));
        $this->assertFalse($this->object->offsetExists(-1));
    }

    public function testOffsetGet()
    {
        $this->assertInstanceOf("ArrayObject", $this->object->offsetGet('1K'));
    }

    public function testOffsetSet()
    {
        $this->object->offsetSet('1A', array('0', '0', '0'));
        $this->assertEquals(new \ArrayObject(array('0', '0', '0')), $this->object['1A']);
    }

    public function testOffsetUnset()
    {
        $this->object->offsetUnset('1K');
        $this->assertEquals(2, $this->object->count());
        $this->assertFalse($this->object->offsetExists('1A'));
    }

    public function testGet2()
    {
        $value = $this->object->get();
        $this->assertInstanceOf('ArrayAccess', $value);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetError1()
    {
        $this->object->set('hi');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetError2()
    {
        $this->object->set(array(array(1, 2, 3), 'hi'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testOffsetSetError1()
    {
        $this->object->offsetSet('4', 'hi');
    }

    public function testSave0()
    {
        $this->assertFalse($this->object->isModified());
        $c = $this->object->save();
        $this->assertEquals(0, $c);
        $this->assertFalse($this->object->isModified());
    }

    public function testSave1()
    {
        $e = new \ArrayObject(array(
            '2K' => new \ArrayObject(array('2P' => '2V', '2Q' => '2W')),
            '3K' => new \ArrayObject(array('3P' => '3V', '3Q' => '3W')),
        ));


        $this->object = new \Nethgui\Adapter\TableAdapter(MockFactory::getMockDatabase($this, $this->getDB()->set(DB::deleteKey("1K"), $e)), 'T', FALSE);

        $this->assertFalse($this->object->isModified());
        unset($this->object['1K']);
        $this->assertTrue($this->object->isModified());
        $c = $this->object->save();
        $this->assertEquals(1, $c);
        $this->assertFalse($this->object->isModified());
        $this->assertFalse(isset($this->object['1K']));
    }

}
