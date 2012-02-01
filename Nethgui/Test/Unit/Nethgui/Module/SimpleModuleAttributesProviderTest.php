<?php
namespace Nethgui\Test\Unit\Nethgui\Module;

/**
 * @covers Nethgui\Module\SimpleModuleAttributesProvider
 */
class SimpleModuleAttributesProviderTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Nethgui\Module\SimpleModuleAttributesProvider
     */
    protected $object;

    /**
     *
     * @return \Nethgui\Module\SimpleModuleAttributesProvider
     */
    public function testInitializeFromModule()
    {
        $this->module = $this->getMock('Nethgui\Module\ModuleInterface', array('getIdentifier', 'setParent', 'getParent', 'initialize', 'isInitialized', 'getAttributesProvider'));
        $this->module->expects($this->any())
            ->method('getIdentifier')
            ->will($this->returnValue('MockModule1'));

        $object = new \Nethgui\Module\SimpleModuleAttributesProvider();

        $this->assertInstanceOf('\Nethgui\Module\ModuleAttributesInterface', $object->initializeFromModule($this->module));

        return $object;
    }

    /**
     * @depends testInitializeFromModule
     */
    public function testExtendModuleAttributes(\Nethgui\Module\SimpleModuleAttributesProvider $object)
    {
        $a = \Nethgui\Module\SimpleModuleAttributesProvider::extendModuleAttributes($object, 'Cat1', 5);
        $this->assertInstanceOf('\Nethgui\Module\ModuleAttributesInterface', $a);
    }

    /**
     * @depends testInitializeFromModule
     */
    public function testGetCategory(\Nethgui\Module\SimpleModuleAttributesProvider $object)
    {
        $this->assertNull($object->getCategory());
    }

    /**
     * @depends testInitializeFromModule
     */
    public function testGetDescription(\Nethgui\Module\SimpleModuleAttributesProvider $object)
    {
        $this->assertEquals('MockModule1_Description', $object->getDescription());
    }

    /**
     * @depends testInitializeFromModule
     */
    public function testGetLanguageCatalog(\Nethgui\Module\SimpleModuleAttributesProvider $object)
    {
        $this->assertStringStartsWith('Mock_ModuleInterface_', $object->getLanguageCatalog());
    }

    /**
     * @depends testInitializeFromModule
     */
    public function testGetMenuPosition(\Nethgui\Module\SimpleModuleAttributesProvider $object)
    {
        $this->assertNull($object->getMenuPosition());
    }

    /**
     * @depends testInitializeFromModule
     */
    public function testGetTags(\Nethgui\Module\SimpleModuleAttributesProvider $object)
    {
        $this->assertEquals('MockModule1_Tags', $object->getTags());
    }

    /**
     * @depends testInitializeFromModule
     */
    public function testGetTitle(\Nethgui\Module\SimpleModuleAttributesProvider $object)
    {
        $this->assertEquals('MockModule1_Title', $object->getTitle());
    }

}

