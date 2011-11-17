<?php
/**
 * @package Client
 */

/**
 * Invoke a Nethgui javascript method on the client-side.
 *
 * @package Client
 */
class Nethgui_Client_CommandFactory implements Nethgui_Core_CommandFactoryInterface
{

    /**
     *
     * @var Nethgui_Core_ViewInterface
     */
    private $view;

    public function __construct(Nethgui_Core_ViewInterface $view)
    {
        $this->view = $view;
    }

    public function activate(Nethgui_Core_ModuleInterface $action, $arguments = array())
    {
        array_unshift($arguments, $this->view->spawnView($action)->getModuleUrl());
        return $this->methodCall('activate', $arguments);
    }

    public function delay(Nethgui_Client_CommandInterface $cmd, $delay = NULL)
    {
        return new Nethgui_Client_DelayCommand($cmd, $delay);
    }

    public function query(Nethgui_Core_ModuleInterface $action, $arguments = array())
    {
        array_unshift($arguments, $this->view->spawnView($action)->getModuleUrl());
        return $this->methodCall('queryUrl', $arguments);
    }

    public function sequence(Nethgui_Client_CommandInterface $cmd1, Nethgui_Client_CommandInterface $cmd2)
    {
        return new Nethgui_Client_CommandSequence(func_get_args());
    }

    public function methodCall($methodName, $arguments)
    {
        return new Nethgui_Client_MethodCallCommand($methodName, $arguments);
    }

}

abstract class Nethgui_Client_AbstractCommand implements Nethgui_Client_CommandInterface
{

    /**
     *
     * @var mixed
     */
    private $receiver = FALSE;

    /**
     *
     * @var string
     */
    protected $methodName = '';

    /**
     *
     * @var array
     */
    protected $arguments = array();

    public function getMethod()
    {
        return $this->methodName;
    }

    public function execute()
    {
        throw new Exception(sprintf('%s: execute() is not implemented', get_class($this)));
    }

    public function setReceiver($receiver)
    {
        $this->receiver = $receiver;
    }

    public function getArguments()
    {
        return $this->arguments;
    }

    public function getReceiver()
    {
        return $this->receiver;
    }

}

class Nethgui_Client_DelayCommand extends Nethgui_Client_AbstractCommand
{

    public function getMethod()
    {
        return 'delayCommand';
    }

    public function __construct(Nethgui_Client_CommandInterface $cmd, $delay = NULL)
    {
        $this->arguments = array($cmd, $delay);
    }

    public function execute()
    {
        $this->command->execute();
    }

}

/**
 * @ignore
 */
class Nethgui_Client_MethodCallCommand extends Nethgui_Client_AbstractCommand
{

    public function __construct($methodName, $arguments)
    {
        $this->methodName = $methodName;
        $this->arguments = $arguments;
    }

    public function execute()
    {
        $context = $this->getReceiver();
        if ( ! is_object($context)) {
            throw new BadMethodCallException(sprintf('%s: invalid receiver object', get_class($this)));
        }
        if ( ! method_exists($context, $this->methodName)) {
            throw new BadMethodCallException(sprintf('%s: method does not exist or is not accessible in the context object!', get_class($this)));
        }
        call_user_func_array(array($context, $this->methodName), $this->arguments);
    }

}

/**
 * @ignore
 */
class Nethgui_Client_CommandSequence extends Nethgui_Client_AbstractCommand
{

    private $commands;

    public function __construct($commands)
    {
        $this->commands = array();
        foreach ($commands as $command) {
            if ( ! $command instanceof Nethgui_Client_CommandInterface) {
                throw new InvalidArgumentException(sprintf('%s: every argument must be a Nethgui_Client_CommandInterface instance!', get_class($this)));
            }

            $this->commands[] = $command;
        }
    }

    public function execute()
    {
        foreach ($this->commands as $command) {
            $command->execute();
        }
    }

    public function getMethod()
    {
        return 'commandSequence';
    }

}
