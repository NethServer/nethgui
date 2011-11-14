<?php
/**
 * @package Core
 * @author Davide Principi <davide.principi@nethesis.it>
 */

/**
 * @package Core
 */
class Nethgui_Core_GlobalFunctionWrapper
{

    public function __call($name, $arguments)
    {
        return call_user_func_array($name, $arguments);
    }

    public function phpInclude($filePath, $vars) {
        extract($vars, EXTR_REFS);
        return include($filePath);
    }

}