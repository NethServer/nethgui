<?php
/**
 * @package Tests
 * @subpackage Unit
 */

/**
 * @package Tests
 * @subpackage Unit
 */
class Nethgui\Core\Module\StandardTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var Nethgui\Core\Module\Standard
     */
    protected $object;

    protected function setUp()
    {
        $this->object = new Test\Unit\ConcreteStandardModule1();

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

        $mockModule = $this->getMockBuilder('Nethgui\Core\Module\Composite')
            ->disableOriginalConstructor()
            ->getMock();

        $this->object->setParent($mockModule);
        $this->assertEquals($this->object->getParent(), $mockModule);
    }

    public function testPrepareView1()
    {
        $viewMock = $this->getMockBuilder('Nethgui\Client\View')
            ->disableOriginalConstructor()
            ->getMock();

        $viewMock->expects($this->once())
            ->method('copyFrom')
            ->with($this->anything());

        $this->object->prepareView($viewMock, Nethgui\Core\Module\Standard::VIEW_CLIENT);
    }

    public function testPrepareView2()
    {
        $viewMock = $this->getMockBuilder('Nethgui\Client\View')
            ->setMethods(array('copyFrom'))
            ->disableOriginalConstructor()
            ->getMock();

        $viewMock->expects($this->once())
            ->method('copyFrom')
            ->with($this->anything());

        $this->object->prepareView($viewMock, Nethgui\Core\Module\Standard::VIEW_SERVER);
    }

    public function testGetLanguageCatalog()
    {
        $this->assertEquals(get_class($this->object), $this->object->getLanguageCatalog());
    }

}

class Test\Unit\ConcreteStandardModule1 extends Nethgui\Core\Module\Standard
{

    public function initialize()
    {
        parent::initialize();
        $this->declareParameter('a');
        $this->declareParameter('b');
    }

}

?>
