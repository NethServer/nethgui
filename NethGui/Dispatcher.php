<?php
/**
 * @package NethGui
 */

/**
 * @package NethGui
 */
class NethGui_Dispatcher
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
     * @param string $currentModuleIdentifier
     * @param array $arguments
     */
    public function dispatch($currentModuleIdentifier, $arguments = array())
    {
        // Replace "index" request with a (temporary) default module value
        if ($currentModuleIdentifier == 'index') {
            // TODO: get this value from configuration:
            $currentModuleIdentifier = 'Security';
        }

        array_unshift($arguments, $currentModuleIdentifier);

        $request = NethGui_Core_Request::getHttpRequest($arguments);

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
        $this->currentModule = $this->topModuleDepot->findModule($currentModuleIdentifier);


        if (is_null($this->currentModule)
            OR ! $this->currentModule instanceof NethGui_Core_TopModuleInterface
        ) {
            show_404();
        }

        $worldModule = new NethGui_Core_Module_World($this->currentModule);

        $report = new NethGui_Core_ValidationReport();

        $view = new NethGui_Core_View($worldModule);

        // Add menu and breadcrumb decorations if we are building a full HTML view.
        if (!$request->isXmlHttpRequest()) {
            $worldModule->addChild(new NethGui_Core_Module_Menu($this->topModuleDepot->getModules()));
            $worldModule->addChild(new NethGui_Core_Module_BreadCrumb($this->topModuleDepot, $this->currentModule));
        }

        $worldModule->addChild(new NethGui_Core_Module_ValidationReport($report));

        if ($request->isSubmitted()) {
            // Multiple modules can be called in the same request.
            $moduleWakeupList = $request->getParameters();
        } else {
            // The default module is the given in the web request.
            $moduleWakeupList = array($currentModuleIdentifier);
        }

        foreach ($moduleWakeupList as $moduleIdentifier) {
            $module = $this->topModuleDepot->findModule($moduleIdentifier);
            if ($module instanceof NethGui_Core_ModuleInterface) {
                $worldModule->addChild($module);
            }
        }

        try {
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
        } catch (NethGui_Exception_HttpStatusClientError $s) {
            show_error('Status ' . $s->getCode(), $s->getCode(), $s->getMessage());
        }

        if ($request->getContentType() === NethGui_Core_Request::CONTENT_TYPE_HTML) {
            header("Content-Type: text/html; charset=UTF-8");
            $worldModule->prepareView($view, NethGui_Core_ModuleInterface::VIEW_REFRESH);
            echo $view->render();
        } elseif ($request->getContentType() === NethGui_Core_Request::CONTENT_TYPE_JSON) {
            header("Content-Type: application/json; charset=UTF-8");
            $worldModule->prepareView($view, NethGui_Core_ModuleInterface::VIEW_UPDATE);
            echo json_encode($view->getArrayCopy());
        }
    }

}
