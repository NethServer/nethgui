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
     * Holds view state
     * @var array
     */
    private $data;
    /**
     *
     * @var string
     */
    private $template;


    public function __construct($module = NULL)
    {
        if ($module instanceof NethGui_Core_ModuleInterface) {
            $this->module = $module;
            // XXX: trying to guess view name
            $this->template = str_replace('_Module_', '_View_', get_class($module));
        }

        $this->data = array();
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

    public function spawnView(NethGui_Core_ModuleInterface $module, $register = FALSE)
    {
        $spawnedView = new self($module);
        if ($register) {
            $this[$module->getIdentifier()] = $spawnedView;
        }
        return $spawnedView;
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
        if ($this->module instanceof NethGui_Core_LanguageCatalogProvider) {
            $languageCatalog = $this->module->getLanguageCatalog();
        } else {
            $languageCatalog = NULL;
        }        

        $state = array(
            'view' => new NethGui_Renderer_Xhtml($this),
        );

        return NethGui_Framework::getInstance()->renderView($this->template, $state, $languageCatalog);
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
     * Returns a recursive array representation of this view and its descendants.
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

    public function getModule()
    {
        return $this->module;
    }

}