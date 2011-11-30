<?php
namespace Test\Unit\Nethgui\Core;

/**
 * @covers \Nethgui\Core\ModuleLoader
 * @covers \Nethgui\Core\ModuleSetInterface
 */
class ModuleLoaderTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Nethgui\Core\ModuleLoader
     */
    protected $object;

    protected function setUp()
    {
        spl_autoload_register(array($this, 'autoloader'));


        $namespaceMap = array(
            'Vendor00' => '/usr/local/share',
            'Vendor1B' => '/home/vendor1',
            'Vendor1A' => '/home/vendor1',
        );

        $gfw = $this->getMockBuilder('Nethgui\Core\GlobalFunctionWrapper')
            ->disableOriginalConstructor()
            ->setMethods(array('scandir'))
            ->getMock();

        $callback = function($fullPath) {
                $directoryContents = array(
                    '/usr/local/share/Vendor00/Module' => array('TestModule1.php', 'README', 'NIL'),
                    '/home/vendor1/Vendor1B/Module' => array('TestModule1.php', 'TestModule2.php'),
                    '/home/vendor1/Vendor1A/Module' => array('TestModule3.php', 'TestModule4.php'),
                );

                return $directoryContents[$fullPath];
            };

        $gfw->expects($this->any())->method('scandir')->will($this->returnCallback($callback));

        $this->object = new \Nethgui\Core\ModuleLoader($namespaceMap);
        $this->object->setGlobalFunctionWrapper($gfw);
    }

    public function autoloader($className)
    {
        $parts = explode('\\', $className);

        $rootNs = $parts[0];
        $unqClass = $parts[2];

        if (substr($rootNs, 0, 6) == 'Vendor' && substr($unqClass, 0, 10) == 'TestModule') {
            eval("namespace ${rootNs}\\Module;\n class ${unqClass} extends \Nethgui\Core\Module\Standard {}");
        }
    }

    public function testGetIterator1()
    {
        $moduleIdentifier = NULL;
        foreach ($this->object as $moduleIdentifier => $moduleInstance) {
            $this->assertInstanceOf('Nethgui\Core\ModuleInterface', $moduleInstance);
            $this->assertEquals($moduleIdentifier, $moduleInstance->getIdentifier());
        }
        $this->assertEquals('TestModule4', $moduleIdentifier);
    }

    public function testGetIterator2()
    {
        $moduleMap = iterator_to_array($this->object, TRUE);
        $this->assertEquals(array('TestModule1', 'TestModule2', 'TestModule3', 'TestModule4'), array_keys($moduleMap));
    }

    public function testGetIterator3()
    {
        $gfw = $this->getMockBuilder('Nethgui\Core\GlobalFunctionWrapper')
            ->disableOriginalConstructor()
            ->setMethods(array('scandir'))
            ->getMock();

        $gfw->expects($this->any())->method('scandir')->will($this->returnValue(FALSE));

        $object = new \Nethgui\Core\ModuleLoader(array('NonExists' => '/non-exists'));
        $object->setGlobalFunctionWrapper($gfw);

        try {
            $object->getIterator();
        } catch (\Exception $ex) {
            $this->assertInstanceOf('UnexpectedValueException', $ex);
            $this->assertEquals(1322649822, $ex->getCode());
        }
    }

    public function testGetModule1()
    {
        $this->assertInstanceOf('Nethgui\Core\ModuleInterface', $this->object->getModule('TestModule1'));
    }

    public function testGetModule2()
    {
        try {
            $this->object->getModule('NullModule');
        } catch (\Exception $ex) {
            $this->assertInstanceOf('RuntimeException', $ex);
            $this->assertEquals(1322231262, $ex->getCode());
        }
    }

    public function testGetModule3()
    {
        for ($i = 1; $i <= 4; $i ++ ) {
            $identifier = sprintf('TestModule%d', $i);
            $this->assertSame($this->object->getModule($identifier), $this->object->getModule($identifier));
        }
    }

    public function testSetLog()
    {
        $this->object->setLog(new \Nethgui\Log\Nullog());
    }

}

