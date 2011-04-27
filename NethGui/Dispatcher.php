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

        $processExitCode = NULL;

        foreach ($moduleWakeupList as $moduleIdentifier) {
            $module = $this->topModuleDepot->findModule($moduleIdentifier);

            if ($module instanceof NethGui_Core_ModuleInterface) {
                $worldModule->addModule($module);

                // Module initialization
                $module->initialize();
            }


            if ( ! $module instanceof NethGui_Core_RequestHandlerInterface) {
                continue;
            }

            // Pass request parameters to the handler 
            $module->bind(
                $request->getParameterAsInnerRequest(
                    $moduleIdentifier,
                    ($moduleIdentifier === $currentModuleIdentifier) ? $request->getArguments() : array()
                )
            );

            // Validate request
            $module->validate($report);

            // Stop here if we have validation errors
            if (count($report->getErrors()) > 0) {
                continue;
            }

            // Process the request
            $moduleExitCode = $module->process();

            // Only the first non-NULL module exit code is considered as
            // the process exit code:
            if (is_null($processExitCode)) {
                $processExitCode = $moduleExitCode;
            }
        }

        // Add menu and breadcrumb decorations if we are building a full HTML view.
        if ( ! $request->isXmlHttpRequest()) {
            $worldModule->addModule(new NethGui_Core_Module_Menu($this->topModuleDepot->getModules()));
            $worldModule->addModule(new NethGui_Core_Module_BreadCrumb($this->topModuleDepot, $currentModuleIdentifier));
        }

        $worldModule->addModule(new NethGui_Core_Module_ValidationReport($report));

        if (is_integer($processExitCode)) {
            set_status_header($processExitCode);
        } elseif (is_array($processExitCode)) {
            redirect($processExitCode[1], 'location', $processExitCode[0]);
        }

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
