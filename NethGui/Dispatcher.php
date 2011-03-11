<?php
/**
 * NethGui
 *
 * @package NethGuiFramework
 */

/**
 * NethGui_Dispatcher
 *
 * @package NethGuiFramework
 */
final class NethGui_Dispatcher
{

    /**
     * Model for getting components (Modules, Panels) from file system.
     * @var ComponentDepot
     */
    private $componentDepot;
    /**
     * Model for changing host system configuration.
     * @var HostConfigurationInterface
     */
    private $hostConfiguration;
    /**
     * @var ModuleInterface
     */
    private $currentModule;
    /**
     *
     * @var NethGui_Core_Response
     */
    private $response;

    /**
     *
     * @param CI_Controller $controller 
     */
    public function __construct()
    {
        /*
         * Create models.
         * TODO: get hostConfiguration and componentDepot clas names
         * from NethGui_Framework.
         */
        $this->hostConfiguration = new NethGui_Core_SMEHostConfiguration();
        $this->componentDepot = new NethGui_Core_ComponentDepot($this->hostConfiguration);

        /*
         * TODO: enforce some security policy on Models
         */
        $pdp = new NethGui_Authorization_PermissivePolicyDecisionPoint();

        $this->hostConfiguration->setPolicyDecisionPoint($pdp);
        $this->componentDepot->setPolicyDecisionPoint($pdp);
    }

    /**
     * Forwards control to Modules and creates output views.
     *
     * @param string $method
     * @param array $parameters
     */
    public function dispatch($method, $parameters = array())
    {
        /*
         * Find current module
         */
        if ($method == 'index') {
            // TODO: take the default module value from the configuration
            $this->currentModule = $this->componentDepot->findModule('Security');
        } else {
            $this->currentModule = $this->componentDepot->findModule($method);
        }

        if (is_null($this->currentModule)
            OR ! $this->currentModule instanceof NethGui_Core_TopModuleInterface
        ) {
            show_404();
        }

        $request = NethGui_Core_Request::getWebRequestInstance(
                $this->currentModule->getIdentifier(),
                $parameters
        );

        /**
         * Retrieve current User object from $request and set it on PEPs.
         */
        $this->hostConfiguration->setUser($request->getUser());
        $this->componentDepot->setUser($request->getUser());

        // Default response view type: HTML
        $responseType = $request->getContentType();

        /*
         * A first parameter ending with `.js` or `.css` triggers 
         * alternative response types (namely JS & CSS).
         */
        if (count($parameters) === 1) {
            $resourceName = $parameters[0];
            if (substr($resourceName, -3) == '.js') {
                $responseType = NethGui_Core_ResponseInterface::JS;
            } elseif (substr($resourceName, -4) == '.css') {
                $responseType = NethGui_Core_ResponseInterface::CSS;
            }
        }



        $response = NethGui_Core_Response::getRootInstance($responseType);


        // TODO: some refactoring...
        $validationReport = $this->handle($request, $response);
        $this->sendResponse($response, $validationReport);
    }

    private function sendResponse(NethGui_Core_Response $response, NethGui_Core_ValidationReport $validationReport)
    {
        if ($response->getFormat() === NethGui_Core_ResponseInterface::HTML) {
            header("Content-Type: text/html; charset=UTF-8");

            // TODO: implement menu, breadcrumb and validatorreport as Modules
            $moduleContent = NethGui_Framework::getInstance()->renderResponse($response->getInnerResponse($this->currentModule));

            $decorationParameters = array(
                'cssMain' => base_url() . 'css/main.css',
                'js' => array(
                    'base' => base_url() . 'js/jquery-1.5.1.min.js',
                    'ui' => base_url() . 'js/jquery-ui-1.8.10.custom.min.js',
                    'test' => base_url() . 'js/test.js',
                ),
                'moduleContent' => $moduleContent,
                'validationReport' => print_r($validationReport->getErrors(), 1),
                'moduleMenu' => $this->renderModuleMenu($this->componentDepot->getTopModules()),
                'breadcrumbMenu' => $this->renderBreadcrumbMenu(),
                'request' => 'xxx' //print_r($request, 1),
            );
            echo NethGui_Framework::getInstance()->renderView('NethGui_Core_View_decoration', $decorationParameters);
            //
        } elseif ($response->getFormat() === NethGui_Core_ResponseInterface::JSON) {
            header("Content-Type: application/json; charset=UTF-8");

            echo NethGui_Framework::getInstance()->renderView('NethGui_Core_View_json', array('data' => $response->getWholeData()));
            //

        } elseif ($response->getFormat() === NethGui_Core_ResponseInterface::JS) {
            header("Content-Type: application/x-javascript; charset=UTF-8");

            
            //
        } elseif ($response->getFormat() === NethGui_Core_ResponseInterface::CSS) {
            header("Content-Type: text/css; charset=UTF-8");
            //
        }
    }

    /**
     * Dispatch $request to top modules.
     * @param NethGui_Core_RequestInterface $parameters
     * @return Response
     */
    private function handle(NethGui_Core_RequestInterface $request, NethGui_Core_ResponseInterface $response)
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

            if (count($validationReport->getErrors()) > 0) {
                continue;
            }


            $module->process($response->getInnerResponse($module));
        }

        // TODO: attach validationReport to a "ValidationReportModule"
        return $validationReport;
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

    /**
     * @see anchor()
     * @param NethGui_Core_ModuleInterface $module
     * @return <type>
     */
    private function renderModuleAnchor(NethGui_Core_ModuleInterface $module)
    {
        $html = '';

        if (strlen($module->getTitle()) == 0) {
            return '';
        }

        if ($module === $this->currentModule) {
            $html = '<span class="moduleTitle current" title="' . htmlspecialchars($module->getDescription()) . '">' . htmlspecialchars($module->getTitle()) . '</span>';
        } else {
            $ciControllerClassName = NethGui_Framework::getInstance()->getControllerName();
            $html = anchor($ciControllerClassName . '/' . $module->getIdentifier(),
                    htmlspecialchars($module->getTitle()),
                    array('class' => 'moduleTitle', 'title' => htmlspecialchars($module->getDescription())
                    )
            );
        }

        return $html;
    }

}
