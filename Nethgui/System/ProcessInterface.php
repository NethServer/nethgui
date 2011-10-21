<?php
/**
 * Nethgui
 *
 * @package System
 */

/**
 * Brings the output and exit status of an external command
 *
 * @see exec()
 * @package System
 */
interface Nethgui_System_ProcessInterface
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
}


