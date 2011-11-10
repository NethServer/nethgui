<?php
/**
 * @package Renderer
 * @author Davide Principi <davide.principi@nethesis.it>
 */

/**
 * Transform a view into a string.
 *
 * @see Nethgui_Renderer_WidgetInterface
 * @see http://en.wikipedia.org/wiki/Decorator_pattern
 * @package Renderer
 */
abstract class Nethgui_Renderer_Abstract extends Nethgui_Core_ReadonlyView
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

    abstract protected function render();

    public function __toString()
    {
        return $this->render();
    }

}

define('NETHGUI_INHERITABLE_FLAGS', Nethgui_Renderer_Abstract::STATE_DISABLED | Nethgui_Renderer_Abstract::LABEL_ABOVE | Nethgui_Renderer_Abstract::LABEL_LEFT | Nethgui_Renderer_Abstract::LABEL_RIGHT | Nethgui_Renderer_Abstract::LABEL_NONE);
