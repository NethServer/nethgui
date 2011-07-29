<?php
/**
 * @package Renderer
 * @author Davide Principi <davide.principi@nethesis.it>
 */

/**
 * A decorator that enhances a View with rendering capabilities
 * and forbids changes to the view data.
 *
 * Rendering methods calls can be chained, as they return a Renderer instance:
 * - Invoking a "container" method returns a new Renderer instance,
 *   representing the container itself.
 * - Invoking a "control" method returns the same Renderer instance.
 *
 * Invoking the render() method, or casting the object to a string resets the
 * object to the initial (empty string) state.
 *
 * @see http://en.wikipedia.org/wiki/Decorator_pattern
 * @package Renderer
 */
abstract class NethGui_Renderer_Abstract implements NethGui_Core_ViewInterface
{
    const LABEL_LEFT = 0x01;
    const LABEL_RIGHT = 0x02;
    const LABEL_ABOVE = 0x04;

    const STATE_CHECKED = 0x08;
    const STATE_DISABLED = 0x10;
    const STATE_VALIDATION_ERROR = 0x20;
    const STATE_READONLY = 0x10000;

    const BUTTON_SUBMIT = 0x40;
    const BUTTON_CANCEL = 0x80;
    const BUTTON_RESET = 0x100;
    const BUTTON_LINK = 0x200;
    const BUTTON_CUSTOM = 0x400;

    const DIALOG_MODAL = 0x800;
    const DIALOG_EMBEDDED = 0x1000;
    const DIALOG_SUCCESS = 0x2000;
    const DIALOG_WARNING = 0x4000;
    const DIALOG_ERROR = 0x8000;

    const SELECTOR_SINGLE = 0x20000;
    const SELECTOR_MULTIPLE = 0x40000;

    /**
     *
     * @var NethGui_Core_ViewInterface
     */
    protected $view;

    public function __construct(NethGui_Core_ViewInterface $view)
    {
        $this->view = $view;
    }

    public function copyFrom($data)
    {
        throw new NethGui_Exception_View('Cannot change the view values');
    }

    public function getIterator()
    {
        return $this->view->getIterator();
    }

    public function getModule()
    {
        return $this->view->getModule();
    }

    public function offsetExists($offset)
    {
        return $this->view->offsetExists($offset);
    }

    public function offsetGet($offset)
    {
        return $this->view->offsetGet($offset);
    }

    public function offsetSet($offset, $value)
    {
        throw new NethGui_Exception_View('Cannot change the view value');
    }

    public function offsetUnset($offset)
    {
        throw new NethGui_Exception_View('Cannot unset a view value');
    }

    /**
     * Must override this method:
     */
    public function render()
    {
        throw new NethGui_Exception_View('Cannot render an abstract renderer object');
    }

    public function setTemplate($template)
    {
        throw new NethGui_Exception_View('Cannot change the view template');
    }

    public function getTemplate()
    {
        return $this->view->getTemplate();
    }

    public function spawnView(NethGui_Core_ModuleInterface $module, $register = FALSE)
    {
        throw new NethGui_Exception_View('Cannot spawn a view now');
    }

    public function translate($message, $args = array())
    {
        return $this->view->translate($message, $args);
    }

    public function getModulePath()
    {
        return $this->view->getModulePath();
    }

    public function getUniqueId($parts = NULL)
    {
        return $this->view->getUniqueId($parts);
    }

    public function getControlName($parts = '')
    {
        return $this->view->getControlName($parts);
    }

    /**
     * Create a member inclusion
     * @param string $name The view member name
     * @param integer $flags Optional {STATE_DISABLED}
     * @return NethGui_Renderer_WidgetInterface
     */
    abstract public function inset($name, $flags = 0);

    /**
     * Create a text input control
     * @param string $name The view member name
     * @param integer $flags Optional {STATE_DISABLED}
     * @return NethGui_Renderer_WidgetInterface
     */
    abstract public function textInput($name, $flags = 0);

    /**
     * Create a text input control
     * @param string $name The view member name
     * @param integer $flags Optional {STATE_DISABLED}
     * @return NethGui_Renderer_WidgetInterface
     */
    abstract public function text($name, $flags = 0);

    /**
     * Create an hidden control
     * @param string $name The view member name
     * @param integer $flags Optional {STATE_DISABLED}
     * @return NethGui_Renderer_WidgetInterface
     */
    abstract public function hidden($name, $flags = 0);

    /**
     * Create a selector control
     *
     * @param string $name The view member name holding the selected value(s)
     * @param integer $flags
     * @return NethGui_Renderer_WidgetInterface
     */
    abstract public function selector($name, $flags = 0);

    /**
     * Create a button control
     * @param string $name The view member name
     * @param integer $flags Optional - {DIALOG_*, STATE_ENABLED}
     * @return NethGui_Renderer_WidgetInterface
     */
    abstract public function button($name, $flags = 0);

    /**
     * Create a radio button control
     * @param string $name The view member name
     * @param string $value The value assigned to the control, when selected.
     * @param integer $flags Optional {STATE_DISABLED, STATE_CHECKED}
     * @return NethGui_Renderer_WidgetInterface
     */
    abstract public function radioButton($name, $value, $flags = 0);

    /**
     * Create a checkbox control
     * @param string $name The view member name
     * @param string $value The value assigned to the control, when selected.
     * @param integer $flags Optional {STATE_DISABLED, STATE_CHECKED}
     * @return NethGui_Renderer_WidgetInterface
     */
    abstract public function checkBox($name, $value, $flags = 0);

    /**
     * Create a selectable fieldset container.
     *
     * @see checkbox()
     * @param string $name
     * @param string $value
     * @param integer $flags
     * @return NethGui_Renderer_WidgetInterface
     */
    abstract public function fieldsetSwitch($name, $value, $flags = 0);

    /**
     * Create a dialog box container.
     *
     * @param int $flags Render flags: {DIALOG_MODAL, DIALOG_EMBEDDED, STATE_DISABLED, DIALOG_SUCCESS, DIALOG_WARNING, DIALOG_ERROR}
     * @return NethGui_Renderer_WidgetInterface
     */
    abstract public function dialog($flags = 0);

    /**
     * Create a tabs container.
     *
     * @param integer $flags {STATE_DISABLED}
     * @return NethGui_Renderer_WidgetInterface
     */
    abstract public function tabs($flags = 0);

    /**
     * Create a simple form container.
     * @param integer $flags Optional - {STATE_DISABLED}
     * @return NethGui_Renderer_WidgetInterface
     */
    abstract public function form($flags = 0);

    /**
     * Create a panel container
     *
     * @param integer $flags
     * @return NethGui_Renderer_WidgetInterface
     */
    abstract public function panel($flags = 0);

    /**
     * Create a list of buttons
     * 
     * The buttons are specified as arrays of arguments for the button() method.
     * 
     * @param integer $flags
     * @return NethGui_Renderer_WidgetInterface
     */
    abstract public function elementList($flags = 0);
}
