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
    /**
     *
     * @var ModuleAggregationInterface
     */
    private $moduleBag;

    private function initialize()
    {
        $this->includeInterfaces();
        $this->includeClasses();

        $this->load->helper('url');
        $this->load->helper('form');
        $this->load->model("local_system_configuration", "systemConfiguration");
        $this->load->model("component_depot", "componentDepot");

        $this->moduleBag = $this->componentDepot->getModuleBag();
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
            'StandardModule',
            'FormPanel',
            'PermissivePolicyDecisionPoint',
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
            $this->currentModule = $this->moduleBag->findModule('Dummy1Module');
        }
        else
        {
            $this->currentModule = $this->moduleBag->findModule($method);

            /*
             * TODO : CLEANUP

              $this->componentDepot->activate($method);
             *
             */
        }

        if (is_null($this->currentModule)
                OR ! $this->currentModule instanceof TopModuleInterface)
        {
            show_404();
        }               

        $this->dispatchCommands($_POST);

        $decoration_parameters = array(
            'css_main' => base_url() . 'css/main.css',
            'module_content' => $this->currentModule->render(),
            'module_menu' => $this->renderModuleMenu($this->moduleBag->getTopModules()),
            'breadcrumb_menu' => $this->renderBreadcrumbMenu(),
        );

        $this->load->view('decoration.php', $decoration_parameters);
    }

    private function dispatchCommands($data)
    {
        $moduleIdentifier = $this->currentModule->getIdentifier();

        $this->currentModule->initialize();

        /*
          if (isset($data[$moduleIdentifier]) && is_array($data[$moduleIdentifier]))
          {
          foreach (array_keys($data[$moduleIdentifier]) as $panelIdentifier)
          {
          $panel = $this->panelBag->findPanel($panelIdentifier);
          if (is_null($panel))
          {
          continue;
          }
          $panel->bind($data[$moduleIdentifier][$panelIdentifier]);
          }
          }
         */
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
            $module = $this->moduleBag->findModule($module->getParentMenuIdentifier());
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

