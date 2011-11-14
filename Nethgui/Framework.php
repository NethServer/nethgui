<?php
/**
 * @package Nethgui
 */

/**
 * @package Nethgui
 */
class Nethgui_Framework
{


    /**
     * Returns framework singleton instance.
     * @staticvar Nethgui_Framework $instance
     * @return Nethgui_Framework
     */
    static public function getInstance()
    {
        static $instance;
        if ( ! isset($instance)) {
            $instance = new self();
        }
        return $instance;
    }

    private function __construct()
    {
        spl_autoload_register(get_class($this) . '::autoloader');
    }

    /**
     * Simple class autoloader
     *
     * This function is registered as SPL class autoloader.
     *
     * @todo XXX Check for class names cheating!
     * @param string $className
     * @return void
     */
    static public function autoloader($className)
    {
        /* Skip CodeIgniter namespace, and "configuration" */
        if (substr($className, 0, 3) == 'CI_' || $className === 'configuration') {
            return;
        }
        $classPath = NETHGUI_ROOTDIR . '/' . str_replace("_", "/", $className) . '.php';
        include $classPath;
    }

    /**
     * Sends a 303 status redirect to $url.
     * @param type $url 
     */
    private function redirect($url)
    {
        redirect($url);
    }

    /**
     * Forwards control to Modules and creates output views.
     *
     * @param string $currentModuleIdentifier
     * @param array $arguments
     */
    public function dispatch($currentModuleIdentifier, $arguments = array())
    {
        // Replace "index" request with a  default module value
        if ($currentModuleIdentifier == 'index') {
            // TODO read from configuration
            $this->redirect('dispatcher/Status');
        }

        $request = $this->createRequest($arguments);
        $user = $request->getUser();

        $platform = new Nethgui_System_NethPlatform($user);               
        $topModuleDepot = new Nethgui_Core_TopModuleDepot($platform, $user);

        /*
         * TODO: enforce some security policy on Models
         */
        $pdp = new Nethgui_Authorization_PermissivePolicyDecisionPoint();

        $platform->setPolicyDecisionPoint($pdp);
        $topModuleDepot->setPolicyDecisionPoint($pdp);

        if ($request->isSubmitted()) {
            // Multiple modules can be called in the same POST request.
            $moduleWakeupList = $request->getParameters();
        } else {
            $moduleWakeupList = array();
        }

        // Ensure the current module is the first of the list (as required by World Module):
        array_unshift($moduleWakeupList, $currentModuleIdentifier);
        $moduleWakeupList = array_unique($moduleWakeupList);

        // Configure the NotificationArea:
        $notificationManager = new Nethgui_Module_NotificationArea($user);
        $notificationManager->setPlatform($platform);
        $topModuleDepot->registerModule($notificationManager);

        // Configure the online Help:
        $helpModule = new Nethgui_Module_Help($topModuleDepot);
        $helpModule->setPlatform($platform);
        $topModuleDepot->registerModule($helpModule);

        // Configure the module menu
        $menuModule = new Nethgui_Module_Menu($topModuleDepot->getModules(), $currentModuleIdentifier);
        $menuModule->setPlatform($platform);
        $topModuleDepot->registerModule($menuModule);

        // Configrue The World module:
        $worldModule = new Nethgui_Module_World();
        $worldModule->setPlatform($platform);
        
        $view = new Nethgui_Core_View($worldModule, new Nethgui_Language_Translator($platform->getLog()));

        try {
            foreach ($moduleWakeupList as $moduleIdentifier) {
                $module = $topModuleDepot->findModule($moduleIdentifier);

                if ($module instanceof Nethgui_Core_ModuleInterface) {
                    $worldModule->addModule($module);

                    // Module initialization
                    $module->initialize();
                } else {
                    show_404();
                }


                if ( ! $module instanceof Nethgui_Core_RequestHandlerInterface) {
                    continue;
                }

                // Pass request parameters to the handler
                $module->bind(
                    $request->getParameterAsInnerRequest(
                        $moduleIdentifier, ($moduleIdentifier === $currentModuleIdentifier) ? $request->getArguments() : array()
                    )
                );

                // Validate request
                $module->validate($notificationManager);

                // Skip process() step, if $module has added some validation errors:
                if ($notificationManager->hasValidationErrors()) {
                    continue;
                }

                $module->process($notificationManager);
            }
        } catch (Nethgui_Exception_HttpStatusClientError $ex) {
            $statusCode = intval($ex->getCode());
            if ($statusCode >= 400 && $statusCode < 600) {
                show_error($ex->getMessage(), $statusCode);
            } else {
                show_error(sprintf('Original status %d, %s', $statusCode, $ex->getMessage()), 500);
            }
        } catch (Exception $ex) {
            // TODO - validate $ex->getCode(): is it a valid HTTP status code?
            throw $ex;
        }

        $worldModule->addModule($notificationManager);

        // Finally, signal "final" events (see #506)
        $platform->signalFinalEvents();

        /**
         * Validation error http status.
         * See RFC2616, section 10.4 "Client Error 4xx"
         */
        if ($notificationManager->hasValidationErrors()) {
            // FIXME: check if we are in FAST-CGI module:
            // @see http://php.net/manual/en/function.header.php
            header("HTTP/1.1 400 Request validation error");
        }

        /*
         * Prepare the views and render into Xhtml or Json
         */
        if ($request->getContentType() === Nethgui_Core_Request::CONTENT_TYPE_HTML) {
            $worldModule->addModule($menuModule);
            $worldModule->prepareView($view, Nethgui_Core_ModuleInterface::VIEW_SERVER);
            $redirectUrl = $this->getRedirectUrl($user);
            if ($redirectUrl === FALSE) {
                header("Content-Type: text/html; charset=UTF-8");
                echo new Nethgui_Renderer_Xhtml($view);
                $notificationManager->dismissTransientDialogBoxes();
            } else {
                $this->redirect($redirectUrl);
            }
        } elseif ($request->getContentType() === Nethgui_Core_Request::CONTENT_TYPE_JSON) {
            $worldModule->prepareView($view, Nethgui_Core_ModuleInterface::VIEW_CLIENT);
            $clientCommands = $this->clientCommandsToArray($user->getClientCommands());
            if ( ! empty($clientCommands)) {
                throw new Exception('TODO: client commands are not supported');
                //$events[] = array('ClientCommandHandler', $clientCommands);
            }
            header("Content-Type: application/json; charset=UTF-8");
            echo new Nethgui_Renderer_Json($view);
            $notificationManager->dismissTransientDialogBoxes();
        }
    }

