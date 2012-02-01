<?php
namespace Nethgui\Test\Unit\Nethgui\Module;

/**
 * @covers Nethgui\Module\Composite
 */
class CompositeTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Nethgui\Module\Composite
     */
    protected $object;

    protected function setUp()
    {
        $platform = $this->getMock('Nethgui\System\PlatformInterface');

        $this->object = new ConcreteCompositeModule1();
        $this->object->setPlatform($platform);
    }

 
    public function testAddChildInitialized()
    {
        $this->object->initialize();

        $mockModule1 = $this->getMockBuilder('\Nethgui\Controller\AbstractController')
            ->getMock();

        $mockModule1->expects($this->once())
            ->method('isInitialized')
            ->will($this->returnValue(FALSE));

        $mockModule1->expects($this->once())
            ->method('initialize');

        $mockModule1->expects($this->once())
            ->method('setParent');

        $this->object->addChild($mockModule1);
    }

}

class ConcreteCompositeModule1 extends \Nethgui\Module\Composite
{
    
}

