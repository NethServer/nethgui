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
 */
abstract class AbstractLog implements \Nethgui\Core\GlobalFunctionConsumer
{

    /**
     * @var \Nethgui\Core\GlobalFunctionWrapper
     */
    protected $globalFunctionWrapper;

    public function __construct()
    {
        $this->globalFunctionWrapper = new \Nethgui\Core\GlobalFunctionWrapper();
    }

    public function exception(Exception $ex, $stackTrace = FALSE)
    {
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
        return $this->message(__FUNCTION__, $message);
    }

    public function notice($message)
    {
        return $this->message(__FUNCTION__, $message);
    }

    public function info($message)
    {
        return $this->notice($message);
    }

    public function error($message)
    {
        return $this->message(__FUNCTION__, $message);
    }

    public function warning($message)
    {
        return $this->message(__FUNCTION__, $message);
    }

    abstract public function message($level, $message);

    public function setGlobalFunctionWrapper(\Nethgui\Core\GlobalFunctionWrapper $object)
    {
        $this->globalFunctionWrapper = $object;
        return $this;
    }

}
