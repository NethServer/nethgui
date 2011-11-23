<?php
/**
 * @package Tests
 *
 */

/**
 * @package Tests
 *
 */
class Nethgui\Renderer\HttpCommandReceiverTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var Nethgui\Renderer\HttpCommandReceiver
     */
    protected $object;

    private $urlPrefix;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $view = $this->getMockBuilder('Nethgui\Client\View')
            ->disableOriginalConstructor()
            ->setMethods(array('getModule'))
            ->getMock();

        $moduleMock = $this->getMockBuilder('Nethgui\Core\ModuleInterface')
            ->getMock();

        $moduleMock->expects($this->any())
            ->method('getIdentifier')
            ->will($this->returnValue('ModuleId'));

        $moduleMock->expects($this->any())
            ->method('getParent')
            ->will($this->returnValue(NULL));

        $view->expects($this->any())
            ->method('getModule')
            ->will($this->returnValue($moduleMock));

        $this->object = new Nethgui\Renderer\HttpCommandReceiver($view);
        $this->urlPrefix = NETHGUI_SITEURL . NETHGUI_BASEURL . NETHGUI_CONTROLLER;
    }

    public function testCancel()
    {
        $this->object->setGlobalFunctionWrapper($this->getGlobalMock($this->urlPrefix));
        $this->object->cancel();
    }


    public function testActivateAction()
    {
        $url = '/OtherModule/a/b#c';
        $this->object->setGlobalFunctionWrapper($this->getGlobalMock($this->urlPrefix . $url));
        $this->object->activateAction('../OtherModule/a/b#c');
    }


    public function testEnable()
    {
        $url = '/ModuleId';
        $this->object->setGlobalFunctionWrapper($this->getGlobalMock($this->urlPrefix . $url));
        $this->object->enable();
    }


    public function testRedirect()
    {
        $url = 'http://www.example.com';
        $this->object->setGlobalFunctionWrapper($this->getGlobalMock($url));
        $this->object->redirect($url);
    }

    private function getGlobalMock($url)
    {
        $mock = $this->getMockBuilder('Nethgui\Core\GlobalFunctionWrapper')
            ->setMethods(array('header', 'ob_get_status', 'ob_end_clean', 'phpExit'))
            ->getMock();

        $mock->expects($this->at(0))
            ->method('header')
            ->with(new PHPUnit_Framework_Constraint_StringStartsWith('HTTP/1.1 302'));

        $mock->expects($this->at(1))
            ->method('header')
            ->with('Location: ' . $url);

        $mock->expects($this->once())
            ->method('ob_get_status')
            ->will($this->returnValue(array('dummy')));

        $mock->expects($this->once())
            ->method('ob_end_clean');

        $mock->expects($this->once())
            ->method('phpExit');

        return $mock;
    }

}

?>
