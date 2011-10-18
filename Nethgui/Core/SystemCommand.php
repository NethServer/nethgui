<?php
/**
 * @package Core
 * @ignore
 */

/**
 * Internal class for exec() return value
 *
 * @see Nethgui_Core_HostConfiguration::exec()
 * @ignore
 * @package Core
 */
class Nethgui_Core_SystemCommand implements Nethgui_Core_SystemCommandInterface
{

    /**
     * @var array
     */
    private $output;

    /**
     * @var int
     */
    private $exitStatus;

    /**
     * @var string
     */
    private $command;

    /**
     * @var array
     */
    private $arguments;

    public function __construct($command)
    {
        $this->arguments = array();
        $this->command = $command;

        $this->initialize();
    }

    private function initialize()
    {
        $this->output = array();
        $this->exitStatus = PHP_INT_MAX;
        $this->executed = FALSE;
    }

    public function addArgument($arg)
    {
        $this->arguments[] = $arg;
    }

    public function isExecuted()
    {
        return $this->executed === TRUE;
    }

    public function exec()
    {
        if ($this->isExecuted()) {
            return FALSE;
        }

        $escapedArguments = array();
        $i = 1;
        foreach ($this->arguments as $arg) {
            $escapedArguments[sprintf('${%d}', $i)] = escapeshellarg($arg);
            $i ++;
        }
        $escapedArguments['${@}'] = implode(' ', array_map('escapeshellarg', $this->arguments));

        $escapedCommand = strtr($this->command, $escapedArguments);

        $this->executed = TRUE;

        exec($escapedCommand, $this->output, $this->exitStatus);

        $this->exitStatus === 0;
    }

    public function __clone()
    {
        $this->initialize();
    }

    public function getCommand()
    {
        return $this->command;
    }

    public function getExitStatus()
    {
        return $this->exitStatus;
    }

    public function getOutput()
    {
        return implode("\n", $this->output);
    }

    public function getOutputArray()
    {
        return $this->output;
    }

}