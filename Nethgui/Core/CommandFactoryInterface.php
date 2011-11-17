<?php
/**
 * @package Core
 */

/**
 * Creates user interface command objects
 *
 * @package Core
 */
interface Nethgui_Core_CommandFactoryInterface
{

    /**
     * @param Nethgui_Client_CommandInterface $cmd
     * @param integer $delay
     * @return Nethgui_Client_CommandInterface
     */
    public function delay(Nethgui_Client_CommandInterface $cmd, $delay = NULL);

    /**
     * @param Nethgui_Client_CommandInterface $cmd1
     * @param Nethgui_Client_CommandInterface $cmd2
     * @return Nethgui_Client_CommandInterface
     */
    public function sequence(Nethgui_Client_CommandInterface $cmd1, Nethgui_Client_CommandInterface $cmd2);

    /**
     * @param Nethgui_Core_Module $action
     * @param array $parameters
     * @return Nethgui_Client_CommandInterface
     */
    public function activate(Nethgui_Core_ModuleInterface $action, $arguments = array());

    /**
     * @param Nethgui_Core_Module $action
     * @param array $parameters
     * @return Nethgui_Client_CommandInterface
     */
    public function query(Nethgui_Core_ModuleInterface $action, $arguments = array());

    /**
     * @param string $methodName
     * @param array $arguments
     * @return Nethgui_Client_CommandInterface
     */
    public function methodCall($methodName, $arguments);
}
