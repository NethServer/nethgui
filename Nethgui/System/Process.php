<?php

namespace Nethgui\System;

/*
 * Copyright (C) 2014 Nethesis S.r.l.
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
 * Backward compatible API to \Symfony\Component\Process\Process
 */
class Process extends \Symfony\Component\Process\Process implements ProcessInterface
{
    public $background = FALSE;
    public $log;

    public function __construct($command, $arguments = array())
    {
        parent::__construct($this->prepareEscapedCommand($command, $arguments));
        $this->identifier = md5(uniqid());
        $this->log = new \Nethgui\Log\Nullog();
    }

    public function addArgument($arg)
    {
        throw new \LogicException(sprintf("%s: %s is not supported", __CLASS__, __FUNCTION__), 1405516178);
    }

    public function dispose()
    {
        throw new \LogicException(sprintf("%s: %s is not supported", __CLASS__, __FUNCTION__), 1405516179);
    }

    private $conditions = array();

    public function on($condition, $description)
    {
        $this->conditions[$condition] = $description;
        return $this;
    }

    public function exec()
    {
        $this->log->deprecated();
        if ($this->background === TRUE) {
            // Bypass Symfony Process harness and let it go:
            $cmd = sprintf('/usr/libexec/nethserver/ptrack %s', $this->getCommandLine());
            $p = popen($cmd, 'w');
            $ui = array(
                'socketPath' => sprintf('/var/run/ptrack/%s.sock', $this->getIdentifier()),
                'conditions' => $this->conditions,
                'starttime' => microtime(TRUE),
                'taskId' => $this->getIdentifier(),
                'debug' => \NETHGUI_DEBUG
            );
            fwrite($p, json_encode($ui));
            pclose($p);

        } else {
            $this->run();
        }
        return $this;
    }

    public function getExitCode()
    {
        $code = parent::getExitCode();
        return $code === NULL ? FALSE : $code;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    private function prepareEscapedCommand($command, $arguments)
    {
        $escapedArguments = array();
        $i = 1;
        foreach ($arguments as $arg) {

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

        return strtr($command, $escapedArguments);
    }

    public function getOutput()
    {
        if ($this->isOutputDisabled()) {
            $this->log->deprecated();
            return '';
        }
        return parent::getOutput();
    }

    public function getOutputArray()
    {
        $this->log->deprecated();
        return explode("\n", trim($this->getOutput()));
    }

    public function getTimes()
    {
        throw new \LogicException(sprintf("%s: %s is not supported", __CLASS__, __FUNCTION__), 1405516180);
    }

    public function isDisposed()
    {
        throw new \LogicException(sprintf("%s: %s is not supported", __CLASS__, __FUNCTION__), 1405516181);
    }

    public function kill()
    {
        $this->log->deprecated();
        if ($this->isRunning()) {
            $this->stop();
            return TRUE;
        }
        return FALSE;
    }

    public function readExecutionState()
    {
        $this->log->deprecated();
        $s = $this->getStatus();
        if ($s === self::STATUS_READY) {
            return self::STATE_NEW;
        } elseif ($s === self::STATUS_STARTED) {
            return self::STATE_RUNNING;
        } elseif ($s === self::STATUS_TERMINATED) {
            return self::STATE_EXITED;
        }
        throw new \RuntimeException(sprintf("%s::%s() unknown execution state: %s", __CLASS__, __FUNCTION__, $s), 1405516182);
    }

    public function readOutput()
    {
        $this->log->deprecated();
        return $this->getIncrementalOutput();
    }

    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
        return $this;
    }

    public function setPhpWrapper(\Nethgui\Utility\PhpWrapper $object)
    {
        return $this;
    }

}