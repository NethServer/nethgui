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
class ProcessDetached implements ProcessInterface, \Nethgui\Utility\PhpConsumerInterface, \Serializable
{
    /**
     * @var string
     */
    private $outputFile;

    /**
     * @var string
     */
    private $errorFile;

    /**
     * @var integer
     */
    private $state;

    /**
     * @var integer
     */
    private $processId;

    /**
     *
     * @var boolean|integer
     */
    private $exitCode;
    private $outputPosition;

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
     * @var array
     */
    private $arguments = array();

    /**
     *
     * @var string
     */
    private $command;

    /**
     *
     * @var \Nethgui\Utility\PhpWrapper
     */
    protected $phpWrapper;

    public function __construct($command, $arguments = array())
    {
        $this->setPhpWrapper(new \Nethgui\Utility\PhpWrapper());
        $this->setExecutionState(self::STATE_NEW);
        $dir = '/tmp';
        $prefix = 'ng-x-';
        $this->outputFile = tempnam($dir, $prefix);
        $this->errorFile = tempnam($dir, $prefix);
        $this->outputPosition = 0;
        $this->arguments = $arguments;
        $this->command = $command;
        $this->identifier = uniqid();
    }

    public function setPhpWrapper(\Nethgui\Utility\PhpWrapper $object)
    {
        $this->phpWrapper = $object;
        return $this;
    }

    private function updateStatus()
    {
        if ( ! isset($this->processId)) {
            return;
        }

        $isRunning = $this->phpWrapper->is_readable(sprintf('/proc/%d', $this->processId));

        if ($this->state === self::STATE_NEW && $isRunning) {
            $this->setExecutionState(self::STATE_RUNNING);
        } elseif ($this->state === self::STATE_RUNNING && ! $isRunning) {
            $this->setExecutionState(self::STATE_EXITED);
        } elseif ($this->state === self::STATE_EXITED && $isRunning) {
            throw new \UnexpectedValueException(sprintf('%s: inconsistent process object state', __CLASS__), 1328109246);
        }
    }

    private function shellBackgroundInvocation($commandTemplate)
    {
        return sprintf('/bin/env PTRACK_SOCKETPATH=/var/run/ptrack/%s.sock /usr/bin/setsid /usr/libexec/nethserver/ptrack %s >%s 2>%s & echo $!', $this->getIdentifier(), $commandTemplate, escapeshellarg($this->outputFile), escapeshellarg($this->errorFile));
    }

    public function addArgument($arg)
    {
        $this->arguments[] = $arg;
        return $this;
    }

    public function exec()
    {
        if ($this->readExecutionState() !== self::STATE_NEW) {
            throw new \UnexpectedValueException(sprintf('%s: cannot invoke exec() on a running or exited process', __CLASS__), 1326103905);
        }

        $innerCommand = new \Nethgui\System\Process($this->shellBackgroundInvocation($this->command), $this->arguments);
        $innerCommand->setPhpWrapper($this->phpWrapper)->exec();
        $this->processId = intval($innerCommand->getOutput());

        if ($this->processId > 0) {
            $this->setExecutionState(self::STATE_RUNNING);
        } else {
            $this->setExecutionState(self::STATE_EXITED);
        }

        return $this;
    }

    private function setExecutionState($newState)
    {
        $this->times[$newState] = $this->phpWrapper->microtime(TRUE);
        $this->state = $newState;
        if ($newState === self::STATE_EXITED) {
            $this->exitCode = $this->processId > 0 ? 0 : 1;
        }
    }

    public function readExecutionState()
    {
        $this->updateStatus();
        return $this->state;
    }

    /**
     * The exit status code of the command _invocation_.
     *
     * NOTE: This is not the exit status code of the detached process.
     *
     * @return integer|boolean FALSE if the command has not exited yet.
     */
    public function getExitCode()
    {
        if ($this->readExecutionState() === self::STATE_EXITED) {
            return $this->exitCode;
        }
        return FALSE;
    }

    public function getOutput()
    {
        return $this->phpWrapper->file_get_contents($this->outputFile);
    }

    public function getOutputArray()
    {
        return explode(PHP_EOL, $this->getOutput());
    }

    public function kill()
    {
        if ($this->readExecutionState() === self::STATE_RUNNING) {
            $killExitCode = NULL;
            $killOutput = array();
            $this->phpWrapper->exec(sprintf('/bin/kill %d', $this->processId), $killOutput, $killExitCode);
            if ($killExitCode === 0) {
                $this->setExecutionState(self::STATE_EXITED);
                return TRUE;
            }
        }
        return FALSE;
    }

    public function serialize()
    {
        $this->expiredCheck();

        $ostate = array(
            $this->errorFile,
            $this->outputFile,
            $this->state,
            $this->processId,
            $this->exitCode,
            $this->phpWrapper,
            $this->outputPosition,
            $this->times,
            $this->identifier,
            $this->disposed,
        );

        return serialize($ostate);
    }

    /**
     * Dispose the object automatically after one minute in EXITED states.
     * 
     * @return void
     */
    private function expiredCheck()
    {
        if ( ! isset($this->times[self::STATE_EXITED])) {
            return;
        }

        if ($this->phpWrapper->microtime(TRUE) - floatval($this->times[self::STATE_EXITED]) > 60.0) {
            $this->dispose();
        }
    }

    public function unserialize($serialized)
    {
        $ostate = unserialize($serialized);

        list(
            $this->errorFile,
            $this->outputFile,
            $this->state,
            $this->processId,
            $this->exitCode,
            $this->phpWrapper,
            $this->outputPosition,
            $this->times,
            $this->identifier,
            $this->disposed,
            ) = $ostate;

        $this->updateStatus();

        return $this;
    }

    public function readOutput()
    {
        $currentOutput = (String) $this->getOutput();
        $nextPos = strlen($currentOutput);

        if ($nextPos > 0) {
            $buffer = substr($currentOutput, $this->outputPosition);
        } else {
            $buffer = '';
        }

        $this->outputPosition = $nextPos;

        return $buffer;
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

    public function dispose()
    {
        $this->disposed = TRUE;
        return $this;
    }

    public function isDisposed()
    {
        return $this->disposed === TRUE;
    }

    public function __destruct()
    {
        if ( ! $this->isDisposed()) {
            return;
        }

        $this->phpWrapper->unlink($this->errorFile);
        $this->phpWrapper->unlink($this->outputFile);
    }

}