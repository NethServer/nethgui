<?php

final class Dispatcher extends CI_Controller {

    /**
     * Model for getting components (Modules, Panels) from file system.
     * @var Component_depot
     */
    public $componentDepot;
    /**
     * Model for changing host system configuration.
     * @var SystemConfigurationInterface
     */
    public $systemConfiguration;
    /**
     * @var ModuleInterface
     */
    private $currentModule;


    private function initialize()
    {
        $this->includeInterfaces();
        $this->includeClasses();

        $this->load->helper('url');
        $this->load->helper('form');
        $this->load->model("local_system_configuration", "systemConfiguration");
        $this->load->model("component_depot", "componentDepot");
    }

    /**
     * Loads all `*Interface.php` files under `APPPATH/libraries/interface/`
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
            'PermissivePolicyDecisionPoint',
            'StandardModule',
            'StandardModuleComposite',
            'ParameterDictionary',
            'ValidationReport',
            'FormModule',
            'ContainerModule',
        );

        foreach ($classNames as $className)
        {
            require_once(APPPATH . 'libraries/' . $className . '.php');
        }
    }

    /**
     * Code Igniter entry point
     *
     * @param string $method
     * @param array $parameters
     */
    public function _remap($method, $parameters = array())
    {
        if ($method == 'phpinfo')
        {
            phpinfo();
            return;
        }

        $this->initialize();

        /*
         * TODO: enforce some security policy on Models
         */
        foreach (array($this->componentDepot, $this->systemConfiguration) as $pep)
        {
            if ($pep instanceof PolicyEnforcementPointInterface)
            {
                $pep->setPolicyDecisionPoint(new PermissivePolicyDecisionPoint());
            }
        }


        /*
         * Find current module
         */
        if ($method == 'index')
        {
            // TODO: take the default module value from the configuration
            $this->currentModule = $this->componentDepot->findModule('Dummy1Module');
        }
        else
        {
            $this->currentModule = $this->componentDepot->findModule($method);
        }

        if (is_null($this->currentModule)
                OR ! $this->currentModule instanceof TopModuleInterface)
        {
            show_404();
        }


        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            $this->currentModule->initialize();
            $data = array();
        }
        elseif ($_SERVER['REQUEST_METHOD'] == 'POST')
        {
            if (isset($_SERVER['X_REQUESTED_WITH'])
                    && $_SERVER['X_REQUESTED_WITH'] == 'XMLHttpRequest')
            {
                // TODO: decode json query
                $data = array();
            }
            else
            {
                $data = $_POST;
            }
            
        }

        $this->dispatchCommands(new ParameterDictionary($data));

        $decoration_parameters = array(
            'css_main' => base_url() . 'css/main.css',
            'module_content' => '',
            'module_menu' => $this->renderModuleMenu($this->componentDepot->getTopModules()),
            'breadcrumb_menu' => $this->renderBreadcrumbMenu(),
        );

        $this->load->view('decoration.php', $decoration_parameters);
    }

    /**
     * TODO: 
     * @param ParameterDictionaryInterface $parameters
     * @return <type>
     */
    private function dispatchCommands(ParameterDictionaryInterface $parameters)
    {
        $validationReport = new ValidationReport();

        foreach ($parameters->getKeys() as $moduleIdentifier)
        {
            $module = $this->componentDepot->findModule($moduleIdentifier);
            if (is_null($module))
            {
                continue;
            }

            if ( ! $module->isInitialized())
            {
                $module->initialize();
            }

            $module->bind($parameters->getValueAsParameterDictionary($moduleIdentifier));
            $module->validate($validationReport);

            // TODO: call process()
        }
    }

    private function renderBreadcrumbMenu()
    {
        $module = $this->currentModule;

        $rootLine = array();

        while ( ! is_null($module) && $module instanceof TopModuleInterface)
        {
            $rootLineElement = $this->renderModuleAnchor($module);
            if (strlen($rootLineElement) > 0)
            {
                $rootLine[] = $rootLineElement;
            }
            $module = $this->componentDepot->findModule($module->getParentMenuIdentifier());
        }

        $rootLine = array_reverse($rootLine);

        // TODO: wrap into LI tag.
        return implode(' &gt; ', $rootLine);
    }

    /**
     *
     * @param RecursiveIterator $rootModule
     * @return string
     */
    private function renderModuleMenu(RecursiveIterator $menuIterator, $level = 0)
    {
        if ($level > 4)
        {
            return '';
        }

        $output = '';

        $menuIterator->rewind();

        while ($menuIterator->valid())
        {
            $output .= '<li><div class="moduleTitle">' . $this->renderModuleAnchor($menuIterator->current()) . '</div>';

            if ($menuIterator->hasChildren())
            {
                $output .= $this->renderModuleMenu($menuIterator->getChildren(), $level + 1);
            }

            $output .= '</li>';

            $menuIterator->next();
        }

        return '<ul>' . $output . '</ul>';
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
            $html = '<span class="moduleTitle current" title="' . htmlspecialchars($module->getDescription()) . '">' . htmlspecialchars($module->getTitle()) . '</span>';
        }
        else
        {
            $html = anchor(strtolower(get_class($this)) . '/' . $module->getIdentifier(), htmlspecialchars($module->getTitle()), array('class' => 'moduleTitle', 'title' => htmlspecialchars($module->getDescription())));
        }

        return $html;
    }

}

