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

    const BUTTON_SUBMIT = 0x40;
    const BUTTON_CANCEL = 0x80;
    const BUTTON_RESET = 0x100;
    const BUTTON_LINK = 0x200;
    const BUTTON_CUSTOM = 0x400;

    const DIALOG_MODAL = 0x800;
    const DIALOG_EMBEDDED = 0x1000;      

    /**
     *
     * @var NethGui_Core_ViewInterface
     */
    protected $view;

    public function __construct(NethGui_Core_ViewInterface $view)
    {
        $this->view = $view;
    }

    public function __toString()
    {
        return $this->render();
    }

    public function copyFrom($data)
    {
        throw new NethGui_Exception_View('Cannot change the view values');
    }

    public function getIterator()
    {
        return $this->view->getIterator($data);
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

    public function spawnView(NethGui_Core_ModuleInterface $module, $register = FALSE)
    {
        throw new NethGui_Exception_View('Cannot spawn a view now');
    }

    /**
     * Concatenate an arbitrary text string.
     * @param string $text
     * @param boolean $hsc Optional - Apply htmlspecialchars() to $text
     * @return NethGui_Renderer_Abstract Same object
     */
    abstract public function append($text, $hsc = TRUE);

    /**
     * Concatenate the View member $offset
     * @return NethGui_Renderer_Abstract Same object
     */
    abstract public function inset($offset);

    /**
     * Concatenate a text input control
     * @param string $name The view member name
     * @param integer $flags Optional {STATE_DISABLED}
     * @return NethGui_Renderer_Abstract Same object
     */
    abstract public function textInput($name, $flags = 0);

    /**
     * Concatenate an hidden control
     * @param string $name The view member name
     * @param integer $flags Optional {STATE_DISABLED}
     * @return NethGui_Renderer_Abstract Same object
     */
    abstract public function hidden($name, $flags = 0);

    /**
     * Concatenate a radio button control
     * @param string $name The view member name
     * @param string $value The value assigned to the control, when selected.
     * @param integer $flags Optional {STATE_DISABLED, STATE_CHECKED}
     * @return NethGui_Renderer_Abstract Same object
     */
    abstract public function radioButton($name, $value, $flags = 0);

    /**
     * Concatenate a checkbox control
     * @param string $name The view member name
     * @param string $value The value assigned to the control, when selected.
     * @param integer $flags Optional {STATE_DISABLED, STATE_CHECKED}
     * @return NethGui_Renderer_Abstract Same object
     */
    abstract public function checkBox($name, $value, $flags = 0);

    /**
     * Concatenate a button control
     * @param string $name The view member name
     * @param integer $flags Optional - {DIALOG_*, STATE_ENABLED}
     * @param string|array $value Optional - Action to execute for LINK and CANCEL button types.
     * @return NethGui_Renderer_Abstract Same object
     */
    abstract public function button($name, $flags = 0, $value = NULL);

    /**
     * Renders a dialog box container.
     *
     * @param string $identifier The identifier of the dialog
     * @param int $flags Render flags: {DIALOG_MODAL, DIALOG_EMBEDDED, STATE_DISABLED}
     * @return NethGui_Renderer_Abstract A new object instance
     */
    abstract public function dialog($identifier, $flags = 0);

    /**
     * Renders a tab container.
     *
     * @param string $name The identifier of the control
     * @param array $pages Optional - The identifier list of the pages. NULL includes all the sub-views of the current object.
     * @param integer $flags {STATE_DISABLED}
     * @return NethGui_Renderer_Abstract A new object instance, representing the tab list.
     */
    abstract public function tabs($name, $pages = NULL, $flags = 0);

    /**
     * Renders a simple form container.
     * @param string $action Optional - The form action name.
     * @param integer $flags Optional - {STATE_DISABLED}
     * @return NethGui_Renderer_Abstract A new object instance
     */
    abstract public function form($action = '', $flags = 0);

    /**
     * Renders a selectable fieldset container.
     *
     * @see checkbox()
     * @param string $name
     * @param string $value
     * @param integer $flags
     * @return NethGui_Renderer_Abstract A new object instance, representing the fieldset surface
     */
    abstract public function fieldsetSwitch($name, $value, $flags = 0);

    /**
     * Renders a panel container
     *
     * @param string $identifier
     * @param integer $flags
     * @return NethGui_Renderer_Abstract A new object instance, representing the panel surface
     */
    abstract public function panel($identifier = NULL, $flags = 0);
}
