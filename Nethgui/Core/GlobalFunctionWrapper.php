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
        Nethgui_Framework::getInstance()->logMessage($name . '() Arguments: ' . print_r($arguments, 1));
        return call_user_func_array($name, $arguments);
    }
}