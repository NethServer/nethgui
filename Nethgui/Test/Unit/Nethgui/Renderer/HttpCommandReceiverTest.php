<?php
namespace Nethgui\Test\Unit\Nethgui\Renderer;

/**
 * @covers \Nethgui\Renderer\HttpCommandReceiver
 */
class HttpCommandReceiverTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Nethgui\Renderer\HttpCommandReceiver
     */
    protected $object;
    private $urlPrefix;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {

        $moduleMock = $this->getMockBuilder('\Nethgui\Module\ModuleInterface')
            ->getMock();

        $moduleMock->expects($this->any())
            ->method('getIdentifier')
            ->will($this->returnValue('ModuleId'));

        $moduleMock->expects($this->any())
            ->method('getParent')
            ->will($this->returnValue(NULL));

        $view = new \Nethgui\View\View(0, $moduleMock, $this->getMock('Nethgui\View\Translator', array(), array(), '', FALSE), array('http://localhost:8080', '/my', 'test.php'));

        $delegatedCommandReceiver = $this->getMockBuilder('\Nethgui\View\CommandReceiverInterface')
            ->setMethods(array('executeCommand'))
            ->getMock();

        $this->object = new \Nethgui\Renderer\HttpCommandReceiver($view, $delegatedCommandReceiver);
        $this->urlPrefix = 'http://localhost:8080/my/test.php';
    }

    public function testShow()
    {
        $this->markTestIncomplete();
    }

    public function testHttpHeader()
    {
        $this->markTestIncomplete();
    }

    public function testGetHttpHeaders()
    {
        $this->markTestIncomplete();
    }

    public function testHasRefresh()
    {
        $this->markTestIncomplete();
    }

    public function testHasLocation()
    {
        $this->markTestIncomplete();
    }

    public function testReloadData()
    {
        $this->markTestIncomplete();
    }

    public function testSendQuery()
    {
        $this->markTestIncomplete();
    }


    private function getGlobalMock($url)
    {
        $mock = $this->getMockBuilder('\Nethgui\Utility\PhpWrapper')
            ->setMethods(array('header', 'ob_get_status', 'ob_end_clean', 'phpExit'))
            ->getMock();

        $mock->expects($this->at(0))
            ->method('header')
            ->with(new \PHPUnit_Framework_Constraint_StringStartsWith('HTTP/1.1 302'));

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

