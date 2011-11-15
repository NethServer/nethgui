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
class Nethgui_Module_Help_Common extends Nethgui_Core_Module_Standard implements Nethgui_Core_GlobalFunctionConsumer
{

    /**
     *
     * @var Nethgui_Core_ModuleInterface
     */
    protected $module;

    /**
     *
     * @var Nethgui_Core_ModuleSetInterface
     */
    public $moduleSet;

    /**
     *
     * @var Nethgui_Core_GlobalFunctionWrapper
     */
    protected $globalFunctions;

    public function __construct($identifier = NULL)
    {
        parent::__construct($identifier);
        $this->globalFunctions = new Nethgui_Core_GlobalFunctionWrapper();        
    }

    public function bind(Nethgui_Core_RequestInterface $request)
    {
        parent::bind($request);

        $arguments = $request->getArguments();

        if (empty($arguments) || preg_match('/[a-z][a-z0-9]+(.html)/i', $arguments[0]) == 0) {
            throw new Nethgui_Exception_HttpStatusClientError('Not found', 404);
        }

        // Now assuming a trailing ".html" suffix.
        $this->module = $this->moduleSet->findModule(substr($arguments[0], 0, -5));

        if (is_null($this->module)) {
            throw new Nethgui_Exception_HttpStatusClientError('Not found', 404);
        }
        $this->module->initialize();
        $this->module->bind($request->getParameterAsInnerRequest('', array_slice($arguments, 1)));
    }

    protected function getHelpDocumentPath(Nethgui_Core_ModuleInterface $module)
    {
        $fileName = get_class($module) . '.html';
        $appPath = realpath(NETHGUI_ROOTDIR . '/' . NETHGUI_APPLICATION);
        $lang = $this->getRequest()->getUser()->getLanguageCode();

        return "${appPath}/Help/${lang}/${fileName}";
    }

    public function setGlobalFunctionWrapper(Nethgui_Core_GlobalFunctionWrapper $object)
    {
        $this->globalFunctions = $object;
    }

}

?>
