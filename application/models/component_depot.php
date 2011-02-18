<?php

final class Component_depot extends CI_Model implements ModuleAggregationInterface, PolicyEnforcementPointInterface, PanelAggregationInterface {

    /**
     * @var array
     */
    private $modules = array();
    /**
     * @var array
     */
    private $panels = array();
    /**
     * Policy Decision Point is applied to all attached modules and panels
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
                    // TODO: log a warning
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
     * @
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
     * The Client asks for activation of a certain Module
     * if it wishes to use its Panel.
     *
     * @param string $moduleIdentifier
     */
    public function activate($moduleIdentifier)
    {
        $module = $this->findModule($moduleIdentifier);
        if (is_null($module))
        {
            throw new Exception("Could not activate `{$moduleIdentifier}` module.");
        }

        // Iterates into panel composite structure to find all its descendants.
        $panels = array($module->getPanel());
        while (count($panels) > 0 && $panels[0] instanceof PanelInterface)
        {
            $panel = array_shift($panels);

            $this->attachPanel($panel);

            if ($panel instanceof PanelCompositeInterface)
            {
                $panels = array_merge($panels, $panel->getChildren());
            }
        }
    }

    /**
     * @return ModuleAggregationInterface
     */
    public function getModuleBag ()
    {
        // TODO: hive off ModuleAggregationInterface
        return $this;
    }

    /**
     *
     * @return PanelAggregationInterface
     */
    public function getPanelBag()
    {
         // TODO: hive off PanelAggregationInterface
        return $this;
    }

    /**
     * Use $pdp as Policy Decision Point for each member of the aggregation
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

    /**
     * Adds $module to this aggregation. Each member of this aggregation
     * shares the same Policy Decision Point.
     * @param ModuleInterface $module The module to attach to this aggregation
     */
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

    public function attachPanel(PanelInterface $panel)
    {
        $this->panels[$panel->getIdentifier()] = $panel;
        if ($panel instanceof PolicyEnforcementPointInterface)
        {
            $panel->setPolicyDecisionPoint($this->policyDecisionPoint);
        }
    }

    public function findPanel($panelIdentifier)
    {
        return $this->panels[$panelIdentifier];
    }

}

/**
 * Modules without a parent are attached to RootModule by default.
 */
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