<?php
namespace Nethgui\System;

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
 * Internal class for exec() return value
 *
 * @see NethPlatform::exec()
 * @ignore
 */
class Process implements ProcessInterface, \Nethgui\Core\GlobalFunctionConsumer
{

    /**
     * @var array
     */
    private $output;

    /**
     * @var int
     */
    private $exitStatus;

    /**
     * @var string
     */
    private $command;

    /**
     * @var array
     */
    private $arguments;

    /**
     *
     * @var integer
     */
    private $state;
    private $outputRed;

    /**
     *
     * @var \Nethgui\Core\GlobalFunctionWrapper
     */
    private $globalFunctionWrapper;

    public function __construct($command, $arguments = array())
    {
        $this->globalFunctionWrapper = new \Nethgui\Core\GlobalFunctionWrapper();
        $this->arguments = $arguments;
        $this->command = $command;
        $this->state = self::STATE_NEW;
        $this->output = array();
        $this->exitStatus = FALSE;
        $this->outputRed = FALSE;
    }

    public function addArgument($arg)
    {
        $this->arguments[] = $arg;
    }

    public function __clone()
    {
        $this->state = self::STATE_NEW;
        $this->output = array();
        $this->exitStatus = FALSE;
        $this->outputRed = FALSE;
    }

    public function kill()
    {
        return FALSE;
    }

    public function exec()
    {
        if ($this->readExecutionState() !== self::STATE_NEW) {
            return FALSE;
        }

        $this->globalFunctionWrapper->exec($this->prepareEscapedCommand(), $this->output, $this->exitStatus);        
        $this->changeState(self::STATE_EXITED);
        return $this->readExecutionState();
    }

    private function changeState($newState)
    {
        $this->state = $newState;
    }

    private function prepareEscapedCommand()
    {
        $escapedArguments = array();
        $i = 1;
        foreach ($this->arguments as $arg) {
            $escapedArguments[sprintf('${%d}', $i)] = escapeshellarg($arg);
            $i ++;
        }
        $escapedArguments['${@}'] = implode(' ', array_map('escapeshellarg', $this->arguments));

        return strtr($this->command, $escapedArguments);
    }

    public function getExitStatus()
    {
        return $this->exitStatus;
    }

    public function getOutput()
    {
        return implode("\n", $this->output);
    }

    public function getOutputArray()
    {
        return $this->output;
    }

    public function readExecutionState()
    {
        return $this->state;
    }

    public function setGlobalFunctionWrapper(\Nethgui\Core\GlobalFunctionWrapper $object)
    {
        $this->globalFunctionWrapper = $object;
    }

    public function readOutput()
    {
        if ($this->outputRed === FALSE) {
            $this->outputRed = TRUE;
            return $this->getOutput();
        }

        return FALSE;
    }

}
