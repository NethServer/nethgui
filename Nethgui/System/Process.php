<?php
/**
 * @package System
 * @ignore
 */

/**
 * Internal class for exec() return value
 *
 * @see Nethgui_System_NethPlatform::exec()
 * @ignore
 * @package System
 */
class Nethgui_System_Process implements Nethgui_System_ProcessInterface, Nethgui_Core_GlobalFunctionConsumer
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

    /**
     *
     * @var integer
     */
    private $state;
    private $outputRed;

    /**
     *
     * @var Nethgui_Core_GlobalFunctionWrapper
     */
    private $globalFunctionWrapper;

    public function __construct($command, $arguments = array())
    {
        $this->globalFunctionWrapper = new Nethgui_Core_GlobalFunctionWrapper();
        $this->arguments = $arguments;
        $this->command = $command;
        $this->state = self::STATE_NEW;
        $this->output = array();
        $this->exitStatus = FALSE;
        $this->outputRed = FALSE;
    }

    public function addArgument($arg)
    {
        $this->arguments[] = $arg;
    }

    public function __clone()
    {
        $this->state = self::STATE_NEW;
        $this->output = array();
        $this->exitStatus = FALSE;
        $this->outputRed = FALSE;
    }

    public function kill()
    {
        return FALSE;
    }

    public function exec()
    {
        if ($this->readExecutionState() !== self::STATE_NEW) {
            return FALSE;
        }

        $output = &$this->output;
        $exitStatus = &$this->exitStatus;

        $this->globalFunctionWrapper->exec($this->prepareEscapedCommand(), $output, $exitStatus);
        $this->changeState(self::STATE_EXITED);
        return $this->readExecutionState();
    }

    private function changeState($newState)
    {
        $this->state = $newState;
    }

    private function prepareEscapedCommand()
    {
        $escapedArguments = array();
        $i = 1;
        foreach ($this->arguments as $arg) {
            $escapedArguments[sprintf('${%d}', $i)] = escapeshellarg($arg);
            $i ++;
        }
        $escapedArguments['${@}'] = implode(' ', array_map('escapeshellarg', $this->arguments));

        return strtr($this->command, $escapedArguments);
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

    public function readExecutionState()
    {
        return $this->state;
    }

    public function setGlobalFunctionWrapper(Nethgui_Core_GlobalFunctionWrapper $object)
    {
        $this->globalFunctionWrapper = $object;
    }

    public function readOutput()
    {
        if ($this->outputRed === FALSE) {            
            $this->outputRed = TRUE;
            return $this->getOutput();
        } 
        
        return FALSE;
    }

}