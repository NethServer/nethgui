<?php

namespace Nethgui\Test\Unit\Nethgui\Adapter;

use \Nethgui\Test\Tool\DB;
use \Nethgui\Test\Tool\MockFactory;

/**
 * @covers \Nethgui\Adapter\TableAdapter
 */
class TableAdapter2Test extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TableAdapter
     */
    protected $object;

    /**
     *
     * @var \Nethgui\Test\Tool\DB
     */
    protected $mockDb;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->mockDb = $this->getInitialDb();
        ;
        $this->object = new \Nethgui\Adapter\TableAdapter(MockFactory::getMockDatabase($this, $this->mockDb));
    }

    /**
     * @return \Nethgui\Test\Tool\DB
     */
    protected function getInitialDb()
    {
        $db = new \Nethgui\Test\Tool\DB;
        $type = 'T';
        $initialTable = array();
        foreach (array(1, 2, 3) as $i) {
            $initialTable[$i . 'K'] = array(
                'type' => $type . $i,
                $i . 'P' => $i . 'V',
                $i . 'Q' => $i . 'W',
            );
        }

        return $db->set(DB::getAll(NULL), $initialTable);
    }

    /**
     * @covers Nethgui\Adapter\TableAdapter::get
     * @todo   Implement testGet().
     */
    public function testGet()
    {
        $e = new \ArrayObject(array(
            '1K' => new \ArrayObject(array('type' => 'T1', '1P' => '1V', '1Q' => '1W')),
            '2K' => new \ArrayObject(array('type' => 'T2', '2P' => '2V', '2Q' => '2W')),
            '3K' => new \ArrayObject(array('type' => 'T3', '3P' => '3V', '3Q' => '3W')),
        ));

        $v = $this->object->get();

        $this->assertInstanceOf("ArrayObject", $v);
        $this->assertEquals($e, $v);
    }

    /**
     * @covers Nethgui\Adapter\TableAdapter::save
     * @todo   Implement testSave().
     */
    public function testSave1()
    {
        $f = $e = new \ArrayObject(array(
            '1K' => new \ArrayObject(array('type' => 'T1', '1P' => '1V', '1Q' => '1W')),
            '2K' => new \ArrayObject(array('type' => 'T2', '2P' => '2V', '2Q' => '2W')),
            '3K' => new \ArrayObject(array('type' => 'T3', '3P' => '3V', '3Q' => '3W')),
        ));

        $this->mockDb->set(DB::setKey('4K', 'T4', array('4P' => '4V', '4Q' => '4W')), $f);
        $this->mockDb->setFinal(TRUE);

        $this->object->offsetSet('4K', array('type' => 'T4', '4P' => '4V', '4Q' => '4W'));
        $this->assertTrue($this->object->isModified());
        $this->assertEquals(1, $this->object->save());
    }

    /**
     * @covers Nethgui\Adapter\TableAdapter::save
     * @todo   Implement testSave().
     * @expectedException \LogicException
     */
    public function testSave2()
    {
        $this->object->offsetSet('4K', array('4P' => '4V', '4Q' => '4W'));
        $this->object->save();
    }

}