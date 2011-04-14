<?php
/**
 * @package NethGui
 */

/**
 * @package NethGui
 */
final class NethGui_Dispatcher
{

    /**
     * Model for getting components (Modules, Panels) from file system.
     * @var NethGui_Core_TopModuleDepot
     */
    private $topModuleDepot;
    /**
     * Model for changing host system configuration.
     * @var HostConfigurationInterface
     */
    private $hostConfiguration;
    /**
     * @var NethGui_Core_ModuleInterface
     */
    private $currentModule;

    /**
     * Forwards control to Modules and creates output views.
     *
     * @param string $method
     * @param array $parameters
     */
    public function dispatch($method, $parameters = array())
    {
        if ($method == 'index') {
            $method = 'Security';
        }


        $request = NethGui_Core_Request::getWebRequestInstance(
                $method,
                $parameters
        );

        $user = $request->getUser();

        /*
         * Create models.
         *
         * TODO: get hostConfiguration and topModuleDepot class names
         * from NethGui_Framework.
         */
        $this->hostConfiguration = new NethGui_Core_HostConfiguration($user);
        $this->topModuleDepot = new NethGui_Core_TopModuleDepot($this->hostConfiguration, $user);

        /*
         * TODO: enforce some security policy on Models
         */
        $pdp = new NethGui_Authorization_PermissivePolicyDecisionPoint();

        $this->hostConfiguration->setPolicyDecisionPoint($pdp);
        $this->topModuleDepot->setPolicyDecisionPoint($pdp);

        /*
         * Find current module
         */
        $this->currentModule = $this->topModuleDepot->findModule($method);


        if (is_null($this->currentModule)
            OR ! $this->currentModule instanceof NethGui_Core_TopModuleInterface
        ) {
            show_404();
        }



        $worldModule = new NethGui_Core_Module_World($this->currentModule);

        $report = new NethGui_Core_ValidationReport();

        $view = new NethGui_Core_View($worldModule);

        if ($request->getContentType() === NethGui_Core_RequestInterface::CONTENT_TYPE_HTML) {
            $worldModule->addChild(new NethGui_Core_Module_Menu($this->topModuleDepot->getModules()));
            $worldModule->addChild(new NethGui_Core_Module_BreadCrumb($this->topModuleDepot, $this->currentModule));
        }

        $worldModule->addChild(new NethGui_Core_Module_ValidationReport($report));

        $moduleActivationList = $request->getParameters();

        foreach ($moduleActivationList as $moduleIdentifier) {
            $module = $this->topModuleDepot->findModule($moduleIdentifier);
            $worldModule->addChild($module);
        }

        $worldModule->initialize();

        $worldModule->bind($request);
        $worldModule->validate($report);

        try {
            $worldModule->process();
        } catch (NethGui_Exception_Process $e) {
            NethGui_Framework::logMessage($e->getMessage(), 'error');
            $view['Exception'] = $e->getMessage();
            $view['StackTrace'] = $e->getTrace();
        }

        if ($request->getContentType() === NethGui_Core_RequestInterface::CONTENT_TYPE_HTML) {
            header("Content-Type: text/html; charset=UTF-8");
            $worldModule->prepareView($view, NethGui_Core_ModuleInterface::VIEW_REFRESH);
            echo $view->render();
        } elseif ($request->getContentType() === NethGui_Core_RequestInterface::CONTENT_TYPE_JSON) {
            header("Content-Type: application/json; charset=UTF-8");
            $worldModule->prepareView($view, NethGui_Core_ModuleInterface::VIEW_UPDATE);
            echo json_encode($view->getArrayCopy());
        }
    }

}
