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
 * The generic log operations
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 * @api 
 */
interface LogInterface
{
    /**
     * Change log details level
     *
     * @api
     * @return LogInterface
     */
    public function setLevel($level);

    /**
     * Log details level
     *
     * It's a bitmask of E_ERROR , E_WARNING, E_NOTICE
     *
     * @api
     * @return integer
     */
    public function getLevel();

    /**
     * @api
     * @param \Exception $ex The exception to be printed
     * @param boolean $stackTrace Whether to print the stack trace or not
     * @return LogInterface
     */
    public function exception(\Exception $ex, $stackTrace = FALSE);

    /**
     * @api
     * @param string $message The message to be printed
     * @return LogInterface
     */
    public function notice($message);

    /**
     * @api
     * @param string $message The message to be printed
     * @return LogInterface
     */
    public function error($message);

    /**
     * @api
     * @param string $message The message to be printed
     * @return LogInterface
     */
    public function warning($message);
}
