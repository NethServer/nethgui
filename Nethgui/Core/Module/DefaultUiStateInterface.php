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
 * @see Nethgui\Core\Module\Controller
 * @package Core
 * @subpackage Module
 */
interface Nethgui\Core\Module\DefaultUiStateInterface
{
    const STYLE_DIALOG = 0x1;
    const STYLE_ENABLED = 0x2;

    const STYLE_CONTAINER_TABLE = 0x4;
    const STYLE_CONTAINER_TABS = 0x08;
    const STYLE_NOFORMWRAP = 0x10;

    /**
     * @return int The style flags
     */
    public function getDefaultUiStyleFlags();
}
