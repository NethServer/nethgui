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
        foreach (array(1, 2, 3, 4) as $i) {
            $initialTable['k' . $i] = array();
            foreach (array('A', 'B', 'C', 'D', 'E') as $j) {
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

    public function testGetKeyValue()
    {
        $this->assertNull($this->object->getKeyValue());
        $this->object->setKeyValue('k2');
        $this->assertEquals('k2', $this->object->getKeyValue());
    }

    public function testOffsetSet2()
    {
        $this->object->offsetSet('A', 'I');
        $this->object->offsetSet('B', 'II');

        $this->assertEquals('I', $this->object->offsetGet('A'));
        $this->assertEquals('II', $this->object->offsetGet('B'));
    }

    /**
     * @todo Implement testDelete().
     */
    public function testDelete()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testGet().
     */
    public function testGet()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testIsModified().
     */
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

    public function testSaveClean()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testSet().
     */
    public function testSet()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testOffsetExists().
     */
    public function testOffsetExists()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testOffsetGet().
     */
    public function testOffsetGet()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testOffsetSet().
     */
    public function testOffsetSet1()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testOffsetUnset().
     */
    public function testOffsetUnset()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testGetIterator().
     */
    public function testGetIterator()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

}


class RecordAdapterTester extends \Nethgui\Adapter\RecordAdapter
{
    public function getTableAdapter()
    {
        return $this->tableAdapter;
    }
}