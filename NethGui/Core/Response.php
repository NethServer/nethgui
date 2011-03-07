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

    /**
     * Internal cache of module fully qualified prefixes
     * @var array
     */
    private $modulePrefixes = array();
    /**
     * View Data repository
     * @var array
     */
    private $moduleViewData = array();
    /**
     * View names
     * @var array
     */
    private $moduleViewNames = array();



    public function __construct($viewType)
    {
        $this->viewType = $viewType;

        switch ($this->viewType) {
            case self::HTML:
                header("Content-Type: text/html; charset=UTF-8");
                break;

            case self::JS:
                // XXX: Non-compliant browsers may have a problem with
                //      JS mime-type.
                header("Content-Type: application/x-javascript; charset=UTF-8");
                break;

            case self::CSS:
                header("Content-Type: text/css; charset=UTF-8");
                break;

            default:
                throw new Exception("Unknown view type code: " . $viewType);
        }
    }

    public function getViewType()
    {
        return $this->viewType;
    }

    public function getParameterName(NethGui_Core_ModuleInterface $module, $parameterName)
    {
        $moduleObjectId = spl_object_hash($module);
        if ( ! isset($this->modulePrefixes[$moduleObjectId])) {
            $this->modulePrefixes[$moduleObjectId] = $this->calculateModulePrefix($module);
        }
        return $this->modulePrefixes[$moduleObjectId] . '[' . $parameterName . ']';
    }

    public function getWidgetId(NethGui_Core_ModuleInterface $module, $widgetId)
    {
        $name = $this->getParameterName($module, $widgetId);
        $name = str_replace('[', '_', $name);
        $name = str_replace(']', '_', $name);

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
     *
     * @param NethGui_Core_ModuleInterface $module
     * @param mixed $data
     */
    public function setViewData(NethGui_Core_ModuleInterface $module, $data)
    {
        $this->moduleViewData[spl_object_hash($module)] = $data;
    }

    /**
     *
     * @param NethGui_Core_ModuleInterface $module
     * @return mixed 
     */
    public function getViewData(NethGui_Core_ModuleInterface $module)
    {
        $objectHash = spl_object_hash($module);
        if ( ! isset($this->moduleViewData[$objectHash]))
        {
            return NULL;
        }

        return $this->moduleViewData[$objectHash];
    }

    public function setViewName($module, $viewName)
    {
        $objectHash = spl_object_hash($module);

        $this->moduleViewNames[$objectHash] = $viewName;
    }

    public function getViewName($module)
    {
        $objectHash = spl_object_hash($module);
        if ( ! isset($this->moduleViewNames[$objectHash]))
        {
            $defaultViewName = str_replace('_Module_', '_View_', get_class($module));
            return $defaultViewName;
        }

        return $this->moduleViewNames[$objectHash];
    }
    
}
