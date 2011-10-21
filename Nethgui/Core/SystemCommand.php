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
class Nethgui_Core_SystemCommand implements Nethgui_Core_SystemCommandInterface, Nethgui_Core_GlobalFunctionConsumer
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
        $this->globalFunctionWrapper->exec($this->prepareEscapedCommand(), &$this->output, &$this->exitStatus);
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

}