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
interface NethGui_Renderer_Abstract
{
    const LABEL_LEFT = 0x01;
    const LABEL_RIGHT = 0x02;
    const LABEL_ABOVE = 0x04;

    const STATE_CHECKED = 0x08;
    const STATE_DISABLED = 0x10;
    const STATE_VALIDATION_ERROR = 0x20;
    const STATE_READONLY = 0x40;

    const BUTTON_SUBMIT = 0x80;
    const BUTTON_CANCEL = 0x100;
    const BUTTON_RESET = 0x200;
    const BUTTON_LINK = 0x400;
    const BUTTON_CUSTOM = 0x800;

    const DIALOG_MODAL = 0x1000;
    const DIALOG_SUCCESS = 0x4000;
    const DIALOG_WARNING = 0x8000;
    const DIALOG_ERROR = 0x10000;
    
    const SELECTOR_MULTIPLE = 0x20000;

    /**
     * Create a member inclusion
     * @param string $name The view member name
     * @param integer $flags Optional {STATE_DISABLED}
     * @return NethGui_Renderer_WidgetInterface
     */
    public function inset($name, $flags = 0);

    /**
     * Create a text input control
     * @param string $name The view member name
     * @param integer $flags Optional {STATE_DISABLED}
     * @return NethGui_Renderer_WidgetInterface
     */
    public function textInput($name, $flags = 0);

    /**
     * Create a plain text control
     * @param string $name The view member name
     * @param integer $flags Optional {STATE_DISABLED}
     * @return NethGui_Renderer_WidgetInterface
     */
    public function textLabel($name, $flags = 0);

    /**
     * Create a text header control
     */
    public function header($name, $flags = 0);

    /**
     * Create an hidden control
     * @param string $name The view member name
     * @param integer $flags Optional {STATE_DISABLED}
     * @return NethGui_Renderer_WidgetInterface
     */
    public function hidden($name, $flags = 0);

    /**
     * Create a selector control
     *
     * @param string $name The view member name holding the selected value(s)
     * @param integer $flags
     * @return NethGui_Renderer_WidgetInterface
     */
    public function selector($name, $flags = 0);

    /**
     * Create a button control
     * @param string $name The view member name
     * @param integer $flags Optional - {DIALOG_*, STATE_ENABLED}
     * @return NethGui_Renderer_WidgetInterface
     */
    public function button($name, $flags = 0);

    /**
     * Create a radio button control
     * @param string $name The view member name
     * @param string $value The value assigned to the control, when selected.
     * @param integer $flags Optional {STATE_DISABLED, STATE_CHECKED}
     * @return NethGui_Renderer_WidgetInterface
     */
    public function radioButton($name, $value, $flags = 0);

    /**
     * Create a checkbox control
     * @param string $name The view member name
     * @param string $value The value assigned to the control, when selected.
     * @param integer $flags Optional {STATE_DISABLED, STATE_CHECKED}
     * @return NethGui_Renderer_WidgetInterface
     */
    public function checkBox($name, $value, $flags = 0);

    /**
     * Create a selectable fieldset container.
     *
     * @see checkbox()
     * @param string $name
     * @param string $value
     * @param integer $flags
     * @return NethGui_Renderer_WidgetInterface
     */
    public function fieldsetSwitch($name, $value, $flags = 0);

    /**
     * Create a dialog box container.
     *
     * @param int $flags Render flags: {DIALOG_MODAL, DIALOG_EMBEDDED, STATE_DISABLED, DIALOG_SUCCESS, DIALOG_WARNING, DIALOG_ERROR}
     * @return NethGui_Renderer_WidgetInterface
     */
    public function dialog($name, $flags = 0);

    /**
     * Create a tabs container.
     *
     * @param integer $flags {STATE_DISABLED}
     * @return NethGui_Renderer_WidgetInterface
     */
    public function tabs($flags = 0);

    /**
     * Create a simple form container.
     * @param integer $flags Optional - {STATE_DISABLED}
     * @return NethGui_Renderer_WidgetInterface
     */
    public function form($flags = 0);

    /**
     * Create a panel container
     *
     * @param integer $flags
     * @return NethGui_Renderer_WidgetInterface
     */
    public function panel($flags = 0);

    /**
     * Create a list of buttons
     * 
     * The buttons are specified as arrays of arguments for the button() method.
     * 
     * @param integer $flags
     * @return NethGui_Renderer_WidgetInterface
     */
    public function elementList($flags = 0);


    public function literal($data);
}
