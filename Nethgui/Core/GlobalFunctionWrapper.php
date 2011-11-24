<?php
/**
 * @author Davide Principi <davide.principi@nethesis.it>
 */

namespace Nethgui\Core;

/**
 */
class GlobalFunctionWrapper
{

    public function __call($name, $arguments)
    {
        return call_user_func_array($name, $arguments);
    }

    public function exec($command, &$output, &$retval)
    {
        return exec($command, $output, $retval);
    }

    public function phpInclude($filePath, $vars)
    {
        extract($vars, EXTR_REFS);
        return include($filePath);
    }

    public function phpExit($code)
    {
        exit($code);
    }

}
