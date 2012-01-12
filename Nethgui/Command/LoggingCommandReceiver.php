<?php
namespace Nethgui\Command;

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
 * Log unhandled commands to syslog.
 *
 * @since 1.0
 * @internal
 */
class LoggingCommandReceiver implements \Nethgui\Command\CommandReceiverInterface
{
    private $log;

    public function __construct()
    {
        $this->log = new \Nethgui\Log\Syslog();
    }

    public function executeCommand(\Nethgui\Core\ViewInterface $origin, $selector, $name, $arguments)
    {
        $argStrings = array_map(function($arg) { return is_object($arg) ? get_class($arg) : gettype($arg); }, $arguments);
        $selectorString = $origin->getClientEventTarget($selector);
        $this->log->notice(sprintf('%s: %s#%s(%s)', get_class($this), $selectorString, $name, implode(', ', $argStrings)));
    }
}
