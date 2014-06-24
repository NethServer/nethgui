<?php
namespace Nethgui\Log;

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
 * Provides message formatting capabilities without specify the log message destination
 *
 * @api
 * @since 1.0
 * @author Davide Principi <davide.principi@nethesis.it>
 */
abstract class AbstractLog implements LogInterface, \Nethgui\Utility\PhpConsumerInterface
{

    /**
     * Implementors must send the given message and level strings to the log facility
     *
     * @api
     * @param string $level
     * @param string $message
     * @return AbstractLog
     */
    abstract protected function message($level, $message);
    /**
     * @var \Nethgui\Utility\PhpWrapper
     */
    protected $phpWrapper;
    private $level;
    protected static $emitted = array();

    /**
     * @see setLevel()
     * @param integer $level
     */
    public function __construct($level = E_ALL)
    {
        $this->level = $level;
        $this->phpWrapper = new \Nethgui\Utility\PhpWrapper();
    }

    public function getLevel()
    {
        return $this->level;
    }

    public function setLevel($level)
    {
        $this->level = $level;
        return $this;
    }

    public function exception(\Exception $ex, $stackTrace = FALSE)
    {
        if (($this->level & E_ERROR) === 0) {
            return $this;
        }

        $message = sprintf('%s: %s (in %s:%d)', get_class($ex),
            $ex->getMessage(), $ex->getFile(), $ex->getLine());
        $this->message(__FUNCTION__, $message);

        if ($stackTrace) {
            foreach (explode("\n", $ex->getTraceAsString()) as $line) {
                $this->message(__FUNCTION__, $line);
            }
        }

        return $this;
    }

    public function notice($message)
    {
        if (($this->level & E_NOTICE) === 0) {
            return $this;
        }

        $this->message(__FUNCTION__, $message);
        return $this;
    }

    public function error($message)
    {
        if (($this->level & E_ERROR) === 0) {
            return $this;
        }

        $this->message(__FUNCTION__, $message);
        return $this;
    }

    public function warning($message)
    {
        if (($this->level & E_WARNING) === 0) {
            return $this;
        }

        $this->message(__FUNCTION__, $message);
        return $this;
    }

    public function deprecated($message = "%s: method %s is DEPRECATED!")
    {
        if( ! NETHGUI_DEBUG) {
            return $this;
        }
        $backtrace = debug_backtrace();
        $caller = $backtrace[2];
        $callee = $backtrace[1];
        $calleeInfo = $callee['class'] . '::' . $callee['function'] . '()';
        $callerInfo = $caller['class'] . '::' . $caller['function'] . '()';
        if ( ! isset(static::$emitted[$callerInfo])) {
            $this->warning(sprintf($message, $callerInfo, $calleeInfo));
            static::$emitted[$callerInfo] = TRUE;
        }
        return $this;
    }

    public function setPhpWrapper(\Nethgui\Utility\PhpWrapper $object)
    {
        $this->phpWrapper = $object;
        return $this;
    }

}