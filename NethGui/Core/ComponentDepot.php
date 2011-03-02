<?php
/**
 * NethGui
 *
 * @package NethGuiFramework
 */

/**
 * ComponentDepot
 *
 * TODO: rename this class
 * 
 * Responsibility:
 *    - creating instances of "Top" Modules
 *    - accessing  "Top" Modules
 *    - filtering access to "Top" Modules depending on User's credentials
 *
 * @package NethGuiFramework
 * @subpackage StandardImplementation
 */
final class ComponentDepot implements ModuleSetInterface, PolicyEnforcementPointInterface {

    /**
     * @var array
     */
    private $modules = array();
    private $menu = array();
    /**
     * Policy Decision Point is applied to all attached modules and panels
     * that implement PolicyEnforcementPointInterface.
     *
     * @var PolicyDecisionPointInterface
     */
    private $policyDecisionPoint;

    /**
     * @var UserInterface
     */
    private $user;

    /**
     * @var HostConfigurationInterface
     */
    private $hostConfiguration;

    public function __construct(HostConfigurationInterface $hostConfiguration)
    {
        $this->hostConfiguration = $hostConfiguration;
        $this->createTopModules();
    }

    /**
     * Include all files under `libraries/module/` directory
     * ending with `Module.php` and create an instance
     * for each Module class.
     */
    private function createTopModules()
    {
        // TODO: set a configuration parameter for modules directory
        $directoryIterator = new DirectoryIterator(dirname(__FILE__) . '/../Module');
        foreach ($directoryIterator as $element)
        {
            if (substr($element->getFilename(), -10) == 'Module.php')
            {
                // Filename OK. Include it.
                require_once($element->getPathname());

                $className = substr($element->getFileName(), 0, -4);

                $classReflector = new ReflectionClass($className);

                if ($classReflector->isInstantiable() && $classReflector->implementsInterface("TopModuleInterface"))
                {
                    $module = $this->createModule($className);
                    $this->registerModule($module);
                }
                else
                {
                    // TODO: log a warning
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

        if ( ! $module instanceof ModuleInterface)
        {
            throw new Exception("Class `{$className}` must implement ModuleInterface");
        }

        if (is_null($module->getIdentifier()))
        {
            throw new Exception("Each module must provide an unique identifier.");
        }

        $module->setHostConfiguration($this->hostConfiguration);

        log_message('debug', "Created `" . $module->getIdentifier() . "`, as `{$className}` instance.");
      
        return $module;
    }

    /**
     * Adds $module to this set. Each member of this set
     * shares the same Policy Decision Point.
     * @param ModuleInterface $module The module to be attached to this set.
     */
    private function registerModule(ModuleInterface $module)
    {
        if(isset($this->modules[$module->getIdentifier()]))
        {
            throw new Exception("Module id `" . $module->getIdentifier() ."` is already registered.");
        }

        $this->modules[$module->getIdentifier()] = $module;

        if ($module instanceof TopModuleInterface)
        {
            $parentId = $module->getParentMenuIdentifier();
            if (is_null($parentId))
            {
                $parentId = '__ROOT__';
            }
            $this->menu[$parentId][] = $module->getIdentifier();
        }
    }    

    /**
     * Use $pdp as Policy Decision Point for each member of the set
     * that implements PolicyEnforcementPointInterface.
     * @param PolicyDecisionPointInterface $pdp
     * @return void
     */
    public function setPolicyDecisionPoint(PolicyDecisionPointInterface $pdp)
    {
        $this->policyDecisionPoint = $pdp;

        foreach ($this->modules as $module)
        {
            if ($module instanceof PolicyEnforcementPointInterface)
            {
                $module->setPolicyDecisionPoint($pdp);
            }
        }
    }

    public function getPolicyDecisionPoint()
    {
        return $this->policyDecisionPoint;
    }

    public function setUser(UserInterface $user)
    {
        $this->user = $user;
    }

    public function getTopModules()
    {
        // TODO: authorize access
        return new ModuleMenuIterator($this, '__ROOT__', $this->menu);
    }

    /**
     * @return ModuleInterface
     */
    public function findModule($moduleIdentifier)
    {
        if (is_null($moduleIdentifier)
                OR ! isset($this->modules[$moduleIdentifier]))
        {
            return NULL;
        }
        return $this->modules[$moduleIdentifier];
    }

}

