<?php
/**
 * Nethgui
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
 * @see Nethgui_Core_ModuleInterface::prepareView()
 * @package Core
 */
class Nethgui_Core_View implements Nethgui_Core_ViewInterface
{

    /**
     * Reference to associated module
     * @var Nethgui_Core_ModuleInterface
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

    /**
     *
     * @var Nethgui_Language_Translator;
     */
    private $translator;

    public function __construct(Nethgui_Core_ModuleInterface $module, Nethgui_Language_Translator $translator)
    {
        $this->module = $module;
        $this->translator = $translator;

        // XXX: trying to guess view name
        $this->template = str_replace('_Module_', '_Template_', get_class($module));
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

    public function spawnView(Nethgui_Core_ModuleInterface $module, $register = FALSE)
    {
        $spawnedView = new self($module, $this->translator);
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
        if (is_null($offset)) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    public function translate($value, $args = array())
    {
        return $this->translator->translate($this->module, $value, $args);
    }

    public function getTranslator()
    {
        return $this->translator;
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

    public function getUniqueId($parts = '')
    {
        $prefix = implode('_', $this->getModulePath());

        if (empty($parts)) {
            return $prefix;
        }

        if (is_array($parts)) {
            $suffix = implode('_', $parts);
        } else {
            $suffix = $parts;
        }

        $suffix = str_replace('/', '_', $suffix);

        return $prefix . '_' . $suffix;
    }

    public function getClientEventTarget($name)
    {
        if (ENVIRONMENT === 'development') {
            return $this->getUniqueId($name);
        }

        return substr(md5($this->getUniqueId($name)), 0, 8);
    }

}