<?php
namespace Nethgui\Test\Unit\Nethgui\Core;

/**
 * @covers \Nethgui\Module\Notification\DialogBox
 */
class DialogBoxTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Nethgui\Module\Notification\DialogBox
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $userMock = $this->getMockBuilder('Nethgui\Controller\Standard')
            ->disableOriginalConstructor()
            ->getMock();
        $this->object = new \Nethgui\Module\Notification\DialogBox($userMock, 'message');
    }
    
    public function testGetActions()
    {
        $this->markTestIncomplete();
    }

    
    public function testGetMessage()
    {
        $this->assertEquals(array('message', array()), $this->object->getMessage());
    }

    public function testGetType()
    {
        $this->assertEquals('Message', $this->object->getType());
    }

    public function testIsTransient()
    {
        $this->assertTrue($this->object->isTransient());
    }

    public function testGetId()
    {
        $this->assertRegExp('/^[a-zA-Z0-9]+$/', $this->object->getIdentifier());
    }

    public function testSerialize()
    {
        $ser = unserialize(serialize($this->object));        
        $this->assertEquals($this->object, $ser);        
    }

    public function testUnserialize()
    {
        $ser = unserialize(serialize($this->object));        
        $this->assertEquals($ser, $this->object);         
    }

}

