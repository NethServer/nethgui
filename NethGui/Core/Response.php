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
final class NethGui_Core_Response implements NethGui_Core_ViewInterface, ArrayAccess
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
    public static function getRootInstance($viewType, NethGui_Core_Module_World $worldModule)
    {
        static $rootResponse;

        if ( ! isset($rootResponse)) {
            $rootResponse = new self($viewType, $worldModule);
        }

        return $rootResponse;
    }

    private function __construct($viewType, NethGui_Core_ModuleInterface $module)
    {
        $this->children = array();
        $this->viewType = $viewType;
        $this->module = $module;
        $this->data = array();

        // Set default view name.
        switch ($viewType) {
            case self::JSON:
                $this->viewName = 'NethGui_Core_View_json';
                break;

            case self::HTML:
                $this->viewName = str_replace('_Module_', '_View_', get_class($module));
                break;

            default:
                $this->viewName = '';
        }
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
            if (is_null($module) || $module instanceof NethGui_Core_Module_World) {
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

    public function getWholeData()
    {
        $wholeData = array();

        foreach ($this->children as $innerResponse) {
            $innerId = $innerResponse->getModule()->getIdentifier();

            $innerData = $innerResponse->getWholeData();

            if ( ! empty($innerData))
            {
                $wholeData = array_merge($wholeData, array($innerId => $innerData));
            }
        }

        $wholeData = array_merge($wholeData, $this->data);

        return $wholeData;
    }

    public function setViewName($viewName)
    {
        $this->viewName = $viewName;
    }

    public function getViewName()
    {
        return $this->viewName;
    }

    /**
     * Returns a Response associated with $module.
     * @param NethGui_Core_ModuleInterface $module
     * @return NethGui_Core_ViewInterface
     */
    public function getInnerView(NethGui_Core_ModuleInterface $module)
    {
        $moduleId = $module->getIdentifier();

        if ( ! isset($this->children[$moduleId])) {
            // Registers a new child
            $child = new self($this->getFormat(), $module);
            $this->children[$moduleId] = $child;
        }

        return $this->children[$moduleId];
    }

    /**
     * Returns the Module associated with this Response instance.
     * 
     * @return NethGui_Core_ModuleInterface Module associated with this Response instance.
     */
    public function getModule()
    {
        return $this->module;
    }

    public function offsetExists($offset)
    {
        if (isset($this->data[$offset])) {
            return TRUE;
        } elseif (isset($this->children[$offset])) {
            return TRUE;
        }

        return FALSE;
    }

    public function offsetGet($offset)
    {
        if (isset($this->data[$offset])) {
            return $this->data[$offset];
        } elseif (isset($this->children[$offset])) {
            return $this->children[$offset];
        }

        return NULL;
    }

    public function offsetSet($offset, $value)
    {
        throw new Exception("Invalid operation");
    }

    public function offsetUnset($offset)
    {
        throw new Exception("Invalid operation");
    }

}