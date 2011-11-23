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
class Common extends \Nethgui\Core\Module\Standard implements \Nethgui\Core\GlobalFunctionConsumer
{

    /**
     *
     * @var \Nethgui\Core\ModuleInterface
     */
    protected $module;

    /**
     *
     * @var \Nethgui\Core\ModuleSetInterface
     */
    public $moduleSet;

    /**
     *
     * @var \Nethgui\Core\GlobalFunctionWrapper
     */
    protected $globalFunctions;

    public function __construct($identifier = NULL)
    {
        parent::__construct($identifier);
        $this->globalFunctions = new \Nethgui\Core\GlobalFunctionWrapper();        
    }

    public function bind(\Nethgui\Core\RequestInterface $request)
    {
        parent::bind($request);

        $arguments = $request->getArguments();

        if (empty($arguments) || preg_match('/[a-z][a-z0-9]+(.html)/i', $arguments[0]) == 0) {
            throw new \Nethgui\Exception\HttpStatusClientError('Not found', 404);
        }

        // Now assuming a trailing ".html" suffix.
        $this->module = $this->moduleSet->findModule(substr($arguments[0], 0, -5));

        if (is_null($this->module)) {
            throw new \Nethgui\Exception\HttpStatusClientError('Not found', 404);
        }
        $this->module->initialize();
        $this->module->bind($request->getParameterAsInnerRequest('', array_slice($arguments, 1)));
    }

    protected function getHelpDocumentPath(\Nethgui\Core\ModuleInterface $module)
    {
        $fileName = get_class($module) . '.html';
        $appPath = realpath(NETHGUI_ROOTDIR . '/' . NETHGUI_APPLICATION);
        $lang = $this->getRequest()->getUser()->getLanguageCode();

        return "${appPath}/Help/${lang}/${fileName}";
    }

    public function setGlobalFunctionWrapper(\Nethgui\Core\GlobalFunctionWrapper $object)
    {
        $this->globalFunctions = $object;
    }

}

?>
