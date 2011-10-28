<?php
/**
 * @package Module
 * @subpackage Help
 */

/**
 * @package Module
 * @subpackage Help
 * @author Davide Principi <davide.principi@nethesis.it>
 */
class Nethgui_Module_Help_Template extends Nethgui_Module_Help_Common
{
    public function process()
    {
        $rootView = new Nethgui_Core_View($this->module);
        $rootView->setTemplate('Nethgui_Template_Help_Schema');

        $moduleView = $rootView->spawnView($this->module);
        $this->module->prepareView($moduleView, self::VIEW_HELP);

        $rootView['title'] = $this->module->getTitle();
        $rootView['lang'] = Nethgui_Framework::getInstance()->getLanguageCode();
        $rootView['content'] = $moduleView;
        $rootView['url'] = NETHGUI_SITEURL . Nethgui_Framework::getInstance()->buildModuleUrl($this, $this->module->getIdentifier() . '.html');

        $this->globalFunctions->header("Content-Type: text/html; charset=UTF-8");
        echo (String) new Nethgui_Renderer_Xhtml($rootView);
        exit;
    }  
}
