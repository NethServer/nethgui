<?php
namespace Nethgui\Core;

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
 * Wraps global PHP functions and variables.
 *
 * Refs #95
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 * @api
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

    public function phpCreateInstance($className, $constructorArgs)
    {
        $r = new \ReflectionClass($className);
        return $r->newInstanceArgs($constructorArgs);
    }

    public function phpReadGlobalVariable($varName, $varIndex = NULL)
    {
        if (isset($varIndex)) {
            return isset($GLOBALS[$varName], $GLOBALS[$varName][$varIndex]) ? $GLOBALS[$varName][$varIndex] : NULL;
        }

        return isset($GLOBALS[$varName]) ? $GLOBALS[$varName] : NULL;
    }

    public function phpWriteGlobalVariable($value, $varName, $varIndex = NULL)
    {
        if (isset($varIndex)) {
            $GLOBALS[$varName][$varIndex] = $value;
        } else {
            $GLOBALS[$varName] = $value;
        }
    }

    public function file_get_contents_extended($filePath, &$meta = NULL)
    {
        ob_start();
        readfile($filePath);
        if (is_array($meta)) {
            $meta['size'] = ob_get_length();
        }
        return ob_get_clean();
    }

}