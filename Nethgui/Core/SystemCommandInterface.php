<?php
/**
 * Nethgui
 *
 * @package Core
 */

/**
 * Brings the output and exit status of an external command
 *
 * @see exec()
 * @package Core
 */
interface Nethgui_Core_SystemCommandInterface
{
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
     * @return bool FALSE
     */
    public function exec();

    /**
     * @see exec();
     * @return bool TRUE if the command has been executed
     */
    public function isExecuted();

}
