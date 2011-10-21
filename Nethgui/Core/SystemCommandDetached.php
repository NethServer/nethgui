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

    /**
     * @var string
     */
    private $outputFile;

    /**
     * @var string
     */
    private $errorFile;

    /**
     * @var integer
     */
    private $state;

    /**
     *
     * @var Nethgui_Core_SystemCommand
     */
    private $innerCommand;

    /**
     * @var integer
     */
    private $processId;

    /**
     *
     * @var boolean|integer
     */
    private $exitStatus;

    /**
     *
     * @var Nethgui_Core_GlobalFunctionWrapper
     */
    protected $globalFunctionWrapper;

    public function __construct($command, $arguments = array())
    {
        $this->innerCommand = new Nethgui_Core_SystemCommand($this->shellBackgroundInvocation($command), $arguments);
        $this->setGlobalFunctionWrapper(new Nethgui_Core_GlobalFunctionWrapper());
        $this->initialize();
    }

    public function setGlobalFunctionWrapper(Nethgui_Core_GlobalFunctionWrapper $object)
    {
        $this->globalFunctionWrapper = $object;
        $this->innerCommand->setGlobalFunctionWrapper($object);
    }

    private function initialize()
    {
        $this->setExecutionState(self::STATE_NEW);
        $dir = '/tmp';
        $prefix = 'ng-';
        $this->outputFile = tempnam($dir, $prefix);
        $this->errorFile = tempnam($dir, $prefix);
    }

    public function __clone()
    {
        $this->initialize();
    }

    private function shellBackgroundInvocation($commandTemplate)
    {
        return sprintf('nohup %s >%s 2>%s & echo $!', $commandTemplate, escapeshellarg($this->outputFile), escapeshellarg($this->errorFile));
    }

    public function addArgument($arg)
    {
        $this->innerCommand->addArgument($arg);
    }

    public function exec()
    {
        if ($this->getExecutionState() != self::STATE_NEW) {
            return FALSE;
        }

        $this->innerCommand->exec();
        $this->processId = intval($this->innerCommand->getOutput());

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
        if ($newState === self::STATE_EXITED) {
            $this->exitStatus = $this->processId > 0 ? 0 : 1;
        }
    }

    public function getExecutionState()
    {
        // An undetermined state or a running state are polled at each request
        if (is_null($this->state) || $this->state === self::STATE_RUNNING)
        {
            $this->pollProcessState();
        }

        return $this->state;
    }

    private function pollProcessState()
    {
        if ($this->globalFunctionWrapper->is_readable(sprintf('/proc/%d', $this->processId))) {
            $this->setExecutionState(self::STATE_RUNNING);
        } else {
            $this->setExecutionState(self::STATE_EXITED);
        }
    }

    /**
     * The exit status code of the command _invocation_.
     *
     * NOTE: This is not the exit status code of the detached process.
     *
     * @return integer|boolean FALSE if the command has not exited yet.
     */
    public function getExitStatus()
    {
        if ($this->getExecutionState() == self::STATE_EXITED) {
            return $this->exitStatus;
        }
        return FALSE;
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



    public function __wakeup()
    {
        ;
    }
}