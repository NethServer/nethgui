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
     * Caches the language catalogs associated to $module
     * @var array
     */
    private $languageCatalogList;

    public function __construct($module = NULL)
    {
        if ($module instanceof Nethgui_Core_ModuleInterface) {
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

    public function spawnView(Nethgui_Core_ModuleInterface $module, $register = FALSE)
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

    /**
     * Get the array of events to properly transfer the view on the client side.
     * @return array
     */
    public function getClientEvents()
    {
        $events = array();

        return $this->fillEvents($events);
    }

    private function fillEvents(&$events)
    {
        foreach ($this as $offset => $value) {

            $eventTarget = $this->getClientEventTarget($offset);

            if ($value instanceof self) {
                $value->fillEvents($events);
                continue;
            } elseif ($value instanceof Traversable) {
                $eventData = $this->traversableToArray($value);
            } else {
                $eventData = $value;
            }

            $events[] = array($eventTarget, $eventData);
        }

        return $events;
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
//            } elseif (is_string($v)) {
//                $v = htmlspecialchars($v);
            }
            $a[$k] = $v;
        }
        return $a;
    }

    private function extractLanguageCatalogList(Nethgui_Core_ModuleInterface $module)
    {
        $languageCatalogList = array();

        do {
            if ($module instanceof Nethgui_Core_LanguageCatalogProvider) {
                $catalog = $module->getLanguageCatalog();
                if (is_array($catalog)) {
                    $languageCatalogList = array_merge($languageCatalogList, $catalog);
                } else {
                    $languageCatalogList[] = $catalog;
                }
            }

            $module = $module->getParent();
        } while ( ! is_null($module));

        return $languageCatalogList;
    }

    public function translate($value, $args = array(), $hsc = TRUE)
    {
        if ( ! isset($this->languageCatalogList)) {
            $this->languageCatalogList = $this->extractLanguageCatalogList($this->getModule());
        }
        return Nethgui_Framework::getInstance()->translate($value, $args, NULL, $this->languageCatalogList);
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

    public function getControlName($parts = '')
    {
        $nameSegments = $this->realPath($parts);
        $prefix = array_shift($nameSegments); // the first segment is a special one
        return $prefix . '[' . implode('][', $nameSegments) . ']';
    }

    private function realPath($name)
    {
        if (is_array($name)) {
            // ensure the $name argument is a string in the form of ../segment1/../segment2/..
            $name = implode('/', $name);
        }

        if (strlen($name) > 0 && $name[0] == '/') {
            // if the first character is a / consider an absolute path
            $nameSegments = array();
        } else {
            // else consider a path relative to the current module
            $nameSegments = $this->getModulePath();
        }

        // split the path into its parts
        $parts = explode('/', $name);

        foreach ($parts as $part) {
            if ($part == '') {
                continue; // skip empty parts
            } elseif ($part == '..') {
                array_pop($nameSegments); // backreference
            } else {
                $nameSegments[] = $part; // add segment
            }
        }

        return $nameSegments;
    }



}