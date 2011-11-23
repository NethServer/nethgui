<?php
/**
 * @package System
 * @ignore
 */

/**
 * Internal class for exec() return value
 *
 * @see Nethgui\System\NethPlatform::exec()
 * @ignore
 * @package System
 */
class Nethgui\System\ProcessDetached implements Nethgui\System\ProcessInterface, Nethgui\Core\GlobalFunctionConsumer, Serializable
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
     * @var Nethgui\System\Process
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
    private $outputPosition;

    /**
     *
     * @var Nethgui\Core\GlobalFunctionWrapper
     */
    protected $globalFunctionWrapper;

    public function __construct($command, $arguments = array())
    {
        $this->initialize();
        $this->innerCommand = new Nethgui\System\Process($this->shellBackgroundInvocation($command), $arguments);
        $this->setGlobalFunctionWrapper(new Nethgui\Core\GlobalFunctionWrapper());        
    }

    public function setGlobalFunctionWrapper(Nethgui\Core\GlobalFunctionWrapper $object)
    {
        $this->globalFunctionWrapper = $object;
        $this->innerCommand->setGlobalFunctionWrapper($object);
    }

    private function initialize()
    {
        $this->setExecutionState(self::STATE_NEW);
        $dir = '/tmp';
        $prefix = 'ng-detached-';
        $this->outputFile = tempnam($dir, $prefix);
        $this->errorFile = tempnam($dir, $prefix);
        $this->outputPosition = 0;
    }

    public function __clone()
    {
        $this->initialize();
    }

    private function shellBackgroundInvocation($commandTemplate)
    {
        return sprintf('/usr/bin/nohup %s >%s 2>%s & echo $!', $commandTemplate, escapeshellarg($this->outputFile), escapeshellarg($this->errorFile));
    }

    public function addArgument($arg)
    {
        $this->innerCommand->addArgument($arg);
    }

    public function exec()
    {
        if ($this->readExecutionState() != self::STATE_NEW) {
            return FALSE;
        }

        $this->innerCommand->exec();
        $this->processId = intval($this->innerCommand->getOutput());

        if ($this->processId > 0) {
            $this->setExecutionState(self::STATE_RUNNING);
        } else {
            $this->setExecutionState(self::STATE_EXITED);
        }

        return $this->readExecutionState();
    }

    private function setExecutionState($newState)
    {
        $this->state = $newState;
        if ($newState === self::STATE_EXITED) {
            $this->exitStatus = $this->processId > 0 ? 0 : 1;
        }
    }

    public function readExecutionState()
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
        if ($this->readExecutionState() == self::STATE_EXITED) {
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
        if ($this->readExecutionState() == self::STATE_RUNNING) {
            $killExitCode = NULL;
            $killOutput = array();
            $this->globalFunctionWrapper->exec(sprintf('/bin/kill %d', $this->processId), $killOutput, $killExitCode);
            if ($killExitCode === 0) {
                $this->setExecutionState(self::STATE_EXITED);
                return TRUE;
            }
        }
        return FALSE;
    }

    public function serialize()
    {
        $ostate = array(
            $this->errorFile,
            $this->outputFile,
            $this->processId,
            $this->exitStatus,
            $this->globalFunctionWrapper,
            $this->outputPosition,
        );

        return serialize($ostate);
    }

    public function unserialize($serialized)
    {
        $ostate = unserialize($serialized);

        list(
            $this->errorFile,
            $this->outputFile,
            $this->processId,
            $this->exitStatus,
            $this->globalFunctionWrapper,
            $this->outputPosition,
            ) = $ostate;

        return $this;
    }

    public function readOutput()
    {
        $currentOutput = (String) $this->getOutput();
        $nextPos = strlen($currentOutput);

        if ($nextPos > 0) {
            $buffer = substr($currentOutput, $this->outputPosition);
        } else {
            $buffer = '';
        }

        $this->outputPosition = $nextPos;

        return $buffer;
    }

}