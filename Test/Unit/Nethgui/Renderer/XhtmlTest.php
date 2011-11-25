<?php
namespace Test\Unit\Nethgui\Renderer;
class XhtmlTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Nethgui\Renderer\Xhtml
     */
    protected $object;

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

        $this->object = new \Nethgui\Renderer\Xhtml($view, array($this, 'failResolver'));
    }    

    public function testGetDefaultFlags()
    {
        $this->testSetDefaultFlags();
        $this->assertEquals(1, $this->object->getDefaultFlags());
    }

    public function testSetDefaultFlags()
    {
        $this->assertSame($this->object, $this->object->setDefaultFlags(1));
    }

    public function testElementList()
    {
        $this->assertInstanceOf('Nethgui\Widget\Xhtml\ElementList', $this->object->elementList());
    }

    public function testButtonList()
    {
        $this->assertInstanceOf('Nethgui\Widget\Xhtml\ElementList', $this->object->elementList());
    }

    public function testButton()
    {
        $this->assertInstanceOf('Nethgui\Widget\Xhtml\Button', $this->object->button('button'));
    }

    public function testCheckBox()
    {
        $this->assertInstanceOf('Nethgui\Widget\Xhtml\CheckBox', $this->object->checkBox('checkbox', 1));
    }

    public function testDialog()
    {
        $this->assertInstanceOf('Nethgui\Widget\XhtmlWidget', $this->object->dialog('test'));
    }

    public function testFieldsetSwitch()
    {
        $this->assertInstanceOf('Nethgui\Widget\Xhtml\FieldsetSwitch', $this->object->fieldsetSwitch('fieldsetSwitch', 'on'));
    }

    public function testForm()
    {
        $this->assertInstanceOf('Nethgui\Widget\XhtmlWidget', $this->object->form());
    }

    public function testHidden()
    {
        $this->assertInstanceOf('Nethgui\Widget\XhtmlWidget', $this->object->hidden('test'));
    }

    public function testInset()
    {
        $this->assertInstanceOf('Nethgui\Widget\XhtmlWidget', $this->object->inset('test'));
    }

    public function testPanel()
    {
        $this->assertInstanceOf('Nethgui\Widget\Xhtml\Panel', $this->object->panel('test'));
    }

    public function testRadioButton()
    {
        $this->assertInstanceOf('Nethgui\Widget\Xhtml\RadioButton', $this->object->radioButton('radiobutton', 999));
    }

    public function testSelector()
    {
        $type = 'Nethgui\Widget\Xhtml\Selector';
        $widget = $this->object->selector('selector');
        $this->assertInstanceOf($type, $widget);
    }

    public function testTabs()
    {
        $type = 'Nethgui\Widget\Xhtml\Tabs';
        $widget = $this->object->tabs('tabs');
        $this->assertInstanceOf($type, $widget);
    }

    public function testTextInput()
    {
        $type = 'Nethgui\Widget\Xhtml\TextInput';
        $widget = $this->object->textInput('inputText');
        $this->assertInstanceOf($type, $widget);
    }

    public function testTextLabel()
    {
        $type = 'Nethgui\Widget\Xhtml\TextLabel';
        $widget = $this->object->textLabel('label');
        $this->assertInstanceOf($type, $widget);
    }

    public function testFieldset1()
    {
        $type = 'Nethgui\Widget\Xhtml\Fieldset';
        $widget = $this->object->fieldset();
        $this->assertInstanceOf($type, $widget);
    }

    public function testFieldset2()
    {
        $type = 'Nethgui\Widget\Xhtml\Fieldset';
        $widget = $this->object->fieldset('fieldset');
        $this->assertInstanceOf($type, $widget);
    }

    public function testHeader()
    {
        $type = 'Nethgui\Widget\Xhtml\TextLabel';
        $widget = $this->object->header('header');
        $this->assertInstanceOf($type, $widget);
    }

    public function testLiteral()
    {
        $type = 'Nethgui\Widget\Xhtml\Literal';
        $widget = $this->object->literal('<data></data>');
        $this->assertInstanceOf($type, $widget);
    }

    public function testColumns()
    {
        $type = 'Nethgui\Widget\Xhtml\Columns';
        $widget = $this->object->columns();
        $this->assertInstanceOf($type, $widget);
    }

    public function testProgressBar()
    {
        $type = 'Nethgui\Widget\Xhtml\ProgressBar';
        $widget = $this->object->progressBar('progress');
        $this->assertInstanceOf($type, $widget);
    }

    public function testTextArea()
    {
        $type = 'Nethgui\Widget\Xhtml\TextArea';
        $widget = $this->object->textArea('textarea');
        $this->assertInstanceOf($type, $widget);
    }

    public function testConsole()
    {
        $type = 'Nethgui\Widget\Xhtml\TextArea';
        $widget = $this->object->console('console');
        $this->assertInstanceOf($type, $widget);
    }

    public function testDateInput()
    {
        $type = 'Nethgui\Widget\Xhtml\TextInput';
        $widget = $this->object->dateInput('date');
        $this->assertInstanceOf($type, $widget);
    }

    public function testObjectPicker()
    {
        $type = 'Nethgui\Widget\Xhtml\ObjectPicker';
        $widget = $this->object->objectPicker('picker');
        $this->assertInstanceOf($type, $widget);
    }

    public function testGetCommandReceiver()
    {
        $type = 'Nethgui\Core\CommandReceiverInterface';
        $o = $this->object->getCommandReceiver();
        $this->assertInstanceOf($type, $o);
    }

}

