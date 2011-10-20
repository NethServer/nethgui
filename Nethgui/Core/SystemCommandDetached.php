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
class Nethgui_Core_SystemCommandDetached implements Nethgui_Core_SystemCommandInterface, Nethgui_Core_GlobalFunctionConsumer
{

    private $outputFile;
    private $errorFile;
    private $state;
    private $systemCommand;
    private $processId = NULL;

    /**
     *
     * @var Nethgui_Core_GlobalFunctionWrapper
     */
    protected $globalFunctionWrapper;

    public function __construct($command, $arguments = array())
    {
        $this->systemCommand = new Nethgui_Core_SystemCommand($this->shellBackgroundInvocation($command), $arguments);
        $this->setGlobalFunctionWrapper(new Nethgui_Core_GlobalFunctionWrapper());
        $this->initialize();
    }

    public function setGlobalFunctionWrapper(Nethgui_Core_GlobalFunctionWrapper $object)
    {
        $this->globalFunctionWrapper = $object;
        $this->systemCommand->setGlobalFunctionWrapper($object);
    }

    private function initialize()
    {
        $this->state = self::STATE_NEW;
        $dir = '/tmp';
        $prefix = 'ng-';
        $this->outputFile = tempnam($dir, $prefix);
        $this->errorFile = tempnam($dir, $prefix);
    }

    public function __clone()
    {
        $this->initialize(NULL);
    }

    private function shellBackgroundInvocation($commandTemplate)
    {
        return sprintf('nohup %s >%s 2>%s & echo $!', $commandTemplate, escapeshellarg($this->outputFile), escapeshellarg($this->errorFile));
    }

    public function addArgument($arg)
    {
        $this->systemCommand->addArgument($arg);
    }

    public function exec()
    {
        if ($this->getExecutionState() != self::STATE_NEW) {
            return FALSE;
        }
        $this->systemCommand->exec();
        $this->processId = intval($this->systemCommand->getOutput());

        if ($this->processId > 0) {
            $this->setExecutionState(self::STATE_RUNNING);
        } else {
            $this->setExecutionState(self::STATE_EXITED);
        }

        return $this->getExecutionState();
    }

    private function setExecutionState($newState)
    {
        $this->state = $newState;
    }

    public function getExecutionState()
    {
        return $this->state;
    }

    /**
     * The exit status code of the command _invocation_.
     *
     * NOTE: This is not the exit status code of the detached process.
     *
     * @return integer 
     */
    public function getExitStatus()
    {
        if ($this->getExecutionState() != self::STATE_EXITED) {
            return FALSE;
        }
        $this->systemCommand->getExitStatus();
    }

    public function getOutput()
    {
        return $this->globalFunctionWrapper->file_get_contents($this->outputFile);
    }

    public function getOutputArray()
    {
        return explode(PHP_EOL, $this->getOutput());
    }

    public function kill()
    {
        if ($this->getExecutionState() == self::STATE_RUNNING) {
            $killExitCode = NULL;
            $killOutput = array();
            $this->globalFunctionWrapper->exec(sprintf('/bin/kill %d', $this->processId), &$killOutput, &$killExitCode);
            if ($killExitCode === 0) {
                $this->setExecutionState(self::STATE_EXITED);
                return TRUE;
            }
        }
        return FALSE;
    }

}