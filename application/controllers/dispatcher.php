<?php

class Dispatcher extends CI_Controller {

    /**
     * @var ModuleLoaderInterface
     */
    public $moduleLoader;
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
        $this->load->model("module_loader", "moduleLoader");

        if ($method == 'index')
        {
            $this->currentModule = $this->moduleLoader->findRootModule();
        }
        else
        {
            try
            {
                $this->currentModule = $this->moduleLoader->findModule($method);
            }
            catch (Exception $e)
            {
                // TODO: log a debug message
                show_404();
            }
        }

        // TODO: bind panel parameters and validate.
        $moduleMenu = $this->renderMenu($this->moduleLoader->findRootModule());
        $moduleContent = $this->renderContent($this->currentModule);

        $decoration_parameters = array(
            'css_main' => base_url() . 'css/main.css',
            'module_menu' => $moduleMenu,
            'module_content' => $moduleContent,
            'breadcrumb_menu' => htmlspecialchars('Bread > Crumb > Menu'),
        );

        $this->load->view('decoration.php', $decoration_parameters);
    }

    /**
     *
     * @param ModuleInterface $rootModule
     * @return string
     */
    private function renderMenu(ModuleInterface $module, $level = 0)
    {
        if ($level > 9)
            throw new Exception("Recursion error");

        $output = htmlspecialchars($module->getTitle());

        if (strlen($output) > 0)
        {
            $output = '<div class="moduleTitle">' . $output . '</out>';
        }

        if ($module instanceof ModuleCompositeInterface)
        {
            $childOutput = '';
            foreach ($module->getChildren() as $child)
            {
                $childOutput .= '<li>' . $this->renderMenu($child, $level + 1) . '</li>';
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

}

