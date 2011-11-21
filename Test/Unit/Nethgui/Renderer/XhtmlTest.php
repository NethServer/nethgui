<?php
/**
 * @package Tests
 *
 */

/**
 * @package Tests
 *
 */
class Nethgui_Renderer_XhtmlTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var Nethgui_Renderer_Xhtml
     */
    protected $object;

    protected function setUp()
    {
        $view = $this->getMockBuilder('Nethgui_Client_View')
            ->disableOriginalConstructor()
            ->setMethods(array('getModule'))
            ->getMock();

        $moduleMock = $this->getMockBuilder('Nethgui_Core_ModuleInterface')
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

        $this->object = new Nethgui_Renderer_Xhtml($view);
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
        $this->assertInstanceOf('Nethgui_Widget_Xhtml_ElementList', $this->object->elementList());
    }

    public function testButtonList()
    {
        $this->assertInstanceOf('Nethgui_Widget_Xhtml_ElementList', $this->object->elementList());
    }

    public function testButton()
    {
        $this->assertInstanceOf('Nethgui_Widget_Xhtml_Button', $this->object->button('button'));
    }

    public function testCheckBox()
    {
        $this->assertInstanceOf('Nethgui_Widget_Xhtml_CheckBox', $this->object->checkBox('checkbox', 1));
    }

    public function testDialog()
    {
        $this->assertInstanceOf('Nethgui_Widget_Xhtml', $this->object->dialog('test'));
    }

    public function testFieldsetSwitch()
    {
        $this->assertInstanceOf('Nethgui_Widget_Xhtml_FieldsetSwitch', $this->object->fieldsetSwitch('fieldsetSwitch', 'on'));
    }

    public function testForm()
    {
        $this->assertInstanceOf('Nethgui_Widget_Xhtml', $this->object->form());
    }

    public function testHidden()
    {
        $this->assertInstanceOf('Nethgui_Widget_Xhtml', $this->object->hidden('test'));
    }

    public function testInset()
    {
        $this->assertInstanceOf('Nethgui_Widget_Xhtml', $this->object->inset('test'));
    }

    public function testPanel()
    {
        $this->assertInstanceOf('Nethgui_Widget_Xhtml_Panel', $this->object->panel('test'));
    }

    public function testRadioButton()
    {
        $this->assertInstanceOf('Nethgui_Widget_Xhtml_RadioButton', $this->object->radioButton('radiobutton', 999));
    }

    public function testSelector()
    {
        $type = 'Nethgui_Widget_Xhtml_Selector';
        $widget = $this->object->selector('selector');
        $this->assertInstanceOf($type, $widget);
    }

    public function testTabs()
    {
        $type = 'Nethgui_Widget_Xhtml_Tabs';
        $widget = $this->object->tabs('tabs');
        $this->assertInstanceOf($type, $widget);
    }

    public function testTextInput()
    {
        $type = 'Nethgui_Widget_Xhtml_TextInput';
        $widget = $this->object->textInput('inputText');
        $this->assertInstanceOf($type, $widget);
    }

    public function testTextLabel()
    {
        $type = 'Nethgui_Widget_Xhtml_TextLabel';
        $widget = $this->object->textLabel('label');
        $this->assertInstanceOf($type, $widget);
    }

    public function testFieldset1()
    {
        $type = 'Nethgui_Widget_Xhtml_Fieldset';
        $widget = $this->object->fieldset();
        $this->assertInstanceOf($type, $widget);
    }

    public function testFieldset2()
    {
        $type = 'Nethgui_Widget_Xhtml_Fieldset';
        $widget = $this->object->fieldset('fieldset');
        $this->assertInstanceOf($type, $widget);
    }

    public function testHeader()
    {
        $type = 'Nethgui_Widget_Xhtml_TextLabel';
        $widget = $this->object->header('header');
        $this->assertInstanceOf($type, $widget);
    }

    public function testLiteral()
    {
        $type = 'Nethgui_Widget_Xhtml_Literal';
        $widget = $this->object->literal('<data></data>');
        $this->assertInstanceOf($type, $widget);
    }

    public function testColumns()
    {
        $type = 'Nethgui_Widget_Xhtml_Columns';
        $widget = $this->object->columns();
        $this->assertInstanceOf($type, $widget);
    }

    public function testProgressBar()
    {
        $type = 'Nethgui_Widget_Xhtml_ProgressBar';
        $widget = $this->object->progressBar('progress');
        $this->assertInstanceOf($type, $widget);
    }

    public function testTextArea()
    {
        $type = 'Nethgui_Widget_Xhtml_TextArea';
        $widget = $this->object->textArea('textarea');
        $this->assertInstanceOf($type, $widget);
    }

    public function testConsole()
    {
        $type = 'Nethgui_Widget_Xhtml_TextArea';
        $widget = $this->object->console('console');
        $this->assertInstanceOf($type, $widget);
    }

    public function testDateInput()
    {
        $type = 'Nethgui_Widget_Xhtml_TextInput';
        $widget = $this->object->dateInput('date');
        $this->assertInstanceOf($type, $widget);
    }

    public function testObjectPicker()
    {
        $type = 'Nethgui_Widget_Xhtml_ObjectPicker';
        $widget = $this->object->objectPicker('picker');
        $this->assertInstanceOf($type, $widget);
    }

    public function testGetCommandReceiver()
    {
        $type = 'Nethgui_Core_CommandReceiverInterface';
        $o = $this->object->getCommandReceiver();
        $this->assertInstanceOf($type, $o);
    }

}

?>
