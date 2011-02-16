<?php

final class Module_loader extends CI_Model implements ModuleAggregationInterface, PolicyEnforcementPointInterface {

    private $modules = array();

    public function __construct()
    {
        parent::__construct();
        $this->modules['__ROOT__'] = new RootModule('__ROOT__');
        $this->createInstances();
    }

    /**
     * Include all files under `libraries/module/` directory
     * ending with `Module.php` and create an instance
     * for each Module class.
     */
    private function createInstances()
    {
        $directoryIterator = new DirectoryIterator(APPPATH . 'libraries/module');
        foreach ($directoryIterator as $element)
        {
            if (substr($element->getFilename(), -10) == 'Module.php')
            {
                $className = substr($element->getFileName(), 0, -4);

                require_once($element->getPathname());

                if (class_exists($className))
                {
                    $moduleInstance = $this->createInstance($className);
                    $this->modules[$moduleInstance->getIdentifier()] = $moduleInstance;
                }
                else
                {
                    // TODO: log warning
                }
            }
        }

        foreach ($this->modules as $moduleIdentifier => $moduleInstance)
        {
            if ($moduleInstance !== $this->findRootModule())
            {
                $this->attachModule($moduleInstance);
            }
        }
    }

    /**
     * Create an instance of $className, checking for valid identifier.
     *
     * @param string $className
     * @return ModuleInterface
     */
    private function createInstance($className)
    {
        $moduleInstance = new $className();

        if ( ! $moduleInstance instanceof ModuleInterface)
        {
            throw new Exception("`{$moduleIdentifier}` must implement ModuleInterface");
        }

        if (is_null($moduleInstance->getIdentifier()))
        {
            throw new Exception("Each module must provide an unique identifier.");
        }

        return $moduleInstance;
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

    /**
     * TODO: setPolicyDecisionPoint implementation
     * @param PolicyDecisionPointInterface $pdp
     */
    public function setPolicyDecisionPoint(PolicyDecisionPointInterface $pdp)
    {

    }

    public function attachModule(ModuleInterface $module)
    {
        $parentModule = $this->findModule($module->getParentIdentifier());

        if (is_null($parentModule))
        {
            $this->findRootModule()->addChild($module);
        }
        elseif ($parentModule instanceof ModuleCompositeInterface)
        {
            $parentModule->addChild($module);
        }
        else
        {
            // TODO: write a better error message
            throw new Exception("Composition error");
        }
    }

    /**
     *
     * @return ModuleCompositeInterface
     */
    public function findRootModule()
    {
        return $this->findModule('__ROOT__');
    }

    public function __toString()
    {
        return $this->getTitle();
    }

}

final class RootModule extends StandardCompositeModule {

    public function getTitle()
    {
        return "";
    }

    public function getDescription()
    {
        return "";
    }

}