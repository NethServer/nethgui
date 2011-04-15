<?php
/**
 * @package Tests
 * @subpackage Unit
 */

/**
 * @package Tests
 * @subpackage Unit
 */
class NethGui_Core_Module_StandardTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var NethGui_Core_Module_Standard
     */
    protected $object;

    protected function setUp()
    {
        $this->object = new ConcreteStandardModule1();
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

        $mockModule = $this->getMockBuilder('NethGui_Core_Module_Composite')
                ->disableOriginalConstructor()
                ->getMock();

        $this->object->setParent($mockModule);
        $this->assertEquals($this->object->getParent(), $mockModule);
    }

    public function testPrepareView1()
    {
        $viewMock = $this->getMockBuilder('NethGui_Core_View')
                ->disableOriginalConstructor()
                ->getMock();

        $viewMock->expects($this->once())
            ->method('copyFrom')
            ->with($this->anything());
        -
            $this->object->prepareView($viewMock, NethGui_Core_Module_Standard::VIEW_UPDATE);
    }

    public function testPrepareView2()
    {
        $viewMock = $this->getMockBuilder('NethGui_Core_View')
                ->disableOriginalConstructor()
                ->getMock();

        $viewMock->expects($this->exactly(2))
            ->method('copyFrom')
            ->with($this->anything());
        -
            $this->object->prepareView($viewMock, NethGui_Core_Module_Standard::VIEW_REFRESH);
    }

    public function testGetLanguageCatalog()
    {
        $this->assertEquals(get_class($this->object), $this->object->getLanguageCatalog());
    }

}

class ConcreteStandardModule1 extends NethGui_Core_Module_Standard {
    public function initialize()
    {
        parent::initialize();
        $this->declareParameter('a');
        $this->declareParameter('b');
    }
}

?>
