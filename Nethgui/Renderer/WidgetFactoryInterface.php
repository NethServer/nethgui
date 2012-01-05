<?php
namespace Nethgui\Renderer;

/*
 * Copyright (C) 2011 Nethesis S.r.l.
 * 
 * This script is part of NethServer.
 * 
 * NethServer is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * NethServer is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with NethServer.  If not, see <http://www.gnu.org/licenses/>.
 */


/**
 * Create Widget objects applying default widget flags
 *
 * The interface methods create and configure widget objects.
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @see WidgetInterface
 * @since 1.0
 * @api
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
    const STATE_UNOBSTRUSIVE = 0x2000000;

    const INSET_WRAP = 0x8000;
    const INSET_FORM = 0x10000;
    const INSET_DIALOG = 0x4000;
    
    const BUTTON_SUBMIT = 0x100;
    const BUTTON_CANCEL = 0x200;
    const BUTTON_RESET = 0x400;
    const BUTTON_LINK = 0x800;
    const BUTTON_CUSTOM = 0x1000;

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
     *
     * @param string $name The view member name
     * @param integer $flags Optional {STATE_DISABLED}
     * @return \Nethgui\Renderer\WidgetInterface
     * @api
     */
    public function inset($name, $flags = 0);

    /**
     * Create a text input control
     * @param string $name The view member name
     * @param integer $flags Optional {STATE_DISABLED}
     * @return \Nethgui\Renderer\WidgetInterface
     */
    public function textInput($name, $flags = 0);

    /**
     * Create a text label.
     *
     * @param string $name The view member name to generate the label contents
     * @param integer $flags Optional {STATE_DISABLED}
     * @return \Nethgui\Renderer\WidgetInterface
     * @api
     */
    public function textLabel($name, $flags = 0);

    /**
     * Create a fieldset container
     *
     * @see textLabel()
     * @param string $name OPTIONAL - The view member passed as argument for the "template" attribute.
     * @param integer $flags OPTIONAL - flags
     * @return \Nethgui\Renderer\WidgetInterface
     * @api
     */
    public function fieldset($name = NULL, $flags = 0);

    /**
     * Create a text header control
     *
     * @see textLabel()
     * @param string $name OPTIONAL - The view member passed as argument for the "template" attribute.
     * @return \Nethgui\Renderer\WidgetInterface
     * @api
     */
    public function header($name = NULL, $flags = 0);

    /**
     * Create an hidden control
     *
     * @param string $name The view member name
     * @param integer $flags Optional {STATE_DISABLED}
     * @return \Nethgui\Renderer\WidgetInterface
     * @api
     */
    public function hidden($name, $flags = 0);

    /**
     * Create a selector control
     *
     * @param string $name The view member name holding the selected value(s)
     * @param integer $flags
     * @return \Nethgui\Renderer\WidgetInterface
     * @api
     */
    public function selector($name, $flags = 0);

    /**
     * Create a button control
     * 
     * @param string $name The view member name
     * @param integer $flags Optional 
     * @return \Nethgui\Renderer\WidgetInterface
     * @api
     */
    public function button($name, $flags = 0);

    /**
     * Create a radio button control
     * 
     * @param string $name The view member name
     * @param string $value The value assigned to the control, when selected.
     * @param integer $flags Optional {STATE_DISABLED, STATE_CHECKED}
     * @return \Nethgui\Renderer\WidgetInterface
     * @api
     */
    public function radioButton($name, $value, $flags = 0);

    /**
     * Create a checkbox control
     * 
     * @param string $name The view member name
     * @param string $value The value assigned to the control, when selected.
     * @param integer $flags Optional {STATE_DISABLED, STATE_CHECKED}
     * @return \Nethgui\Renderer\WidgetInterface
     * @api
     */
    public function checkBox($name, $value, $flags = 0);

    /**
     * Create a selectable fieldset container.
     *
     * @see checkbox()
     * @param string $name
     * @param string $value
     * @param integer $flags
     * @return \Nethgui\Renderer\WidgetInterface
     * @api
     */
    public function fieldsetSwitch($name, $value, $flags = 0);


    /**
     * Create a tabs container.
     *
     * @param integer $flags {STATE_DISABLED}
     * @return \Nethgui\Renderer\WidgetInterface
     * @api
     */
    public function tabs($flags = 0);

    /**
     * Create a simple form container.
     * 
     * @param integer $flags Optional - {STATE_DISABLED}
     * @return \Nethgui\Renderer\WidgetInterface
     * @api
     */
    public function form($flags = 0);

    /**
     * Create a panel container
     *
     * @param integer $flags
     * @return \Nethgui\Renderer\WidgetInterface
     * @api
     */
    public function panel($flags = 0);

    /**
     * Create a list of elements
     *
     * Add the actual elements invoking the insert() operation of the returned object.
     *
     * @param integer $flags
     * @return \Nethgui\Renderer\WidgetInterface
     * @api
     */
    public function elementList($flags = 0);

    /**
     * Create a list of button elements
     *
     * Add the actual elements invoking the insert() operation of the returned object.
     *
     * @param integer $flags
     * @return \Nethgui\Renderer\WidgetInterface
     * @api
     */
    public function buttonList($flags = 0);


    /**
     * Create literal data - helper.
     *
     * @param string|object|\Nethgui\Core\ViewInterface $data Can be a string, any object implementing toString() method, or a View.
     * @return \Nethgui\Renderer\WidgetInterface
     * @api
     */
    public function literal($data, $flags = 0);

    /**
     * Create a column container - helper.
     *
     * Add the actual columns through the insert() operation of the returned object
     *
     * @return \Nethgui\Renderer\WidgetInterface
     * @api
     */
    public function columns();

    /**
     * Create a progress bar
     * 
     * - name View member holding the percent value Int range [0, 100]
     *
     * @see #554
     * @return \Nethgui\Renderer\WidgetInterface
     * @api
     */
    public function progressbar($name, $flags = 0);

    /**
     * Create a text area
     *
     * Attributes:
     * - dimensions
     * - appendOnly
     *
     * @see #556
     * @return \Nethgui\Renderer\WidgetInterface
     * @api
     */
    public function textArea($name, $flags = 0);


    /**
     * Create a console-like text area
     *
     * @see textArea()
     *
     * @return \Nethgui\Renderer\WidgetInterface
     * @api
     */
    public function console($name, $flags = 0);

    /**
     * Create a date picker widget:
     *
     *
     * Attributes:
     * - class (string) "Date " plus one of "be" (default), "me", "le".
     *
     * @see 474
     * @return \Nethgui\Renderer\WidgetInterface
     * @api
     */
    public function dateInput($name, $flags);


    /**
     * Pick/selects objects from a collection
     *
     * @see WidgetFactoryInterface::selector()
     * @return \Nethgui\Renderer\WidgetInterface
     * @api
     */
    public function objectPicker($name = NULL, $flags = 0);


}

define('NETHGUI_INHERITABLE_FLAGS', WidgetFactoryInterface::STATE_DISABLED | WidgetFactoryInterface::LABEL_ABOVE | WidgetFactoryInterface::LABEL_LEFT | WidgetFactoryInterface::LABEL_RIGHT | WidgetFactoryInterface::LABEL_NONE | WidgetFactoryInterface::STATE_UNOBSTRUSIVE);
