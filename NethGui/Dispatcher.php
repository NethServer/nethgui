<?php

final class NethGui_Dispatcher
{

    /**
     * Model for getting components (Modules, Panels) from file system.
     * @var ComponentDepot
     */
    public $componentDepot;
    /**
     * Model for changing host system configuration.
     * @var HostConfigurationInterface
     */
    public $hostConfiguration;
    /**
     * @var ModuleInterface
     */
    private $currentModule;

    /**
     *
     * @param CI_Controller $controller 
     */
    public function __construct(CI_Controller $controller)
    {
        $this->controller = $controller;
        $this->initialize();

    }

    private function initialize()
    {
        $this->includeArtifacts();

        /*
         * Create models.
         */
        $this->hostConfiguration = new NethGui_Core_MockHostConfiguration();
        $this->componentDepot = new NethGui_Core_ComponentDepot($this->hostConfiguration);

        /*
         * TODO: enforce some security policy on Models
         */
        $this->hostConfiguration->setPolicyDecisionPoint(new NethGui_Authorization_PermissivePolicyDecisionPoint());
        $this->componentDepot->setPolicyDecisionPoint(new NethGui_Authorization_PermissivePolicyDecisionPoint());

    }

    private function includeArtifacts()
    {
        $classNames = array(
            'Authorization/AccessControlRequestInterface',
            'Authorization/AccessControlResponseInterface',
            'Authorization/PolicyDecisionPointInterface',
            'Authorization/PolicyEnforcementPointInterface',
            'Core/HostConfigurationInterface',
            'Core/ModuleCompositeInterface',
            'Core/ModuleInterface',
            'Core/ModuleSetInterface',
            'Core/RequestInterface',
            'Core/UserInterface',
            'Core/ValidationReportInterface',
            'Authorization/AccessControlRequest',
            'Authorization/AccessControlResponse',
            'Core/AlwaysAuthenticatedUser',
            'Core/MockHostConfiguration',
            'Core/ModuleMenuIterator',
            'Core/ComponentDepot',
            'Core/Request',
            'Core/Response',
            'Core/ValidationReport',
            'Authorization/PermissivePolicyDecisionPoint',
            'Core/StandardModule',
            'Core/StandardModuleComposite',
            'Core/FormModule',
            'Core/ContainerModule',
        );

        foreach ($classNames as $className) {
            require_once($className . '.php');
        }

    }

    /**
     * Code Igniter entry point
     *
     * @param string $method
     * @param array $parameters
     */
    public function main($method, $parameters = array())
    {
        /*
         * Find current module
         */
        if ($method == 'index') {
// TODO: take the default module value from the configuration
            $this->currentModule = $this->componentDepot->findModule('NethGui_Module_SecurityModule');
        } else {
            $this->currentModule = $this->componentDepot->findModule($method);
        }

        if (is_null($this->currentModule)
            OR ! $this->currentModule instanceof NethGui_Core_TopModuleInterface
        ) {
            show_404();
        }

        $request = NethGui_Core_Request::createInstanceFromServer($this->currentModule->getIdentifier());

        $this->hostConfiguration->setUser($request->getUser());
        $this->componentDepot->setUser($request->getUser());

        $this->dispatch($request);

        // Default response view type: HTML
        $responseType = NethGui_Core_Response::HTML;

        /*
         * A first parameter ending with `.js` or `.css` triggers 
         * alternate response types.
         */
        if (count($parameters) === 1) {
            $resourceName = $parameters[0];
            if (substr($resourceName, -3) == '.js') {
                $responseType = NethGui_Core_Response::JS;
            } elseif (substr($resourceName, -4) == '.css') {
                $responseType = NethGui_Core_Response::CSS;
            }
        }

        $response = new NethGui_Core_Response($responseType);

        if ($response->getViewType() === NethGui_Core_Response::HTML) {
            $decorationParameters = array(
                'css_main' => base_url() . 'css/main.css',
                'module_content' => $this->currentModule->renderView($response),
                'module_menu' => $this->renderModuleMenu($this->componentDepot->getTopModules()),
                'breadcrumb_menu' => $this->renderBreadcrumbMenu(),
            );

            header("Content-Type: text/html; charset=UTF-8");
            $this->controller->load->view('../../NethGui/Core/View/decoration.php', $decorationParameters);
        } elseif ($response->getViewType() === NethGui_Core_Response::JS) {
            // What's the correct mime-type for js?
            header("Content-Type: application/x-javascript; charset=UTF-8");
            return $this->currentModule->renderView($response);
        } elseif ($response->getViewType() === NethGui_Core_Response::CSS) {
            // What's the correct mime-type for js?
            header("Content-Type: text/css; charset=UTF-8");
            return $this->currentModule->renderView($response);
        }

    }

    /**
     * Dispatch $request to top modules.
     * @param NethGui_Core_RequestInterface $parameters
     * @return Response
     */
    private function dispatch(NethGui_Core_RequestInterface $request)
    {
        $validationReport = new NethGui_Core_ValidationReport();

        foreach ($request->getParameters() as $moduleIdentifier) {
            $module = $this->componentDepot->findModule($moduleIdentifier);

            if (is_null($module)) {
                continue;
            }

            if ( ! $module->isInitialized()) {
                $module->initialize();
            }

            $module->bind($request->getParameterAsInnerRequest($moduleIdentifier));

            $module->validate($validationReport);

            if (count($validationReport->getErrors()) == 0) {
                $module->process();
            }
        }

    }

    private function renderBreadcrumbMenu()
    {
        $module = $this->currentModule;

        $rootLine = array();

        while ( ! is_null($module)
        && $module instanceof NethGui_Core_TopModuleInterface
        ) {
            $rootLineElement = $this->renderModuleAnchor($module);
            if (strlen($rootLineElement) > 0) {
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
        if ($level > 4) {
            return '';
        }

        $output = '';

        $menuIterator->rewind();

        while ($menuIterator->valid()) {
            $output .= '<li><div class="moduleTitle">' . $this->renderModuleAnchor($menuIterator->current()) . '</div>';

            if ($menuIterator->hasChildren()) {
                $output .= $this->renderModuleMenu($menuIterator->getChildren(), $level + 1);
            }

            $output .= '</li>';

            $menuIterator->next();
        }

        return '<ul>' . $output . '</ul>';

    }

    private function renderModuleAnchor(NethGui_Core_ModuleInterface $module)
    {
        $html = '';

        if (strlen($module->getTitle()) == 0) {
            return '';
        }

        if ($module === $this->currentModule) {
            $html = '<span class="moduleTitle current" title="' . htmlspecialchars($module->getDescription()) . '">' . htmlspecialchars($module->getTitle()) . '</span>';
        } else {
            $html = anchor(strtolower(get_class($this->controller)) . '/' . $module->getIdentifier(), htmlspecialchars($module->getTitle()), array('class' => 'moduleTitle', 'title' => htmlspecialchars($module->getDescription())));
        }

        return $html;

    }

}
