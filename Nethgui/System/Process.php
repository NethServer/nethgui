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
 */
class Process implements ProcessInterface, \Nethgui\Utility\PhpConsumerInterface
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
     * @var string
     */
    private $identifier;

    /**
     *
     * @var array
     */
    private $times;
    private $disposed = FALSE;

    /**
     *
     * @var \Nethgui\Utility\PhpWrapper
     */
    private $phpWrapper;

    public function __construct($command, $arguments = array())
    {
        $this->phpWrapper = new \Nethgui\Utility\PhpWrapper();
        $this->arguments = $arguments;
        $this->command = $command;
        $this->changeState(self::STATE_NEW);
        $this->output = array();
        $this->exitStatus = FALSE;
        $this->outputRed = FALSE;
        $this->identifier = uniqid();
        $this->times = array();
    }

    public function addArgument($arg)
    {
        $this->arguments[] = $arg;
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

        $this->changeState(self::STATE_RUNNING);
        $this->phpWrapper->exec($this->prepareEscapedCommand(), $this->output, $this->exitStatus);
        $this->changeState(self::STATE_EXITED);
        return $this;
    }

    private function changeState($newState)
    {
        $this->times[$newState] = microtime();
        $this->state = $newState;
    }

    private function prepareEscapedCommand()
    {
        $escapedArguments = array();
        $i = 1;
        foreach ($this->arguments as $arg) {

            if (is_string($arg)) {
                $argOutput = $arg;
            } elseif (is_callable($arg)) {
                $argOutput = call_user_func($arg);
            } else {
                $argOutput = strval($arg);
            }

            $escapedArguments[sprintf('${%d}', $i)] = escapeshellarg($argOutput);
            $i ++;
        }
        $escapedArguments['${@}'] = implode(' ', $escapedArguments);

        return strtr($this->command, $escapedArguments);
    }

    public function getExitCode()
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

    public function setPhpWrapper(\Nethgui\Utility\PhpWrapper $object)
    {
        $this->phpWrapper = $object;
        return $this;
    }

    public function readOutput()
    {
        if ($this->outputRed === FALSE) {
            $this->outputRed = TRUE;
            return $this->getOutput();
        }

        return FALSE;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function getTimes()
    {
        return $this->times;
    }

    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
        return $this;
    }

    public function isDisposed()
    {
        return $this->disposed === TRUE;
    }

    public function dispose()
    {
        $this->disposed = TRUE;
        return $this;
    }
}
