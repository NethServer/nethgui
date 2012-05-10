<?php
namespace Nethgui\Test\Unit\Nethgui\Adapter;

use \Nethgui\Test\Tool\DB;
use \Nethgui\Test\Tool\MockFactory;

/**
 * @covers \Nethgui\Adapter\RecordAdapter
 */
class RecordAdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     * @var \Nethgui\Adapter\RecordAdapter
     */
    protected $object;

    protected function setUp()
    {
        $this->object = $this->getObject($this->getDB());
    }

    protected function getObject(\Nethgui\Test\Tool\DB $db)
    {
        $tableAdapter = new \Nethgui\Adapter\TableAdapter(MockFactory::getMockDatabase($this, $db), 'T', FALSE);
        return new RecordAdapterTester($tableAdapter);
    }

    /**
     * @return \Nethgui\Test\Tool\DB
     */
    protected function getDB()
    {
        $db = new \Nethgui\Test\Tool\DB;
        $type = 'T';
        $initialTable = array();
        foreach (array(1, 2) as $i) {
            $initialTable['k' . $i] = array();
            foreach (array('A', 'B', 'C') as $j) {
                $initialTable['k' . $i]['type'] = $type;
                $initialTable['k' . $i][$j] = implode(',', array('v', $i, $j));
            }
        }

        return $db->set(DB::getAll('T', FALSE), $initialTable);
    }

    public function testSetKeyValue1()
    {
        $ret = $this->object->setKeyValue('k1');
        $this->assertSame($this->object, $ret);
    }

    /**
     * @expectedException \LogicException
     */
    public function testSetKeyValue2()
    {
        $this->object->setKeyValue('k1');
        $this->object->setKeyValue('k2');
    }
    
    public function testSetKeyValueNonExisting()
    {
        $this->object->offsetSet('Q', '1');
        $this->object->setKeyValue('NA');
        $this->assertEquals(array('Q' => '1'), $this->object->get());
    }    

    public function testGetKeyValue()
    {
        $this->assertNull($this->object->getKeyValue());
        $this->object->setKeyValue('k2');
        $this->assertEquals('k2', $this->object->getKeyValue());
    }

    public function testDelete1()
    {
        $this->object->setKeyValue('k1');
        $this->object->delete();
        $this->assertTrue($this->object->isModified());
        $this->object->save();
        $this->assertFalse($this->object->isModified());
    }

    /**
     * Failure when saving a deleted record without a key
     * @expectedException \LogicException
     */
    public function testDelete2()
    {
        $this->object->delete();
        $this->object->save();
    }

    public function testGetk1()
    {
        $expectedRecord = array(
            'A' => 'v,1,A',
            'B' => 'v,1,B',
            'C' => 'v,1,C',
        );
        $this->object->setKeyValue('k1');
        $this->assertEquals($expectedRecord, $this->object->get());
    }

    public function testGetNull()
    {
        $this->assertEquals(array(), $this->object->get());
    }

    public function testIsModified()
    {
        $this->assertFalse($this->object->isModified(), 'clean');
        $this->object->offsetSet('p1', 'I');
        $this->assertTrue($this->object->isModified(), 'dirty');
        $this->object->setKeyValue('k2');
        $this->object->save();
        $this->assertFalse($this->object->isModified(), 'saved1');
        $this->object->delete();
        $this->assertTrue($this->object->isModified(), 'deleted');
        $this->object->save();
        $this->assertFalse($this->object->isModified(), 'saved2');
    }

    public function testSaveDeleted1()
    {
        $object = $this->object;

        $object->setKeyValue('k2');
        $object->offsetSet('p1', 'v1');
        $object->delete();
        $object->save();
    }

    /**
     * @expectedException \LogicException
     */
    public function testSaveDeleted2()
    {
        $this->object->delete();
        $this->object->save();
    }

    public function testSaveModified()
    {
        $this->object->setKeyValue('k1');
        $this->object->offsetSet('A', 'XXX');
        $this->object->save();
        $this->assertFalse($this->object->isModified());

        $row = $this->object->getTableAdapter()->offsetGet('k1');

        $this->assertEquals('XXX', $row['A']);
    }

    public function testSaveCreated()
    {
        $this->object->save();
        $this->assertFalse($this->object->isModified());
    }

    public function testSet1()
    {
        $expectedRecord = array(
            'A' => 'v,1,A',
            'B' => 'v,1,B',
            'C' => 'v,1,C',
        );
        $this->object->set($expectedRecord);
    }

    public function testSetEmpty()
    {
        $this->object->set(array());
        $this->assertEquals(array(), $this->object->get());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetInvalidArgumentException1()
    {
        $this->object->set('a');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetInvalidArgumentException2()
    {
        $this->object->set(NULL);
    }


    public function testOffsetExists1()
    {
        $this->assertFalse($this->object->offsetExists('A'));
        $this->object->setKeyValue('k1');
        $this->assertTrue($this->object->offsetExists('A'));
    }

    public function testOffsetExists2()
    {
        $this->object->setKeyValue('k1');
        $this->object->delete();
        $this->assertTrue($this->object->offsetExists('A'));
        $this->object->save();
        $this->assertFalse($this->object->offsetExists('A'));
    }

    public function testOffsetGet1()
    {
        $this->object->setKeyValue('k1');
        $this->assertEquals('v,1,A', $this->object->offsetGet('A'));
    }

    /**
     * @expectedException \PHPUnit_Framework_Error
     */
    public function testOffsetGet2()
    {
        $this->assertNull($this->object->offsetGet('A'));
    }

    public function testOffsetSet1()
    {
        $this->object->offsetSet('A', 'Set1');
        $this->assertTrue($this->object->isModified());
        $this->assertEquals('Set1', $this->object->offsetGet('A'));
    }

    public function testOffsetSet2()
    {
        $this->object->offsetSet('A', 'Set1');
        $this->object->setKeyValue('k1');
        $this->object->save();
        $this->assertEquals('Set1', $this->object->offsetGet('A'));
    }

    public function testOffsetSet3()
    {
        $this->object->offsetSet('A', 'I');
        $this->object->offsetSet('B', 'II');

        $this->assertEquals('I', $this->object->offsetGet('A'));
        $this->assertEquals('II', $this->object->offsetGet('B'));
    }

    /**
     * Unsetting a field before initialization fails
     * @expectedException \PHPUnit_Framework_Error
     */
    public function testOffsetUnset1()
    {
        $this->object->offsetUnset('A');
    }

    public function testOffsetUnset2()
    {
        $this->object->setKeyValue('k1');
        $this->object->offsetUnset('A');
        $this->assertEquals(array('B' => 'v,1,B', 'C' => 'v,1,C'), $this->object->get());
    }

    public function testGetIterator()
    {
        $this->object->setKeyValue('k1');
        $it = $this->object->getIterator();

        $expectedRecord = array(
            'A' => 'v,1,A',
            'B' => 'v,1,B',
            'C' => 'v,1,C',
        );

        foreach ($it as $key => $value) {
            $this->assertTrue(isset($expectedRecord[$key]));
            $this->assertEquals($expectedRecord[$key], $value);
        }
    }

}

/**
 * Give public access to the internal tableAdapter: 
 */
class RecordAdapterTester extends \Nethgui\Adapter\RecordAdapter
{

    public function getTableAdapter()
    {
        return $this->tableAdapter;
    }

}