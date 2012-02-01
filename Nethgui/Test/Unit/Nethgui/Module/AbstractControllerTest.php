<?php
namespace Nethgui\Test\Unit\Nethgui\Module;

/**
 * @covers Nethgui\Controller\AbstractController
 */
class AbstractControllerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Nethgui\Controller\AbstractController
     */
    protected $object;

    protected function setUp()
    {
        $this->object = new ConcreteStandardModule1();

        $validator = $this->getMockBuilder('Nethgui\System\Validator')
            ->disableOriginalConstructor()
            ->getMock();

        $validator->expects($this->any())
            ->method($this->anything())
            ->will($this->returnValue($validator));

        $platform = $this->getMockBuilder('Nethgui\System\PlatformInterface')
            ->disableOriginalConstructor()            
            ->getMock();

        $platform
            ->expects($this->any())
            ->method('createValidator')
            ->will($this->returnValue($validator));

        $this->object->setPlatform($platform);
    }

    public function testIsInitialized()
    {
        $this->assertFalse($this->object->isInitialized());
        $this->object->initialize();
        $this->assertTrue($this->object->isInitialized());
    }

    public function testGetIdentifier()
    {
        $this->assertNotEmpty($this->object->getIdentifier());
    }

    public function testGetSetParent()
    {
        $this->assertEquals($this->object->getParent(), NULL);

        $mockModule = $this->getMockBuilder('Nethgui\Module\Composite')
            ->disableOriginalConstructor()
            ->getMock();

        $this->object->setParent($mockModule);
        $this->assertEquals($this->object->getParent(), $mockModule);
    }

    public function testPrepareView1()
    {
        $viewMock = $this->getMockBuilder('Nethgui\View\View')
            ->disableOriginalConstructor()
            ->getMock();

        $viewMock->expects($this->once())
            ->method('copyFrom')
            ->with($this->anything());

        $this->object->prepareView($viewMock);
    }

    public function testPrepareView2()
    {
        $viewMock = $this->getMockBuilder('\Nethgui\View\View')
            ->setMethods(array('copyFrom'))
            ->disableOriginalConstructor()
            ->getMock();

        $viewMock->expects($this->once())
            ->method('copyFrom')
            ->with($this->anything());

        $this->object->prepareView($viewMock);
    }

    public function testGetLanguageCatalog()
    {
        $this->assertEquals(strtr(get_class($this->object), '\\', '_'), $this->object->getAttributesProvider()->getLanguageCatalog());
    }

}

class ConcreteStandardModule1 extends \Nethgui\Controller\AbstractController
{

    public function initialize()
    {
        parent::initialize();
        $this->declareParameter('a');
        $this->declareParameter('b');
    }

}

