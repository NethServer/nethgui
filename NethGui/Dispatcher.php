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

        if ($request->isSubmitted()) {
            // Multiple modules can be called in the same request.
            $moduleWakeupList = $request->getParameters();

            // Ensure the current module is in the list:
            if ( ! in_array($currentModuleIdentifier, $moduleWakeupList)) {
                array_unshift($currentModuleIdentifier, $moduleWakeupList);
            }
        } else {
            // The default module is the given in the web request.
            $moduleWakeupList = array($currentModuleIdentifier);
        }

        $report = new NethGui_Core_ValidationReport();

        // The World module is a non-processing container.
        $worldModule = new NethGui_Core_Module_World();
        $view = new NethGui_Core_View($worldModule);

        foreach ($moduleWakeupList as $moduleIdentifier) {
            $module = $this->topModuleDepot->findModule($moduleIdentifier);
            if ($module instanceof NethGui_Core_ModuleInterface) {

                $worldModule->addModule($module);

                try {
                    $module->initialize();
                    $module->bind(
                        $request->getParameterAsInnerRequest(
                            $moduleIdentifier,
                            ($moduleIdentifier === $currentModuleIdentifier) ? $request->getArguments() : array()
                        )
                    );

                    $module->validate($report);
                    
                    if(count($report->getErrors()) > 0) {
                        continue;
                    }

                    try {
                        $module->process();
                    } catch (NethGui_Exception_Process $e) {
                        NethGui_Framework::logMessage($e->getMessage(), 'error');
                        $view['Exception'] = $e->getMessage();
                        $view['StackTrace'] = $e->getTrace();
                        throw new NethGui_Exception_HttpStatusClientError($e->getMessage(), 500, $e);
                    }
                } catch (NethGui_Exception_HttpStatusClientError $s) {
                    show_error('Status ' . $s->getCode(), $s->getCode(), $s->getMessage());
                }
                
            } else {
                show_404();
            }
        }

        // Add menu and breadcrumb decorations if we are building a full HTML view.
        if ( ! $request->isXmlHttpRequest()) {
            $worldModule->addModule(new NethGui_Core_Module_Menu($this->topModuleDepot->getModules()));
            $worldModule->addModule(new NethGui_Core_Module_BreadCrumb($this->topModuleDepot, $currentModuleIdentifier));
        }

        $worldModule->addModule(new NethGui_Core_Module_ValidationReport($report));

        if ($request->getContentType() === NethGui_Core_Request::CONTENT_TYPE_HTML) {
            header("Content-Type: text/html; charset=UTF-8");
            $worldModule->prepareView($view, NethGui_Core_ModuleInterface::VIEW_REFRESH);
            echo $view;
        } elseif ($request->getContentType() === NethGui_Core_Request::CONTENT_TYPE_JSON) {
            header("Content-Type: application/json; charset=UTF-8");
            $worldModule->prepareView($view, NethGui_Core_ModuleInterface::VIEW_UPDATE);
            echo json_encode($view->getArrayCopy());
        }
    }
}
