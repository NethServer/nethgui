<?php

class Dispatcher extends CI_Controller {

    /**
     * @var moduleAggregationInterface
     */
    public $moduleAggregation;
    /**
     * @var SystemConfigurationInterface
     */
    public $systemConfiguration;
    /**
     * @var ModuleInterface
     */
    private $currentModule;

    /**
     * Load all `*Interface.php` files under `APPPATH/libraries/interface/`
     * directory.
     */
    private function includeInterfaces()
    {
        $directoryIterator = new DirectoryIterator(APPPATH . 'libraries/interface');
        foreach ($directoryIterator as $element)
        {
            if (substr($element->getFilename(), -13) == 'Interface.php')
            {
                require_once($element->getPathname());
            }
        }
    }

    private function includeClasses()
    {
        $classNames = array(
            'StandardModule',
            'StandardPanel',
            'FormPanel',
            'PermissivePolicyDecisionPoint',
            'panel/Dummy1Panel'
        );

        foreach ($classNames as $className)
        {
            require_once(APPPATH . 'libraries/' . $className . '.php');
        }
    }

    public function _remap($method, $parameters = array())
    {
        if ($method == 'phpinfo')
        {
            phpinfo();
            return;
        }

        $this->includeInterfaces();
        $this->includeClasses();

        $this->load->helper('url');
        $this->load->helper('form');
        $this->load->model("local_system_configuration", "systemConfiguration");
        $this->load->model("module_loader", "moduleAggregation");

        /*
         * TODO: enforce some security policy
         */
        foreach (array($this->moduleAggregation, $this->systemConfiguration) as $pep)
        {
            if ($pep instanceof PolicyEnforcementPointInterface)
            {
                $pep->setPolicyDecisionPoint(new PermissivePolicyDecisionPoint());
            }
        }

        /*
         * Module routing
         */
        if ($method == 'index')
        {
            $this->currentModule = $this->moduleAggregation->findRootModule();
        }
        else
        {
            try
            {
                $this->currentModule = $this->moduleAggregation->findModule($method);
            }
            catch (Exception $e)
            {
                // TODO: log a debug message
                show_404();
            }
        }

        $decoration_parameters = array(
            'css_main' => base_url() . 'css/main.css',
            'module_content' => $this->renderModulePanel(),
            'module_menu' => $this->renderModuleMenu($this->moduleAggregation->findRootModule()),
            'breadcrumb_menu' => $this->renderBreadcrumbMenu(),
        );

        $this->load->view('decoration.php', $decoration_parameters);
    }

    private function renderModulePanel()
    {
        $panel = $this->currentModule->getPanel();

        if ($panel instanceof PanelInterface)
        {
            if (isset($_POST[$panel->getIdentifier()]))
            {
                $panel->bind($_POST[$panel->getIdentifier()]);

                // TODO: validation errors handling
                $validate = $panel->validate();
            }
            $output = $panel->render();
        }
        else
        {
            $output = '';
        }

        return $output;
    }

    private function renderBreadcrumbMenu()
    {
        $module = $this->currentModule;

        $rootLine = array();

        do
        {
            $rootLineElement = $this->renderModuleAnchor($module);
            if (strlen($rootLineElement) > 0)
            {
                $rootLine[] = $rootLineElement;
            }
            $module = $this->moduleAggregation->findModule($module->getParentIdentifier());
        }
        while ( ! is_null($module));

        $rootLine[] = anchor('', 'Home');

        $rootLine = array_reverse($rootLine);

        return implode(' : ', $rootLine);
    }

    /**
     *
     * @param ModuleInterface $rootModule
     * @return string
     */
    private function renderModuleMenu(ModuleInterface $module, $level = 0)
    {
        if ($level > 9)
            throw new Exception("Recursion error");

        $output = $this->renderModuleAnchor($module);

        if (strlen($output) > 0)
        {
            $output = '<div class="moduleTitle">' . $output . '</out>';
        }

        if ($module instanceof ModuleCompositeInterface)
        {
            $childOutput = '';
            foreach ($module->getChildren() as $child)
            {
                $childOutput .= '<li>' . $this->renderModuleMenu($child, $level + 1) . '</li>';
            }

            if (strlen($childOutput) > 0)
            {
                $output .= '<ul>' . $childOutput . '</ul>';
            }
        }

        return $output;
    }

    private function renderModuleAnchor(ModuleInterface $module)
    {
        $html = '';

        if (strlen($module->getTitle()) == 0)
        {
            return '';
        }

        if ($module === $this->currentModule)
        {
            $html = '<span class="moduleTitle">' . htmlspecialchars($module->getTitle()) . '</span>';
        }
        else
        {
            $html = anchor(strtolower(get_class($this)) . '/' . $module->getIdentifier(), htmlspecialchars($module->getTitle()));
        }

        return $html;
    }

}

