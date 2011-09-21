<?php
/**
 * @package Tests
 * 
 */

/**
 * @package Tests
 * 
 */
class Nethgui_Core_DialogBoxTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var Nethgui_Core_DialogBox
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $userMock = $this->getMockBuilder('Nethgui_Core_Module_Standard')
            ->disableOriginalConstructor()
            ->getMock();
        $this->object = new Nethgui_Core_DialogBox($userMock, 'message');
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        
    }

    
    public function testGetActions()
    {
        $this->markTestIncomplete();
        //$this->assertEmpty($this->object->getActionViews($notificationModule));
    }

    
    public function testGetMessage()
    {
        $this->assertEquals(array('message', array()), $this->object->getMessage());
    }

    /**
     * @todo Implement testGetType().
     */
    public function testGetType()
    {
        $this->assertEquals(0, $this->object->getType());
    }

    /**
     * @todo Implement testIsTransient().
     */
    public function testIsTransient()
    {
        $this->assertTrue($this->object->isTransient());
    }

    /**
     * @todo Implement testGetId().
     */
    public function testGetId()
    {
        $this->assertRegExp('/^[a-zA-Z0-9]+$/', $this->object->getId());
    }

    /**
     * @todo Implement testSerialize().
     */
    public function testSerialize()
    {
        $ser = unserialize(serialize($this->object));        
        $this->assertEquals($this->object, $ser);        
    }

    /**
     * @todo Implement testUnserialize().
     */
    public function testUnserialize()
    {
        $ser = unserialize(serialize($this->object));        
        $this->assertEquals($ser, $this->object);         
    }

}

?>
