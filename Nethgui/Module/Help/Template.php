<?php
/**
 * @package Module
 * @subpackage Help
 */

namespace Nethgui\Module\Help;

/**
 * @package Module
 * @subpackage Help
 * @author Davide Principi <davide.principi@nethesis.it>
 */
class Template extends Common
{
    public function prepareView(Nethgui\Core\ViewInterface $rootView, $mode)
    {
        $rootView->setTemplate('Nethgui_Template_Help_Schema');

        $moduleView = $rootView->spawnView($this->module);
        $this->module->prepareView($moduleView, self::VIEW_HELP);

        $rootView['title'] = $this->module->getTitle();
        $rootView['lang'] = $this->getRequest()->getUser()->getLanguageCode();
        $rootView['content'] = $moduleView;
        $rootView['url'] = NETHGUI_SITEURL . $rootView->getModuleUrl($this->module->getIdentifier() . '.html');

        $this->globalFunctions->header("Content-Type: text/html; charset=UTF-8");
        echo (String) new Nethgui\Renderer\Xhtml($rootView);
        exit;
    }
}
