<?php
/**
 * @package Renderer
 * @author Davide Principi <davide.principi@nethesis.it>
 */

namespace Nethgui\Renderer;


/**
 * Create Widget objects applying default widget flags
 *
 * The interface methods create and configure widget objects.
 *
 * @see WidgetInterface
 * @package Renderer
 */
interface WidgetFactoryInterface
{
    const LABEL_NONE = 0x1;
    const LABEL_LEFT = 0x2;
    const LABEL_RIGHT = 0x4;
    const LABEL_ABOVE = 0x8;

    const STATE_CHECKED = 0x10;
    const STATE_DISABLED = 0x20;
    const STATE_VALIDATION_ERROR = 0x40;
    const STATE_READONLY = 0x80;

    const BUTTON_SUBMIT = 0x100;
    const BUTTON_CANCEL = 0x200;
    const BUTTON_RESET = 0x400;
    const BUTTON_LINK = 0x800;
    const BUTTON_CUSTOM = 0x1000;

    const DIALOG_MODAL = 0x4000;
    const DIALOG_SUCCESS = 0x8000;
    const DIALOG_WARNING = 0x10000;
    const DIALOG_ERROR = 0x20000;

    const SELECTOR_MULTIPLE = 0x40000;
    const SELECTOR_DROPDOWN = 0x80000;

    const TEXTINPUT_PASSWORD = 0x100000;
    const FIELDSET_EXPANDABLE = 0x200000;
    const BUTTONSET = 0x400000;
    const BUTTON_DROPDOWN = 0x800000;
    const BUTTON_HELP = 0x1000000;

    /**
     *
     * @return integer
     */
    public function getDefaultFlags();

    /**
     * @return WidgetFactoryInterface
     */
    public function setDefaultFlags($flags);

    /**
     * Include a view element that is a sub-view
     * @param string $name The view member name
     * @param integer $flags Optional {STATE_DISABLED}
     * @return WidgetInterface
     */
    public function inset($name, $flags = 0);

    /**
     * Create a text input control
     * @param string $name The view member name
     * @param integer $flags Optional {STATE_DISABLED}
     * @return WidgetInterface
     */
    public function textInput($name, $flags = 0);

    /**
     * Create a text label.
     *
     * @param string $name The view member name to generate the label contents
     * @param integer $flags Optional {STATE_DISABLED}
     * @return WidgetInterface
     */
    public function textLabel($name, $flags = 0);

    /**
     * Create a fieldset container
     *
     * @see textLabel()
     * @param string $name OPTIONAL - The view member passed as argument for the "template" attribute.
     * @param integer $flags OPTIONAL - flags
     * @return WidgetInterface
     */
    public function fieldset($name = NULL, $flags = 0);

    /**
     * Create a text header control
     *
     * @see textLabel()
     * @param string $name OPTIONAL - The view member passed as argument for the "template" attribute.
     */
    public function header($name = NULL, $flags = 0);

    /**
     * Create an hidden control
     * @param string $name The view member name
     * @param integer $flags Optional {STATE_DISABLED}
     * @return WidgetInterface
     */
    public function hidden($name, $flags = 0);

    /**
     * Create a selector control
     *
     * @param string $name The view member name holding the selected value(s)
     * @param integer $flags
     * @return WidgetInterface
     */
    public function selector($name, $flags = 0);

    /**
     * Create a button control
     * @param string $name The view member name
     * @param integer $flags Optional - {DIALOG_*, STATE_ENABLED}
     * @return WidgetInterface
     */
    public function button($name, $flags = 0);

    /**
     * Create a radio button control
     * @param string $name The view member name
     * @param string $value The value assigned to the control, when selected.
     * @param integer $flags Optional {STATE_DISABLED, STATE_CHECKED}
     * @return WidgetInterface
     */
    public function radioButton($name, $value, $flags = 0);

    /**
     * Create a checkbox control
     * @param string $name The view member name
     * @param string $value The value assigned to the control, when selected.
     * @param integer $flags Optional {STATE_DISABLED, STATE_CHECKED}
     * @return WidgetInterface
     */
    public function checkBox($name, $value, $flags = 0);

    /**
     * Create a selectable fieldset container.
     *
     * @see checkbox()
     * @param string $name
     * @param string $value
     * @param integer $flags
     * @return WidgetInterface
     */
    public function fieldsetSwitch($name, $value, $flags = 0);

    /**
     * Create a dialog box container.
     *
     * @param int $flags Render flags: {DIALOG_MODAL, DIALOG_EMBEDDED, STATE_DISABLED, DIALOG_SUCCESS, DIALOG_WARNING, DIALOG_ERROR}
     * @return WidgetInterface
     */
    public function dialog($name, $flags = 0);

    /**
     * Create a tabs container.
     *
     * @param integer $flags {STATE_DISABLED}
     * @return WidgetInterface
     */
    public function tabs($flags = 0);

    /**
     * Create a simple form container.
     * @param integer $flags Optional - {STATE_DISABLED}
     * @return WidgetInterface
     */
    public function form($flags = 0);

    /**
     * Create a panel container
     *
     * @param integer $flags
     * @return WidgetInterface
     */
    public function panel($flags = 0);

    /**
     * Create a list of elements
     *
     * Add the actual elements invoking the insert() operation of the returned object.
     *
     * @param integer $flags
     * @return WidgetInterface
     */
    public function elementList($flags = 0);

    /**
     * Create a list of button elements
     *
     * Add the actual elements invoking the insert() operation of the returned object.
     *
     * @param integer $flags
     * @return WidgetInterface
     */
    public function buttonList($flags = 0);


    /**
     * Create literal data - helper.
     *
     * @param string|object|\Nethgui\Core\ViewInterface $data Can be a string, any object implementing toString() method, or a View.
     * @return WidgetInterface
     */
    public function literal($data, $flags = 0);

    /**
     * Create a column container - helper.
     *
     * Add the actual columns through the insert() operation of the returned object
     *
     * @return WidgetInterface
     */
    public function columns();

    /**
     * Create a progress bar
     *
     * Refs #554.
     *
     * - name View member holding the percent value Int range [0, 100]
     */
    public function progressBar($name, $flags = 0);

    /**
     * Create a text area
     *
     * Refs #556
     *
     * Attributes:
     * - dimensions
     * - appendOnly
     */
    public function textArea($name, $flags = 0);


    /**
     * Create a console-like text area
     *
     * @see textArea()
     */
    public function console($name, $flags = 0);

    /**
     * Create a date picker widget:
     *
     * Refs #474
     *
     * Attributes:
     * - class (string) "Date " plus one of "be" (default), "me", "le".
     */
    public function dateInput($name, $flags);


    /*
     * Pick/selects objects from a collection
     * @see selector()
     */
    public function objectPicker($name = NULL, $flags = 0);


}

define('NETHGUI_INHERITABLE_FLAGS', WidgetFactoryInterface::STATE_DISABLED | WidgetFactoryInterface::LABEL_ABOVE | WidgetFactoryInterface::LABEL_LEFT | WidgetFactoryInterface::LABEL_RIGHT | WidgetFactoryInterface::LABEL_NONE);
