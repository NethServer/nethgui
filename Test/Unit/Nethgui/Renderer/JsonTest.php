<?php

class Nethgui_Renderer_JsonTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var Nethgui_Renderer_Json
     */
    protected $object;

    protected function setUp()
    {
        $view = $this->getMockBuilder('Nethgui_Core_ViewInterface')
            ->getMock();

        $module = new Test_Unit_NethguiCoreModuleClientAdapter();

        $module->initialize();

        $translator = $this->getMockBuilder('Nethgui_Core_TranslatorInterface')
            ->getMock();

        $translator->expects($this->any())
            ->method('translate')->will($this->returnArgument(0));

        $translator->expects($this->any())
            ->method('getLanguageCode')->will($this->returnValue('en'));

        $view = new Nethgui_Client_View($module, $translator);
        
        $module->prepareView($view, 0);
        
        $this->object = new Nethgui_Renderer_Json($view);
    }

    public function testRender()
    {
        $events = json_decode((String) $this->object);
       
        $this->assertInternalType('array', $events);

        $expected = array(
            array(
                'ID_a',
                array('A', 'AA', 'AAA')
            ),
            array(
                'ID_b',
                '10.2'
            ),
            array(
                'ID_c',
                array('C', 'CC', 'CCC', 'CCCC', array('X'))
            ),
            array(
                'ID___invalidParameters',
                array()
            )
        );

        $this->assertEquals($expected, $events);
    }

}

/**
 * @ignore
 */
class Test_Unit_NethguiCoreModuleClientAdapter extends Nethgui_Core_Module_Standard
{

    public function __construct($identifier = NULL)
    {
        parent::__construct('ID');
    }

    public function initialize()
    {
        parent::initialize();

        $this->parameters['a'] = array('A', 'AA', 'AAA');
        $this->parameters['b'] = '10.2';
        $this->parameters['c'] = new ArrayObject(array('C', 'CC', 'CCC', 'CCCC', new ArrayObject(array('X'))));
    }

    public function testGetClientEvents()
    {
        $events = $this->object->getClientEvents();

        $this->assertInternalType('array', $events);

        $expected = array(
            array(
                'ID_a',
                array('A', 'AA', 'AAA')
            ),
            array(
                'ID_b',
                '10.2'
            ),
            array(
                'ID_c',
                array('C', 'CC', 'CCC', 'CCCC', array('X'))
            ),
            array(
                'ID___invalidParameters',
                array()
            )
        );

        $this->assertEquals($expected, $events);
    }

}
