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
 * @see exec()
 */
interface ProcessInterface
{

    const STATE_NEW = 0;
    const STATE_RUNNING = 1;
    const STATE_EXITED = 2;

    /**
     * The command output
     * @return string
     */
    public function getOutput();

    /**
     * The lines of the command output
     * @return array
     */
    public function getOutputArray();


    /**
     * @return string|bool An output chunk, if more data is available, FALSE otherwise.
     */
    public function readOutput();

    /**
     * The exit status code
     * @return int
     */
    public function getExitStatus();

    /**
     * @param string
     */
    public function addArgument($arg);

    /**
     * Execute the command
     * @return the execution status
     * @see getExecStatus
     */
    public function exec();

    /**
     * Kills a RUNNING command
     *
     * @return FALSE on error, TRUE if the command was RUNNING
     */
    public function kill();

    /**
     * Read and returns the execution state, one of NEW, RUNNING, EXITED
     * @return integer
     */
    public function readExecutionState();

    /**
     * Give an identity to the process object to retrieve it later.
     *
     * @param string Unique identifier of the process when stored in a hash table
     * @return ProcessInterface
     */
    public function setIdentifier($identifier);

    /**
     * Obtain the process identifier.
     *
     * If the identifier has not been set this method returns a random string
     *
     * @return string The process unique identifier
     */
    public function getIdentifier();

    /**
     * @return array Timing informations
     */
    public function getTimes();
}


