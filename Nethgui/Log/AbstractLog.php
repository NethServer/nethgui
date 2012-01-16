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
 */
abstract class AbstractLog implements LogInterface, \Nethgui\Utility\PhpConsumerInterface
{

    /**
     * Implementors must send the given message and level strings to the log facility
     *
     * @param string $level
     * @param string $message
     * @return void
     */
    abstract public function message($level, $message);

    /**
     * @var \Nethgui\Utility\PhpWrapper
     */
    protected $phpWrapper;
    private $level;

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
    }

    public function exception(\Exception $ex, $stackTrace = FALSE)
    {
        if ( ! $this->level & E_ERROR) {
            return;
        }

        $message = sprintf('%s : file %s; line %d', $ex->getMessage(), $ex->getFile(), $ex->getLine());
        $retval = $this->message(__FUNCTION__, $message);

        if ($stackTrace) {
            foreach (explode("\n", $ex->getTraceAsString()) as $line) {
                $this->message(__FUNCTION__, $line);
            }
        }

        return $retval;
    }

    public function debug($message)
    {
        if ( ! $this->level & E_NOTICE) {
            return;
        }

        return $this->message(__FUNCTION__, $message);
    }

    public function notice($message)
    {
        if ( ! $this->level & E_NOTICE) {
            return;
        }

        return $this->message(__FUNCTION__, $message);
    }

    public function info($message)
    {
        if ( ! $this->level & E_NOTICE) {
            return;
        }

        return $this->notice($message);
    }

    public function error($message)
    {
        if ( ! $this->level & E_ERROR) {
            return;
        }

        return $this->message(__FUNCTION__, $message);
    }

    public function warning($message)
    {
        if ( ! $this->level & E_WARNING) {
            return;
        }

        return $this->message(__FUNCTION__, $message);
    }

    public function setPhpWrapper(\Nethgui\Utility\PhpWrapper $object)
    {
        $this->phpWrapper = $object;
        return $this;
    }

}