    private function clientCommandsToArray($clientCommands)
    {
        $output = array();
        foreach ($clientCommands as $command) {
            if ($command instanceof Nethgui_Client_CommandInterface) {
                $output[] = array(
                    'targetSelector' => $command->getTargetSelector(),
                    'method' => $command->getMethod(),
                    'arguments' => $command->getArguments(),
                );
            }
        }
        return $output;
    }

    /**
     * Check if a redirect condition has been set and calculate the URL.
     * 
     * @param Nethgui_Client_UserInterface $user
     * @return string|bool The URL where to redirect the user
     */
    private function getRedirectUrl(Nethgui_Client_UserInterface $user)
    {
        foreach ($user->getClientCommands() as $command) {
            if ($command instanceof Nethgui_Client_CommandInterface && $command->isRedirection()) {
                return $command->getRedirectionUrl();
            }
        }

        return FALSE;
    }

    /**
     * Creates a new Nethgui_Core_Request object from current HTTP request.
     * @param string $defaultModuleIdentifier
     * @param array $parameters
     * @return Nethgui_Core_Request
     */
    public function createRequest($arguments)
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
            $isXmlHttpRequest = TRUE;
        } else {
            $isXmlHttpRequest = FALSE;
        }


        $submitted = FALSE;
        $data = array();

        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
            $submitted = TRUE;

            if (isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] == 'application/json; charset=UTF-8') {
                // Decode RAW request
                $data = json_decode($GLOBALS['HTTP_RAW_POST_DATA'], true);

                if (is_null($data)) {
                    throw new Nethgui_Exception_HttpStatusClientError('Bad Request', 400);
                }
            } else {
                // Use PHP global:
                $data = $_POST;
            }
        }

        // XXX: This is a non-compliant HTTP Accept-header parsing:
        $httpAccept = isset($_SERVER['HTTP_ACCEPT']) ? trim(array_shift(explode(',', $_SERVER['HTTP_ACCEPT']))) : FALSE;

        if ($httpAccept == 'application/json')
            $contentType = Nethgui_Core_Request::CONTENT_TYPE_JSON;
        else {
            // Standard  POST request.
            $contentType = Nethgui_Core_Request::CONTENT_TYPE_HTML;
        }


        // TODO: retrieve user state from Session
        $user = new Nethgui_Client_AlwaysAuthenticatedUser();

        $instance = new Nethgui_Core_Request($user, $data, $submitted, $arguments, array(
            'XML_HTTP_REQUEST' => $isXmlHttpRequest,
            'CONTENT_TYPE' => $contentType,
        ));

        /*
         * Clear global variables
         */
        $_POST = array();

        return $instance;
    }

}
