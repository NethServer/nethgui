<?php
/**
 * @package Tests
 * @subpackage Unit
 */

/**
 * @package Tests
 * @subpackage Unit
 */
class NethGui_Core_Module_CompositeTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var NethGui_Core_Module_Composite
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new ConcreteCompositeModule1();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {

    }

    public function testAddChildInitialized()
    {
        $this->object->initialize();

        $mockModule1 = $this->getMockForAbstractClass("NethGui_Core_Module_Standard", array(), 'Module_M1');

        $mockModule1->expects($this->once())
            ->method('isInitialized')
            ->will($this->returnValue(FALSE));

        $mockModule1->expects($this->once())
            ->method('initialize');

        $mockModule1->expects($this->once())
            ->method('setParent');

        $this->object->addChild($mockModule1);



//        $mockModule2 = $this->getMockForAbstractClass("NethGui_Core_Module_Standard", array(), 'Module_M2');
//
//        $mockModule2->expects($this->never())
//            ->method('initialize');
//
//        $mockModule2->expects($this->once())
//            ->method('isInitialized')
//            ->will($this->returnValue(TRUE));
//
//        $mockModule2->expects($this->once())
//            ->method('setParent');
//
//        $this->object->addChild($mockModule2);

        
    }

    public function testInitialize()
    {
        
    }

    /**
     * @todo Implement testGetChildren().
     */
    public function testGetChildren()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testBind().
     */
    public function testBind()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testValidate().
     */
    public function testValidate()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testProcess().
     */
    public function testProcess()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testPrepareView().
     */
    public function testPrepareView()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testSetHostConfiguration().
     */
    public function testSetHostConfiguration()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

}

class ConcreteCompositeModule1 extends NethGui_Core_Module_Composite {}

?>
