<?php
/**
 * NethGui
 *
 * @package NethGuiFramework
 */

/**
 * A view object holds output data and references to child views.
 * 
 * It inherits an ArrayAccess interface and is capable to convert its internal
 * state to a string using a Template.
 *
 * A Template can be a PHP script or a callback function that receives the
 * view state.
 *
 * Usually, PHP templates are kept into View/ directories, but generally they
 * follow the class naming convention.
 *
 * Moreover, every module has a View object assigned to it as a parameter during
 * prepareView() operation.
 *
 * @see NethGui_Core_ModuleInterface::prepareView()
 * @package NethGuiFramework
 * @subpackage StandardImplementation
 */
class NethGui_Core_View implements NethGui_Core_ViewInterface
{

    /**
     * Holds child views
     * @var array
     */
    private $children;
    /**
     * Reference to associated module
     * @var NethGui_Core_ModuleInterface
     */
    private $module;
    /**
     * Holds view state
     * @var array
     */
    private $data;
    /**
     *
     * @var string
     */
    private $template;

    public function __construct(NethGui_Core_ModuleInterface $module)
    {
        $this->children = array();
        $this->module = $module;
        $this->data = array();

        // XXX: trying to guess view name
        $this->template = str_replace('_Module_', '_View_', get_class($module));
    }

    private function getFullName($parameterName)
    {
        // TODO: cache prefix value
        return $this->calculateModulePrefix($this->module) . '[' . $parameterName . ']';
    }

    private function getFullId($widgetId)
    {
        $name = $this->getFullName($widgetId);
        $name = str_replace('[', '_', $name);
        $name = str_replace(']', '', $name);
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
     * TODO: rename to "mergeFrom()"
     * @param mixed $data
     */
    public function copyFrom($data)
    {
        foreach ($data as $offset => $value) {
            $this->offsetSet($offset, $value);
        }
    }

    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * Returns the View associated with $module.
     * @param NethGui_Core_ModuleInterface $module
     * @return NethGui_Core_ViewInterface
     */
    public function getInnerView(NethGui_Core_ModuleInterface $module)
    {
        $moduleId = $module->getIdentifier();

        if ( ! isset($this->children[$moduleId])) {
            // Registers a new child
            $child = new self($module);
            $this->children[$moduleId] = $child;
        }

        return $this->children[$moduleId];
    }

    public function getIterator()
    {
        return new ArrayIterator(array_merge($this->children, $this->data));
    }

    /**
     * Returns the Module associated with this instance.
     * 
     * @return NethGui_Core_ModuleInterface Module associated with this instance.
     */
    public function getModule()
    {
        return $this->module;
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->data)
        || isset($this->children[$offset]);
    }

    public function offsetGet($offset)
    {
        if (array_key_exists($offset, $this->data)) {
            return $this->data[$offset];
        } elseif (isset($this->children[$offset])) {
            return $this->children[$offset];
        }

        return NULL;
    }

    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    /**
     * Renders the view.
     *
     * @return string
     */
    public function render()
    {
        $state = array();

        $state['view'] = $this;
        $state['id'] = array();
        $state['name'] = array();
        $state['parameters'] = array();
        $state['module'] = $this->getModule();
        $state['framework'] = NethGui_Framework::getInstance();

        // Add a reference to forward current view state into inner views.
        $state['self'] = &$state;

        // Put all view data into id, name, parameter helper arrays.
        foreach ($this->data as $parameterName => $parameterValue) {
            $state['id'][$parameterName] = htmlspecialchars($this->getFullId($parameterName));
            $state['name'][$parameterName] = htmlspecialchars($this->getFullName($parameterName));

            if (is_string($parameterValue)) {
                $state['parameters'][$parameterName] = htmlspecialchars($parameterValue);
            } else {
                $state['parameters'][$parameterName] = $parameterValue;
            }
        }

        // TODO: add a getLanguageCatalog to ModuleInterface.
        $languageCatalog = get_class($this->getModule());

        if(is_string($this->template))
        {
            $string = NethGui_Framework::getInstance()->renderView($this->template, $state, $languageCatalog);
        } elseif (is_array($this->template)) {
            $string = call_user_func($this->template, $state);
        } elseif (is_null($this->template)) {
            $string = '';
        }

        return $string;
    }




}