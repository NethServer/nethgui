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

    private $modulePrefixes = array();

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

}
