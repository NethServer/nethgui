<?php
/**
 * NethGui
 *
 * @package NethGuiFramework
 */

/**
 * TODO: describe class
 *
 * @package NethGuiFramework
 * @subpackage StandardImplementation
 */
final class Response {
    const HTML = 0;
    const JS = 1;

    private $modulePrefixes = array();

    public function __construct($viewType)
    {
        $this->viewType = $viewType;
    }

    public function getViewType()
    {
        return $this->viewType;
    }

    public function getParameterName(ModuleInterface $module, $parameterName)
    {
        $moduleObjectId = spl_object_hash($module);
        if ( ! isset($this->modulePrefixes[$moduleObjectId]))
        {
            $this->modulePrefixes[$moduleObjectId] = $this->calculateModulePrefix($module);
        }
        return $this->modulePrefixes[$moduleObjectId] . '[' . $parameterName . ']';
    }

    public function getWidgetId(ModuleInterface $module, $widgetId)
    {
        $name = $this->getParameterName($module, $widgetId);
        $name = str_replace('[', '_', $name);
        $name = str_replace(']', '_', $name);

        return $name;
    }

    private function calculateModulePrefix(ModuleInterface $module)
    {
        $prefix = '';
        while (TRUE)
        {
            $identifier = $module->getIdentifier();
            $module = $module->getParent();
            if (is_null($module))
            {
                $prefix = $identifier . $prefix;
                break;
            }
            else
            {
                $prefix = '[' . $identifier . ']' . $prefix;
            }
        }
        return $prefix;
    }

}
