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
class Nethgui_Client_Command_Redirect implements Nethgui_Client_CommandInterface
{

    private $url;

    public function __construct(Nethgui_Core_ModuleInterface $module, $path = array())
    {
        $this->url = Nethgui_Framework::getInstance()->buildModuleUrl($module, $path);
    }

    public function getArguments()
    {
        return array($this->getAbsoluteUrl());
    }

    public function getMethod()
    {
        return "redirect";
    }

    public function getTargetSelector()
    {
        return '.ClientCommandHandler';
    }

    public function getAbsoluteUrl()
    {
        return NETHGUI_SITEURL . $this->url;
    }

    public function isRedirection()
    {
        return FALSE;
    }

    public function getRedirectionUrl()
    {
        return NULL;
    }

}