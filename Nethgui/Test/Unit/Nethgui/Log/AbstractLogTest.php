<?php
namespace Nethgui\Test\Unit\Nethgui\Log;

/**
 * @covers \Nethgui\Log\AbstractLog
 */
class AbstractLogTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Nethgui\Log\AbstractLog
     */
    protected $object;

    protected function setUp()
    {
        $this->object = $this->getMockBuilder('Nethgui\Log\AbstractLog')
            ->setMethods(array('message'))
            ->getMockForAbstractClass();
    }

    public function testGetLevel()
    {
        $this->assertEquals(E_ALL, $this->object->getLevel());
    }

    public function testSetLevel()
    {
        $this->assertSame($this->object, $this->object->setLevel(E_ERROR | E_WARNING));
    }

    public function testException()
    {
        $this->object->setLevel(0);
        $this->assertSame($this->object, $this->object->exception(new \Exception('Ex', 1234)));
    }

    public function testNotice()
    {
        $this->object->setLevel(0);
        $this->assertSame($this->object, $this->object->notice('N'));
    }

    public function testError()
    {
        $this->object->setLevel(0);
        $this->assertSame($this->object, $this->object->error('E'));
    }

    public function testWarning()
    {
        $this->object->setLevel(0);
        $this->assertSame($this->object, $this->object->warning('W'));
    }

    public function testSetPhpWrapper()
    {
        $this->assertSame($this->object, $this->object->setPhpWrapper($this->getMock('Nethgui\Utility\PhpWrapper')));
    }

}

