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
     *
     * @param CI_Controller $controller 
     */
    public function __construct()
    {
        /*
         * Create models.
         * TODO: get hostConfiguration and topModuleDepot class names
         * from NethGui_Framework.
         */
        $this->hostConfiguration = new NethGui_Core_SMEHostConfiguration();
        $this->topModuleDepot = new NethGui_Core_TopModuleDepot($this->hostConfiguration);

        /*
         * TODO: enforce some security policy on Models
         */
        $pdp = new NethGui_Authorization_PermissivePolicyDecisionPoint();

        $this->hostConfiguration->setPolicyDecisionPoint($pdp);
        $this->topModuleDepot->setPolicyDecisionPoint($pdp);
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
            $this->currentModule = $this->topModuleDepot->findModule('Security');
        } else {
            $this->currentModule = $this->topModuleDepot->findModule($method);
        }

        if (is_null($this->currentModule)
            OR ! $this->currentModule instanceof NethGui_Core_TopModuleInterface
        ) {
            show_404();
        }



        $worldModule = new NethGui_Core_Module_World($this->currentModule);

        $request = NethGui_Core_Request::getWebRequestInstance(
                $this->currentModule->getIdentifier(),
                $parameters
        );

        $report = new NethGui_Core_ValidationReport();
        
        $response = NethGui_Core_Response::getRootInstance($request->getContentType(), $worldModule);

        /**
         * Retrieve current User object from $request and set it on PEPs.
         */
        $this->hostConfiguration->setUser($request->getUser());
        $this->topModuleDepot->setUser($request->getUser());


        if ($response->getFormat() === NethGui_Core_ViewInterface::HTML) {
            $worldModule->addChild(new NethGui_Core_Module_Menu($this->topModuleDepot->getModules()));
            $worldModule->addChild(new NethGui_Core_Module_BreadCrumb($this->topModuleDepot, $this->currentModule));
        }

        $worldModule->addChild(new NethGui_Core_Module_ValidationReport($report));

        $moduleActivationList = $request->getParameters();

        if ( ! in_array($this->currentModule->getIdentifier(), $moduleActivationList)) {
            $moduleActivationList[] = $this->currentModule->getIdentifier();
        }

        foreach ($moduleActivationList as $moduleIdentifier) {
            $module = $this->topModuleDepot->findModule($moduleIdentifier);
            $worldModule->addChild($module);
        }

        $worldModule->initialize();
        $worldModule->bind($request);
        $worldModule->validate($report);
        $worldModule->process();
        $worldModule->prepareView($response);

        if ($response->getFormat() === NethGui_Core_ViewInterface::HTML) {
            header("Content-Type: text/html; charset=UTF-8");
            echo NethGui_Framework::getInstance()->renderResponse($response);
        } elseif ($response->getFormat() === NethGui_Core_ViewInterface::JSON) {
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode($response->getWholeData());
            //
        }
    }

}
