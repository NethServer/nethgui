<?php
/**
 * NethGui
 *
 * @package NethGuiFramework
 */

/**
 * UNSTABLE
 *
 * TODO: describe class
 *
 * @package NethGuiFramework
 * @subpackage StandardImplementation
 */
final class NethGui_Core_Response implements NethGui_Core_ResponseInterface
{

    private $children;
    /**
     *
     * @var NethGui_Core_ModuleInterface
     */
    private $module;
    /**
     *
     * @var int
     */
    private $viewType;
    /**
     *
     * @var array
     */
    private $data;
    /**
     *
     * @var string
     */
    private $viewName;

    /**
     * Get the root response singleton instance.
     * 
     * @staticvar NethGui_Core_Response $rootResponse
     * @param int $viewType
     * @return NethGui_Core_Response
     */
    public function getRootInstance($viewType)
    {
        static $rootResponse;

        if ( ! isset($rootResponse)) {
            $rootResponse = new self($viewType, new NethGui_Core_Module_World());
        }

        return $rootResponse;
    }

    private function __construct($viewType, NethGui_Core_ModuleInterface $module)
    {
        $this->children = array();
        $this->viewType = $viewType;
        $this->module = $module;
        $this->data = array();
        $this->viewName = str_replace('_Module_', '_View_', get_class($module));
    }

    public function getFormat()
    {
        return $this->viewType;
    }

    public function getParameterName($parameterName)
    {
        // TODO: cache prefix value
        return $this->calculateModulePrefix($this->module) . '[' . $parameterName . ']';
    }

    public function getWidgetId($widgetId)
    {
        $name = $this->getParameterName($widgetId);
        $name = str_replace('][', '_', $name);
        $name = str_replace('[', '_', $name);
        $name = str_replace(']', '_', $name);
        $name = trim($name, '_');
        return $name;
    }

    private function calculateModulePrefix(NethGui_Core_ModuleInterface $module)
    {
        $prefix = '';
        while (TRUE) {
            $identifier = $module->getIdentifier();
            $module = $module->getParent();
            if (is_null($module)) {
                $prefix = $identifier . $prefix;
                break;
            } else {
                $prefix = '[' . $identifier . ']' . $prefix;
            }
        }
        return $prefix;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

//    public function getWholeData()
//    {
//        $wholeData = array();
//
//        foreach ($this->getInnerResponses() as $innerResponse) {
//            $innerId = $innerResponse->getModule()->getIdentifier();
//
//            $wholeData = array_merge($wholeData, array($innerId => $innerResponse->getWholeData()));
//        }
//
//        $wholeData = array_merge($wholeData, $this->getData());
//
//        return $wholeData;
//    }

    public function setViewName($viewName)
    {
        $this->viewName = $viewName;
    }

    public function getViewName()
    {
        return $this->viewName;
    }

    public function getInnerResponses()
    {
        return array_values($this->children);
    }

    public function getInnerResponse(NethGui_Core_ModuleInterface $module)
    {
        $moduleId = $module->getIdentifier();
        
        if ( ! isset($this->children[$moduleId])) {
            // Registers a new child
            $child = new self($this->getFormat(), $module);
            $this->children[$moduleId] = $child;
        }

        return $this->children[$moduleId];
    }


    public function getModule()
    {
        return $this->module;
    }

}
