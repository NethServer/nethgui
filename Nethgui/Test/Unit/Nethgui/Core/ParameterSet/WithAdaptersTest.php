<?php
namespace Test\Unit\Nethgui\Adapter\ParameterSet;

/**
 * @covers \Nethgui\Adapter\ParameterSet
 */
class WithAdaptersTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Nethgui\Adapter\ParameterSet
     */
    protected $object;

    protected function setUp()
    {
        $this->object = new \Nethgui\Adapter\ParameterSet;

        $this->arrayAdapter = $this->getMockBuilder('\Nethgui\Adapter\ArrayAdapter')
            ->disableOriginalConstructor()
            ->getMock();

        $this->scalarAdapter = $this->getMockBuilder('\Nethgui\Adapter\ScalarAdapter')
            ->disableOriginalConstructor()
            ->getMock();

        $this->object->addAdapter($this->arrayAdapter, 'arrayAdapter');
        $this->object->addAdapter($this->scalarAdapter, 'scalarAdapter');
        $this->object['inner'] = $this->getMock('\Nethgui\Adapter\ParameterSet');
        $this->object['pi'] = 3.14;
    }

    public function testCount()
    {
        $this->assertEquals(4, $this->object->count());
    }

    public function testUserScalarAdapter()
    {
        $this->scalarAdapter->expects($this->once())
            ->method('set')
            ->with('VALUE');

        $this->object['scalarAdapter'] = 'VALUE';
    }

    public function testUseArrayAdapter()
    {
        $this->arrayAdapter->expects($this->once())
            ->method('offsetSet')
            ->with(1, 'NEW');

        $this->arrayAdapter->expects($this->exactly(2))
            ->method('get')
            ->will($this->returnValue($this->arrayAdapter));

        $this->arrayAdapter->expects($this->once())
            ->method('offsetGet')
            ->with(1)
            ->will($this->returnValue('VALUE'));


        $this->assertEquals('VALUE', $this->object['arrayAdapter'][1]);

        $this->object['arrayAdapter'][1] = 'NEW';
    }

    public function testSaveClean()
    {
        $this->arrayAdapter->expects($this->once())
            ->method('save');

        $this->scalarAdapter->expects($this->once())
            ->method('save');

        $this->object['inner']->expects($this->once())
            ->method('save');

        $this->assertEquals(FALSE, $this->object->save());
    }

    public function testSaveDirty()
    {
        $this->arrayAdapter->expects($this->once())
            ->method('save')
            ->will($this->returnValue(1))
        ;

        $this->scalarAdapter->expects($this->once())
            ->method('save')
            ->will($this->returnValue(1))
        ;

        $this->object['inner']->expects($this->once())
            ->method('save')
            ->will($this->returnValue(3))
        ;

        $this->assertEquals(TRUE, $this->object->save());
    }

}

