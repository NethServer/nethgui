<?php
/**
 * @package Client
 * @subpackage Command
 * @ignore
 */

/**
 * @package Client
 * @subpackage Command
 * @ignore
 */
class Nethgui_Client_Command_DelayCommand implements Nethgui_Client_CommandInterface
{

    /**
     * @var Nethgui_Client_CommandInterface
     */
    private $command;

    public function __construct(Nethgui_Client_CommandInterface $command, $delay = 1000)
    {
        $this->command = $command;
        $this->delay = $delay;
    }

    public function getArguments()
    {
        $a = array(
            'targetSelector' => $this->command->getTargetSelector(),
            'method' => $this->command->getMethod(),
            'arguments' => $this->command->getArguments(),
        );
        return array($a, $this->delay);
    }

    public function getMethod()
    {
        return "delayCommand";
    }

    public function getTargetSelector()
    {
        return '.ClientCommandHandler';
    }

    public function getRedirectionUrl()
    {
        return NULL;
    }

    public function isRedirection()
    {
        return FALSE;
    }

}
