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
    /**
     * Caches the identifier of all ancestors from the root to the
     * associated $module.
     * @var array
     */
    private $modulePath;

    public function __construct($module = NULL)
    {
        if ($module instanceof NethGui_Core_ModuleInterface) {
            $this->module = $module;
            // XXX: trying to guess view name
            $this->template = str_replace('_Module_', '_Template_', get_class($module));
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

    public function getTemplate()
    {
        return $this->template;
    }

    public function spawnView(NethGui_Core_ModuleInterface $module, $register = FALSE)
    {
        $spawnedView = new self($module);
        if ($register === TRUE) {
            $this[$module->getIdentifier()] = $spawnedView;
        } elseif (is_string($register)) {
            $this[$register] = $spawnedView;
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
        $languageCatalog = NULL;
        if ($this->getModule() instanceof NethGui_Core_LanguageCatalogProvider) {
            $languageCatalog = $this->getModule()->getLanguageCatalog();
        }

        $state = array(
            // Decorate the view object with a Renderer interface:
            'view' => new NethGui_Renderer_Xhtml($this),
        );

        return NethGui_Framework::getInstance()->renderView($this->template, $state, $languageCatalog);
    }

    public function toJson()
    {
        $jsonString = '{';

        $separator = '';

        foreach ($this as $offset => $value) {
            $jsonString .= $separator;
            if (empty($separator)) {
                $separator = ',';
            }

            $jsonString .= json_encode($offset) . ':';

            if ($value instanceof NethGui_Core_View) {
                $jsonString .= $value->toJson();
            } elseif ($value instanceof Traversable) {
                $jsonString .= json_encode($this->traversableToArray($value));
            } else {
                $jsonString .= json_encode($value);
            }
        }

        $jsonString .= '}';

        return $jsonString;
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
     * Convert a Traversable object to a PHP array
     * @param Traversable $value
     * @return array
     */
    private function traversableToArray(Traversable $value)
    {
        $a = array();
        foreach ($value as $k => $v) {
            if ($v instanceof Traversable) {
                $v = $this->traversableToArray($v);
            }
            $a[$k] = $v;
        }
        return $a;
    }

    public function translate($value, $args = array())
    {
        return NethGui_Framework::getInstance()->translate($value, array(), NULL, NULL);
    }

    public function getModule()
    {
        return $this->module;
    }

    public function getModulePath()
    {
        if ( ! isset($this->modulePath)) {
            $this->modulePath = array();

            $watchdog = 0;
            $module = $this->getModule();

            while ( ! (is_null($module))) {
                if ( ++ $watchdog > 20) {
                    throw new Exception("Too many nested modules or cyclic module structure.");
                }
                array_unshift($this->modulePath, $module->getIdentifier());
                $module = $module->getParent();
            }
        }

        return $this->modulePath;
    }

}