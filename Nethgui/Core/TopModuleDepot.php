<?php
/**
 * @package Core
 */

/**
 * TopModuleDepot
 *
 * TODO: rename this class
 * 
 * Responsibility:
 *    - creating instances of "Top" Modules
 *    - accessing  "Top" Modules
 *    - filtering access to "Top" Modules depending on User's credentials
 *
 * @package Core
 */
class Nethgui_Core_TopModuleDepot implements Nethgui_Core_ModuleSetInterface, Nethgui_Authorization_PolicyEnforcementPointInterface, Nethgui_Log_LogConsumerInterface
{

    /**
     * @var array
     */
    private $modules = array();
    private $menu = array();
    private $categories = array();

    /**
     * Policy Decision Point is applied to all attached modules and panels
     * that implement PolicyEnforcementPointInterface.
     *
     * @var PolicyDecisionPointInterface
     */
    private $policyDecisionPoint;

    /**
     * @var Nethgui_Client_UserInterface
     */
    private $user;

    /**
     * @var Nethgui_System_PlatformInterface
     */
    private $platform;



    public function __construct(Nethgui_System_PlatformInterface $platform, Nethgui_Client_UserInterface $user)
    {
        $this->platform = $platform;
        $this->user = $user;
        $this->createTopModules();
    }

    /**
     * Include all files under `libraries/module/` directory
     * ending with `Module.php` and create an instance
     * for each Module class.
     */
    private function createTopModules()
    {
        $moduleDir = realpath(implode('/', array(NETHGUI_ROOTDIR, NETHGUI_APPLICATION, 'Module')));
        $directoryIterator = new DirectoryIterator($moduleDir);
        foreach ($directoryIterator as $element) {
            if (substr($element->getFilename(), -4) == '.php') {

                $className = NETHGUI_APPLICATION . '_Module_' . substr($element->getFileName(), 0, -4);

                $classReflector = new ReflectionClass($className);

                if ($classReflector->isInstantiable()
                    && $classReflector->implementsInterface("Nethgui_Core_TopModuleInterface")
                ) {
                    $module = $this->createModule($className);
                    $this->registerModule($module);
                } else {
                    // TODO: log a warning+
                }
            }
        }
    }

    /**
     * Create an instance of $className, checking for valid identifier.
     *
     * @param string $className
     * @return ModuleInterface
     */
    private function createModule($className)
    {
        $module = new $className();

        if ( ! $module instanceof Nethgui_Core_ModuleInterface) {
            throw new Exception("Class `{$className}` must implement Nethgui_Core_ModuleInterface");
        }

        if (is_null($module->getIdentifier())) {
            throw new Exception("Each module must provide an unique identifier.");
        }

        $module->setPlatform($this->platform);

        if (NETHGUI_ENVIRONMENT == 'development') {
            $this->getLog()->notice("Created `" . $module->getIdentifier() . "`, as `{$className}` instance.", 'debug');
        }

        return $module;
    }

    /**
     * Adds $module to this set. Each member of this set
     * shares the same Policy Decision Point.
     * @param Nethgui_Core_ModuleInterface $module The module to be attached to this set.
     */
    public function registerModule(Nethgui_Core_ModuleInterface $module)
    {
        if (isset($this->modules[$module->getIdentifier()])) {
            throw new Exception("Module id `" . $module->getIdentifier() . "` is already registered.");
        }

        $this->modules[$module->getIdentifier()] = $module;

        if ($module instanceof Nethgui_Core_TopModuleInterface) {
            $parentId = $module->getParentMenuIdentifier();

            # if category is NULL, create the category
            if (is_null($parentId[0])) {
                $this->categories[$parentId[1] . $module->getIdentifier()] = $module->getIdentifier();
            } else { #otherwise insert into the menu according to menu and index 
                $this->menu[$parentId[0]][$parentId[1]] = $module->getIdentifier();
            }
        }
    }

    /**
     * Use $pdp as Policy Decision Point for each member of the set
     * that implements PolicyEnforcementPointInterface.
     * @param Nethgui_Authorization_PolicyDecisionPointInterface $pdp
     * @return void
     */
    public function setPolicyDecisionPoint(Nethgui_Authorization_PolicyDecisionPointInterface $pdp)
    {
        $this->policyDecisionPoint = $pdp;

        foreach ($this->modules as $module) {
            if ($module instanceof Nethgui_Authorization_PolicyEnforcementPointInterface) {
                $module->setPolicyDecisionPoint($pdp);
            }
        }
    }

    public function getPolicyDecisionPoint()
    {
        return $this->policyDecisionPoint;
    }

    /**
     *
     * @return RecursiveIterator
     */
    public function getModules()
    {
        // TODO: authorize access
        ksort($this->categories);

        foreach ($this->menu as $cat => $sub_menu) {
            ksort($sub_menu, SORT_NUMERIC);
            $this->menu[$cat] = $sub_menu;
        }

        foreach ($this->categories as $cat) {
            $ret['__ROOT__'][] = $cat;
            if (isset($this->menu[$cat])) {
                $ret[$cat] = array_values($this->menu[$cat]);
            }
        }
        return new Nethgui_Core_ModuleMenuIterator($this, '__ROOT__', $ret);
    }

    /**
     * @return Nethgui_Core_ModuleInterface
     */
    public function findModule($moduleIdentifier)
    {
        if (is_null($moduleIdentifier)
            OR ! isset($this->modules[$moduleIdentifier])) {
            return NULL;
        }
        return $this->modules[$moduleIdentifier];
    }

    public function setLog(Nethgui_Log_AbstractLog $log)
    {
        throw new Exception(sprintf('Cannot invoke setLog() on %s', get_class($this)));
    }

    public function getLog()
    {
        if ($this->platform instanceof Nethgui_Log_LogConsumerInterface) {
            return $this->platform->getLog();
        }

        return NULL;
    }

}

