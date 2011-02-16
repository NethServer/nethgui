<?php

final class Module_loader extends CI_Model implements ModuleAggregationInterface, PolicyEnforcementPointInterface {

    private $modules = array();
    /**
     * Policy Decision Point is applied to all attached modules
     * that implement PolicyEnforcementPointInterface.
     *
     * @var PolicyDecisionPointInterface
     */
    private $policyDecisionPoint;

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
                    $module = $this->createInstance($className);
                    $this->modules[$module->getIdentifier()] = $module;
                }
                else
                {
                    // TODO: log warning
                }
            }
        }

        foreach ($this->modules as $moduleIdentifier => $module)
        {
            if ($module !== $this->findRootModule())
            {
                $this->attachModule($module);
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
        $module = new $className();

        if ( ! $module instanceof ModuleInterface)
        {
            throw new Exception("Class `{$className}` must implement ModuleInterface");
        }

        if (is_null($module->getIdentifier()))
        {
            throw new Exception("Each module must provide an unique identifier.");
        }

        return $module;
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
     * Use $pdp as Policy Decision Point for each member of the aggregation
     * that implements PolicyEnforcementPointInterface.
     * @param PolicyDecisionPointInterface $pdp
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

        if (isset($this->policyDecisionPoint)
                && $module instanceof PolicyEnforcementPointInterface)
        {
            $module->setPolicyDecisionPoint($this->policyDecisionPoint);
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