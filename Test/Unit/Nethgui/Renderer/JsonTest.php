<?php
/**
 * @package Tests
 *
 */

/**
 * @package Tests
 *
 */
class Nethgui\Renderer\JsonTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var Nethgui\Renderer\Json
     */
    protected $object;

    protected function setUp()
    {
        $view = $this->getMockBuilder('Nethgui\Core\ViewInterface')
            ->getMock();

        $innerModule = $this->getMockBuilder('Nethgui\Core\ModuleInterface')
            // ->setMethods(array('getParent', 'getIdentifier'))
            ->getMock();


        $module = new Test\Unit\NethguiCoreModuleJsonTest($innerModule, 'ID');

        $innerModule->expects($this->once())
            ->method('getParent')
            ->will($this->returnValue($module));

        $innerModule->expects($this->once())
            ->method('getIdentifier')
            ->will($this->returnValue('Inner'));

        $module->initialize();

        $translator = $this->getMockBuilder('Nethgui\Core\TranslatorInterface')
            ->getMock();

        $translator->expects($this->any())
            ->method('translate')->will($this->returnArgument(0));

        $translator->expects($this->any())
            ->method('getLanguageCode')->will($this->returnValue('en'));

        $view = new Nethgui\Client\View($module, $translator);

        $module->prepareView($view, 0);

        $this->object = new Nethgui\Renderer\Json($view);
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
 * @ignore
 */
class Test\Unit\NethguiCoreModuleJsonTest extends Nethgui\Core\Module\Standard
{

    /**
     * @var Nethgui\Core\ModuleInterface
     * 
     */
    private $innerModule;

    public function __construct(Nethgui\Core\ModuleInterface $innerModule, $identifier = NULL)
    {
        parent::__construct($identifier);
        $this->innerModule = $innerModule;
    }

    public function initialize()
    {
        parent::initialize();
        $this->parameters['a'] = array('A', 'AA', 'AAA');
        $this->parameters['b'] = '10.2';
        $this->parameters['c'] = new ArrayObject(array('C', 'CC', 'CCC', 'CCCC', new ArrayObject(array('X'))));
    }

    public function prepareView(Nethgui\Core\ViewInterface $view, $mode)
    {
        parent::prepareView($view, $mode);
        $view['VIEW'] = $view->spawnView($this->innerModule);
        $view['VIEW']['X'] = 5;
        $view['CMD1'] = $view->createUiCommand('testCommand', array(0, 'A'));
        $view[] = $view->createUiCommand('testCommand', array(1, 'A'));
        $innerCommand = $view->createUiCommand('delayedCommand', array('ABC', 'DEF'));
        $view[] = $view->createUiCommand('delay', array(clone $innerCommand, 1000));
        $view['CMD4'] = $view->createUiCommand('delay', array(clone $innerCommand, 1000));
    }

}
