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
class Nethgui_Module_Help_Template extends Nethgui_Core_Module_Standard
{

    /**
     *
     * @var Nethgui_Core_ModuleInterface
     */
    private $module;

    /**
     *
     * @var Nethgui_Core_ModuleSetInterface
     */
    public $moduleSet;

    public function bind(Nethgui_Core_RequestInterface $request)
    {
        parent::bind($request);

        $arguments = $request->getArguments();

        if (empty($arguments)) {
            return;
        }

        if (preg_match('/[a-z][a-z0-9]+/i', $arguments[0]) == 0) {
            throw new Nethgui_Exception_HttpStatusClientError('Not found', 404);
        }

        $this->module = $this->moduleSet->findModule($arguments[0]);

        if (is_null($this->module)) {
            throw new Nethgui_Exception_HttpStatusClientError('Not found', 404);
        }
        $this->module->initialize();
        $this->module->bind($request->getParameterAsInnerRequest('', array_slice($arguments, 1)));
    }

    public function process()
    {
        $rootView = new Nethgui_Core_View($this->module);
        $rootView->setTemplate('Nethgui_Template_Help_Schema');

        $moduleView = $rootView->spawnView($this->module);
        $this->module->prepareView($moduleView, self::VIEW_HELP);

        $rootView['title'] = $this->module->getTitle();
        $rootView['lang'] = Nethgui_Framework::getInstance()->getLanguageCode();
        $rootView['content'] = $moduleView;

        header("Content-Type: text/html; charset=UTF-8");
        echo (String) new Nethgui_Renderer_Xhtml($rootView);
        exit;
    }
   
}
