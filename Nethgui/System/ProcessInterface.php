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
 * Brings the output and exit status of an external command
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @see \Nethgui\System\PlatformInterface::exec()
 * @since 1.0
 * @api 
 */
interface ProcessInterface extends \Nethgui\Core\DisposableInterface
{
    const STATE_NEW = 0;
    const STATE_RUNNING = 1;
    const STATE_EXITED = 2;

    /**
     * The command output
     *
     * @api
     * @return string
     */
    public function getOutput();

    /**
     * The lines of the command output
     *
     * @api
     * @return array
     */
    public function getOutputArray();

    /**
     * Peek the running process output
     *
     * @api
     * @return string|bool An output chunk, if more data is available, FALSE otherwise.
     */
    public function readOutput();

    /**
     * The exit status code
     *
     * @api
     * @return int
     */
    public function getExitCode();

    /**
     *
     * @api
     * @param string
     */
    public function addArgument($arg);

    /**
     * Execute the command
     *
     * @api
     * @return ProcessInterface
     */
    public function exec();

    /**
     * Kills a RUNNING command
     *
     * @api
     * @return FALSE on error, TRUE if the command was RUNNING
     */
    public function kill();

    /**
     * Read and returns the execution state, one of NEW, RUNNING, EXITED
     *
     * @api
     * @return integer
     */
    public function readExecutionState();

    /**
     * Give an identity to the process object to retrieve it later.
     *
     * @api
     * @param string Unique identifier of the process 
     * @return ProcessInterface
     */
    public function setIdentifier($identifier);

    /**
     * Obtain the process identifier.
     *
     * If the identifier has not been set this method returns a random string
     *
     * @api
     * @return string The process unique identifier
     */
    public function getIdentifier();

    /**
     *
     * @api
     * @return array Timing informations
     */
    public function getTimes();

}

