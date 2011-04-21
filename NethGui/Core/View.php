<?php
/**
 * NethGui
 *
 * @package Core
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
 * @package Core
 */
class NethGui_Core_View implements NethGui_Core_ViewInterface
{
    /**
     * Reference to associated module
     * @var NethGui_Core_ModuleInterface
     */
    private $module;
    /**
     * Module path caches the identifier of all ancestors from the root to the
     * associated module.
     * @var array
     */
    private $modulePath;

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
        $this->module = $module;
        $this->data = array();

        // XXX: trying to guess view name
        $this->template = str_replace('_Module_', '_View_', get_class($module));
    }

    private function getFullName($parameterName)
    {
        $path = $this->getModulePath();
        $path[] = $parameterName;
        $prefix = array_shift($path);

        return $prefix . '[' . implode('][', $path) . ']';
    }

    private function getFullId($widgetId)
    {
        return implode('_', $this->getModulePath()) . '_' . $widgetId;
    }

    /**
     * Return the array of parent module identifiers.
     * @return array
     */
    private function getModulePath()
    {
        if ( ! isset($this->modulePath)) {
            $this->modulePath = array();

            $watchdog = 0;
            $module = $this->module;

            while ( ! (is_null($module) || $module instanceof NethGui_Core_Module_World)) {
                if ( ++ $watchdog > 20) {
                    throw new Exception("Too many nested modules or cyclic module structure.");
                }
                array_unshift($this->modulePath, $module->getIdentifier());
                $module = $module->getParent();
            }
        }

        return $this->modulePath;
    }

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

    public function spawnView(NethGui_Core_ModuleInterface $module)
    {
        return new self($module);
    }

    public function getIterator()
    {
        return new ArrayIterator($this->data);
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->data);
    }

    public function offsetGet($offset)
    {
        if ($this->offsetExists($offset)) {
            return $this->data[$offset];
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
        $state['module'] = $this->module;
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

        if ($this->module instanceof NethGui_Core_LanguageCatalogProvider) {
            $languageCatalog = $this->module->getLanguageCatalog();
        } else {
            $languageCatalog = NULL;
        }

        if (is_string($this->template)) {
            $viewString = NethGui_Framework::getInstance()->renderView($this->template, $state, $languageCatalog);
        } elseif (is_callable($this->template)) {
            $viewString = call_user_func($this->template, $state);
        } elseif (is_null($this->template)) {
            $viewString = '';
        }

        return $viewString;
    }

    /**
     * @see NethGui_Core_View::render()
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * Returns an array representation of this view and all its aggregates.
     * @return array
     */
    public function getArrayCopy(NethGui_Core_View $view = NULL, $depth = 0)
    {
        if ($depth > 10) {
            return;
        }

        if (is_null($view)) {
            $view = $this;
        }

        $data = array();

        foreach ($view as $offset => $value) {
            if ($value instanceof NethGui_Core_View) {
                $data[$offset] = $this->getArrayCopy($value, $depth + 1);
            } elseif ($value instanceof ArrayObject) {
                $data[$offset] = $value->getArrayCopy();
            } elseif (is_string($value)
                && $this->module instanceof NethGui_Core_LanguageCatalogProvider) {
                $languageCatalog = $this->module->getLanguageCatalog();
                $data[$offset] = T($value, array(), NULL, $languageCatalog);
            } else {
                $data[$offset] = $value;
            }
        }

        return $data;
    }

    /**
     *
     * @param array|string $_ Arguments for URL
     * @return string the URL
     */
    public function buildUrl()
    {
        $parameters = array();
        $path = $this->getModulePath();

        foreach (func_get_args() as $arg) {
            if (is_string($arg)) {
                $path[] = $arg;
            } elseif (is_array($arg)) {
                $parameters = array_merge($parameters, $arg);
            }
        }

        return NethGui_Framework::getInstance()->buildUrl($path, $parameters);
    }


}