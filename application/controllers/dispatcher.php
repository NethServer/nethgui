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

    public function _remap($method, $parameters = array())
    {
        if ($method == 'phpinfo')
        {
            phpinfo();
            return;
        }

        $this->includeInterfaces();

        $this->load->helper('url');
        $this->load->model("local_system_configuration", "systemConfiguration");
        $this->load->model("module_loader", "moduleAggregation");

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

// TODO: bind panel parameters and validate.
        $moduleMenu = $this->renderModuleMenu($this->moduleAggregation->findRootModule());
        $moduleContent = $this->renderContent($this->currentModule);

        $decoration_parameters = array(
            'css_main' => base_url() . 'css/main.css',
            'module_menu' => $moduleMenu,
            'module_content' => $moduleContent,
            'breadcrumb_menu' => $this->renderBreadcrumbMenu(),
        );

        $this->load->view('decoration.php', $decoration_parameters);
    }

    private function renderBreadcrumbMenu()
    {
        $module = $this->currentModule;

        $rootLine = array();

        do
        {
            $rootLine[] = $this->renderModuleAnchor($module);

            $module = $this->moduleAggregation->findModule($module->getParentIdentifier());
        }
        while(!is_null($module));

        $rootLine[] = 'Home';

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

    private function renderContent(ModuleInterface $currentModule)
    {
        $output = '';
        foreach ($currentModule->getPanels() as $panel)
        {
            if ($panel instanceof PanelInterface)
            {

                $output .= $panel->render();
            }
        }
        return $output;
    }

    private function renderModuleAnchor(ModuleInterface $module)
    {
        $html = '';

        if(strlen($module->getTitle()) == 0)
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

