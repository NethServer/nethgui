<?php
/**
 * Nethgui
 *
 * @package Core
 * @subpackage Module
 */

/**
 * Describe the behaviour to be applied in the user interface
 *
 * @see Nethgui_Core_Module_Controller
 * @package Core
 * @subpackage Module
 */
interface Nethgui_Core_Module_DefaultUiStateInterface
{
    const STYLE_DIALOG = 0x1;
    const STYLE_ENABLED = 0x2;

    const STYLE_CONTAINER_TABLE = 0x4;    

    /**
     * @return int The style flags
     */
    public function getDefaultUiStyleFlags();
}
