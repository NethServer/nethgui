<?php
namespace Test\Unit\Nethgui\Renderer;

/**
 * @covers \Nethgui\Renderer\Json
 */
class JsonTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Nethgui\Renderer\Json
     */
    protected $object;

    protected function setUp()
    {
        $view = $this->getMockBuilder('\Nethgui\View\ViewInterface')
            ->getMock();

        $innerModule = $this->getMockBuilder('\Nethgui\Module\ModuleInterface')
            // ->setMethods(array('getParent', 'getIdentifier'))
            ->getMock();


        $module = new NethguiCoreModuleJsonTest($innerModule, 'ID');

        $innerModule->expects($this->once())
            ->method('getParent')
            ->will($this->returnValue($module));

        $innerModule->expects($this->once())
            ->method('getIdentifier')
            ->will($this->returnValue('Inner'));

        $module->initialize();

        $translator = $this->getMockBuilder('\Nethgui\View\TranslatorInterface')
            ->getMock();

        $translator->expects($this->any())
            ->method('translate')->will($this->returnArgument(0));

        $translator->expects($this->any())
            ->method('getLanguageCode')->will($this->returnValue('en'));

        $view = new \Nethgui\View\View(1, $module, $translator);

        $module->prepareView($view);

       $delegatedCommandReceiver = $this->getMockBuilder('\Nethgui\Core\CommandReceiverInterface')
            ->setMethods(array('executeCommand'))
            ->getMock();

        $this->object = new \Nethgui\Renderer\Json($view, $delegatedCommandReceiver);
    }

    public function testRender()
    {
        $events = json_decode((String) $this->object);

        $this->assertInternalType('array', $events);

        $oTestCommand1 = json_decode(json_encode(array(
                'receiver' => '.ID_CMD1',
                'methodName' => 'testCommand',
                'arguments' => array(0, 'A')
            )));

        $oTestCommand2 = json_decode(json_encode(array(
                'receiver' => '#ID',
                'methodName' => 'testCommand',
                'arguments' => array(1, 'A')
            )));

        $oDelayedCommand1 = json_decode(json_encode(array(
                'receiver' => '#ID',
                'methodName' => 'delayedCommand',
                'arguments' => array('ABC', 'DEF')
            )));

        $oDelayedCommand2 = json_decode(json_encode(array(
                'receiver' => '.ID_CMD4',
                'methodName' => 'delayedCommand',
                'arguments' => array('ABC', 'DEF')
            )));

        $oTestCommand3 = json_decode(json_encode(array(
                'receiver' => '',
                'methodName' => 'delay',
                'arguments' => array($oDelayedCommand1, 1000)
            )));

        $oTestCommand4 = json_decode(json_encode(array(
                'receiver' => '',
                'methodName' => 'delay',
                'arguments' => array($oDelayedCommand2, 1000)
            )));



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
            ),
            array(
                'ID_Inner_X',
                5
            ),
            array(
                'ClientCommandHandler',
                array(
                    $oTestCommand1,
                    $oTestCommand2,
                    $oTestCommand3,
                    $oTestCommand4,
                )
            )
        );

        $this->assertEquals($expected, $events);
    }

}

/**
 */
class NethguiCoreModuleJsonTest extends \Nethgui\Controller\AbstractController
{

    /**
     * @var \Nethgui\Module\ModuleInterface
     * 
     */
    private $innerModule;

    public function __construct(\Nethgui\Module\ModuleInterface $innerModule, $identifier = NULL)
    {
        parent::__construct($identifier);
        $this->innerModule = $innerModule;
    }

    public function initialize()
    {
        parent::initialize();
        $this->parameters['a'] = array('A', 'AA', 'AAA');
        $this->parameters['b'] = '10.2';
        $this->parameters['c'] = new \ArrayObject(array('C', 'CC', 'CCC', 'CCCC', new \ArrayObject(array('X'))));
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);
        $view['VIEW'] = $view->spawnView($this->innerModule);
        $view['VIEW']['X'] = 5;
        $view['CMD1'] = $view->createUiCommand('testCommand', array(0, 'A'));
        $view[] = $view->createUiCommand('testCommand', array(1, 'A'));
        $innerCommand = $view->createUiCommand('delayedCommand', array('ABC', 'DEF'));
        $view[] = $view->createUiCommand('delay', array(clone $innerCommand, 1000));
        $view['CMD4'] = $view->createUiCommand('delay', array(clone $innerCommand, 1000));
    }

}
