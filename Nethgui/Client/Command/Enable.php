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
class Nethgui_Client_Command_Enable implements Nethgui_Client_CommandInterface
{

    /**
     * @var Nethgui_Core_ModuleInterface
     */
    private $action;

    public function __construct(Nethgui_Core_ModuleInterface $action)
    {
        $this->action = $action;
    }

    public function getArguments()
    {
        return array();
    }

    public function getMethod()
    {
        return "enable";
    }

    public function getTargetSelector()
    {
        return sprintf('#%s', $this->calcModuleId($this->action));
    }

    /**
     * FIXME: Need refactor, creating a view object to get $module unique id only...
     * @param Nethgui_Core_ModuleInterface $module
     * @return type
     */
    private function calcModuleId(Nethgui_Core_ModuleInterface $module)
    {
        $tmpView = new Nethgui_Core_View($module);
        return $tmpView->getUniqueId();
    }

    public function getRedirectionUrl()
    {
        $redirect = new Nethgui_Client_Command_Redirect($this->action);
        return $redirect->getAbsoluteUrl();
    }

    public function isRedirection()
    {
        return TRUE;
    }

}
