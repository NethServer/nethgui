<?php

namespace Nethgui\Model;

/*
 * Copyright (C) 2014  Nethesis S.r.l.
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
 * Access to status informations of background running tasks
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.6
 */
class SystemTasks
{
    const PTRACK_PATH_TEMPLATE = '/var/run/ptrack/%s.sock';
    const PTRACK_DUMP_PATH = '/var/spool/ptrack/%.16s.dump';
    const TY_DECLARE = 0x01;
    const TY_DONE = 0x02;
    const TY_QUERY = 0x03;
    const TY_PROGRESS = 0x04;
    const TY_ERROR = 0x40;
    const TY_RESPONSE = 0x80;

    /**
     *
     * @var \Nethgui\Utility\PhpWrapper
     */
    protected $phpWrapper;

    /**
     *
     * @var \Nethgui\Log\LogInterface
     */
    protected $log;
    private $tasks = array();

    public function __construct(\Nethgui\Log\LogInterface $log)
    {
        $this->log = $log;
        $this->phpWrapper = new \Nethgui\Utility\PhpWrapper(__CLASS__);
    }

    public function getRunningTasks()
    {
        $running = array();
        $pattern = '|' . sprintf(preg_quote(self::PTRACK_PATH_TEMPLATE, '|'), "(?P<taskId>[^.]+)") . '|';
        foreach ($this->phpWrapper->glob(sprintf(self::PTRACK_PATH_TEMPLATE, '*')) as $socketPath) {
            $matches = array();
            if (preg_match($pattern, $socketPath, $matches) && isset($matches['taskId'])) {
                try {
                    $task = $this->getTaskStatus($matches['taskId']);
                } catch (\RuntimeException $ex) {
                    $this->log->exception($ex);
                    $this->phpWrapper->unlink($socketPath);
                    continue;
                }
                $running[$matches['taskId']] = $task;
            }
        }
        return $running;
    }

    public function getStartingTasks()
    {
        return array_filter($this->tasks, function ($t) {
            return isset($t['starting']);
        });
    }

    /**
     *
     * @param string $taskId
     * @return array
     * @throws \RuntimeException
     */
    public function getTaskStatus($taskId)
    {
        if ( ! isset($this->tasks[$taskId])) {
            $this->tasks[$taskId] = $this->fetchTaskStatus($taskId);
        }
        return $this->tasks[$taskId];
    }

    public function setTaskStarting($taskId)
    {
        if (isset($this->tasks[$taskId])) {
            throw new \LogicException(sprintf("%s: the taskId is already registered", __CLASS__), 1405928979);
        }
        $this->tasks[$taskId] = array(
            'starting' => $taskId,
        );
        return $this;
    }

    private function fetchTaskStatus($taskId)
    {
        $socketPath = sprintf(self::PTRACK_PATH_TEMPLATE, $taskId);
        $dumpPath = sprintf(self::PTRACK_DUMP_PATH, md5($socketPath));

        $taskStatus = FALSE;
        $errno = 0;
        $errstr = "";

        $socket = $this->phpWrapper->fsockopen('unix://' . $socketPath, -1, $errno, $errstr);

        if ($socket === FALSE) {
            $socketPathExists = $errno != 2;
            if ($socketPathExists) {
                $this->log->error(sprintf('%s: Socket %s exists, but open failed: errno %d, errstr %s', __CLASS__, $socketPath, $errno, $errstr));
            }
            $taskStatus = $this->fetchDumpFile($dumpPath);
        } else {
            $this->sendMessage($socket, self::TY_QUERY);
            $taskStatus = $this->recvMessage($socket);
            $this->phpWrapper->fclose($socket);
        }

        return $taskStatus;
    }

    private function fetchDumpFile($dumpPath)
    {
        if ( ! $this->phpWrapper->file_exists($dumpPath)) {
            throw new \RuntimeException(sprintf("%s: could not open dump file %s", __CLASS__, $dumpPath), 1405613538);
        }

        $tmp = json_decode($this->phpWrapper->file_get_contents($dumpPath), TRUE);
        if ( ! is_array($tmp)) {
            throw new \RuntimeException(sprintf("%s: dump file decode error", __CLASS__), 1405613539);
        }

        return $tmp;
    }

    private function sendMessage($socket, $type, $args = array())
    {
        $payload = json_encode($args);
        $data = pack('Cn', (int) $type, strlen($payload)) . $payload;
        $written = $this->phpWrapper->fwrite($socket, $data);
        if ($written !== strlen($data)) {
            throw new \RuntimeException(sprintf('%s: Socket write error', __CLASS__), 1405610071);
        }
    }

    private function recvMessage($socket)
    {
        $buf = $this->safeRead($socket, 3);
        if ($buf === FALSE) {
            throw new \RuntimeException(sprintf('%s: Socket read error', __CLASS__), 1405610072);
        }

        $header = unpack('Ctype/nsize', $buf);
        if ( ! is_array($header)) {
            throw new \RuntimeException(sprintf('%s: Socket read error', __CLASS__), 1405610073);
        }

        $message = NULL;
        if ($header['type'] & self::TY_RESPONSE) {
            $message = $this->safeRead($socket, $header['size']);
            if ($message === FALSE) {
                throw new \RuntimeException(sprintf('%s: Socket read error', __CLASS__), 1405610074);
            }
        }
        return json_decode($message, TRUE);
    }

    private function safeRead($socket, $size)
    {
        $buffer = "";
        $count = 0;
        while ($count < $size) {
            if (feof($socket)) {
                return FALSE;
            }
            $chunk = $this->phpWrapper->fread($socket, $size - $count);
            $count += strlen($chunk);
            if ($chunk === FALSE) {
                return FALSE;
            }
            $buffer .= $chunk;
        }
        return $buffer;
    }

}