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
 * A log that sends message to the system log
 *
 * @since 1.0
 * @author Davide Principi <davide.principi@nethesis.it>
 */
class Syslog extends AbstractLog
{
    private $syslogLevels = array(
        'warning' => LOG_WARNING,
        'notice' => LOG_NOTICE,
        'error' => LOG_ERR,
        'exception' => LOG_ERR
    );

    protected function message($level, $message)
    {
        $levelValue = isset($this->syslogLevels[$level]) ? $this->syslogLevels[$level] : LOG_NOTICE;
        $xmessage = sprintf('[%s] %s', strtoupper($level), $message);
        if($levelValue <= LOG_WARNING) {
            // send errors also to SAPI error logger
            $this->phpWrapper->error_log($xmessage, 4);
        }
        $this->phpWrapper->syslog($levelValue, $xmessage);
        return $this;
    }

}