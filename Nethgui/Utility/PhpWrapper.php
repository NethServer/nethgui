<?php
namespace Nethgui\Utility;

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
class PhpWrapper implements \Nethgui\Log\LogConsumerInterface
{
    /**
     *
     * @var \Nethgui\Log\LogInterface
     */
    private $log;

    /**
     *
     * @var string
     */
    private $identifier;

    public function __construct($identifier = __CLASS__)
    {
        $this->identifier = $identifier;
    }

    public function class_exists($className)
    {
        $warnings = array();
        $this->wrapBegin($warnings, E_NOTICE);
        $retval = class_exists($className);
        $this->wrapEnd($warnings);
        return $retval;
    }

    public function __call($name, $arguments)
    {
        $warnings = array();
        $this->wrapBegin($warnings);
        $exitCode = call_user_func_array($name, $arguments);
        $this->wrapEnd($warnings);
        return $exitCode;
    }

    protected function wrapBegin(&$messages, $forceErrno = 0)
    {
        set_error_handler(function ($errno, $errstr) use (&$messages, $forceErrno) {
                $messages[] = array($forceErrno > 0 ? $forceErrno : $errno, $errstr);
            }, E_WARNING | E_NOTICE);
    }

    protected function wrapEnd($messages)
    {
        restore_error_handler();

        $log = $this->getLog();
        if ($log instanceof \Nethgui\Log\Nullog) {
            return;
        }
        
        if (count($messages) > 0) {
            $message = '';
            foreach ($messages as $msg) {
                if ($msg[0] & E_WARNING) {
                    $level = 'warning';
                } else {
                    $level = 'notice';
                    // skip notice messages, if NETHGUI_DEBUG is TRUE
                    if(NETHGUI_DEBUG === FALSE) {
                        continue;
                    }
                }
                $log->$level(sprintf("%s: %s", $this->identifier, $msg[1]));
            }
        }
    }

    public function exec($command, &$output, &$retval)
    {
        $warnings = array();
        $this->wrapBegin($warnings);
        $lastLine = exec($command, $output, $retval);
        $this->wrapEnd($warnings);
        return $lastLine;
    }

    public function fsockopen($host, $port=-1, &$errno=NULL, &$errstr=NULL)
    {
        $warnings = array();
        $this->wrapBegin($warnings);
        $resource = fsockopen($host, $port, $errno, $errstr);
        $this->wrapEnd($warnings);
        return $resource;
    }

    public function phpInclude($filePath, $vars)
    {
        extract($vars, EXTR_REFS);
        $_nethgui_warnings = array();
        $this->wrapBegin($_nethgui_warnings);
        $include = include($filePath);
        $this->wrapEnd($_nethgui_warnings);
        return $include;
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

    public function getLog()
    {
        if ( ! isset($this->log)) {
            $this->log = new \Nethgui\Log\Nullog();
        }
        return $this->log;
    }

    public function setLog(\Nethgui\Log\LogInterface $log)
    {
        $this->log = $log;
        return $this;
    }

}
