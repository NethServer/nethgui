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
class Nethgui_Client_Command_QueryUrl implements Nethgui_Client_CommandInterface
{

    /**
     * @var Nethgui_Core_ModuleInterface
     */
    private $url;

    public function __construct(Nethgui_Core_ModuleInterface $module, $path = array())
    {
        $this->url = Nethgui_Framework::getInstance()->buildModuleUrl($module, $path);
    }

    public function getArguments()
    {
        return array($this->url);
    }

    public function getMethod()
    {
        return "queryUrl";
    }

    public function getTargetSelector()
    {
        return sprintf('.ClientCommandHandler');
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
