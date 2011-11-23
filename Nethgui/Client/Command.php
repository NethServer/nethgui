<?php
/**
 * @package Client
 * @author Davide Principi <davide.principi@nethesis.it>
 */

namespace Nethgui\Client;

/**
 * @package Client
 * @ignore
 */
class Command implements \Nethgui\Core\CommandInterface
{

    /**
     *
     * @var object
     */
    private $receiver;

    /**
     *
     * @var string
     */
    private $methodName;

    /**
     *
     * @var array
     */
    private $arguments;

    /**
     *
     * @var boolean
     */
    private $executed;

    public function __construct($methodName, $arguments = array())
    {
        $this->methodName = $methodName;
        $this->arguments = $arguments;
        $this->executed = FALSE;
    }

    public function execute()
    {
        if ($this->executed === TRUE) {
            throw new LogicException(sprintf('%s: command was already executed', get_class($this)));
        }

        if ($this->receiver instanceof \Nethgui\Core\CommandReceiverInterface) {
            $this->executed = TRUE;
            return $this->receiver->executeCommand($this->methodName, $this->arguments);
        }

        if ($this->receiver instanceof \Nethgui\Core\CommandReceiverAggregateInterface) {
            $this->executed = TRUE;
            return $this->receiver->getCommandReceiver()->executeCommand($this->methodName, $this->arguments);
        }

        throw new LogicException(sprintf('%s: invalid receiver object', get_class($this)));
    }

    public function setReceiver($receiver)
    {
        $this->receiver = $receiver;
        return $this;
    }

    public function isExecuted()
    {
        return $this->executed === TRUE;
    }

}
