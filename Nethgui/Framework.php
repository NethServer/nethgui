<?php
namespace Nethgui;

/*
 * Copyright (C) 2011 Nethesis S.r.l.
 * 
 * This script is part of NethServer.
 * 
 * NethServer is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * NethServer is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with NethServer.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 */
class Framework
{

    private $namespaceMap = array();
    private $applications = array();

    /**
     * Sends a 303 status redirect to $url.
     * @param type $url
     */
    public function __construct()
    {
        $this->namespaceMap['Nethgui'] = dirname(__DIR__);
        spl_autoload_register(array($this, 'autoloader'));
    }

    public function registerApplication($applicationPath)
    {
        $appRoot = dirname($applicationPath);
        $appName = basename($applicationPath);

        $this->applications[] = $appName;
        $this->namespaceMap[$appName] = $appRoot;
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
    public function autoloader($className)
    {
        //$className = substr($className, strlen(__NAMESPACE__) + 1);

        $nsKey = array_head(explode('\\', $className));

        if (isset($this->namespaceMap[$nsKey])) {
            $filePath = $this->namespaceMap[$nsKey] . '/' . str_replace('\\', '/', $className) . '.php';
            include $filePath;
        }
    }

    private function redirect($url)
    {
        header(sprintf("HTTP/1.1 %d %s", 303, 'See other'));
        header('Location: ' . NETHGUI_BASEURL . NETHGUI_CONTROLLER . '/' . $url);
        exit;
    }

    /**
     * Forwards control to Modules and creates output views.
     *
     * @param string $currentModuleIdentifier
     * @param array $arguments
     */
    public function dispatch($currentModuleIdentifier, $arguments = array())
    {
        if ($currentModuleIdentifier == 'index') {
            if (NETHGUI_INDEX) {
                $this->redirect(NETHGUI_INDEX);
            } else {
                $this->httpError(500, 'Server error', 'Missing NETHGUI_INDEX constant');
            }
        }

        $request = $this->createRequest($arguments);
        $user = $request->getUser();

        $platform = new \Nethgui\System\NethPlatform($user);
        $topModuleDepot = new \Nethgui\Core\TopModuleDepot($platform, $user);

        /*
         * TODO: enforce some security policy on Models
         */
        $pdp = new \Nethgui\Authorization\PermissivePolicyDecisionPoint();

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
        $notificationManager = new \Nethgui\Module\NotificationArea($user);
        $notificationManager->setPlatform($platform);
        $topModuleDepot->registerModule($notificationManager);

        // Configure the online Help:
        $helpModule = new \Nethgui\Module\Help($topModuleDepot);
        $helpModule->setPlatform($platform);
        $topModuleDepot->registerModule($helpModule);

        // Configure the module menu
        $menuModule = new \Nethgui\Module\Menu($topModuleDepot->getModules(), $currentModuleIdentifier);
        $menuModule->setPlatform($platform);
        $topModuleDepot->registerModule($menuModule);

        // Configrue The World module:
        $worldModule = new \Nethgui\Module\World();
        $worldModule->setPlatform($platform);

        $view = new \Nethgui\Client\View($worldModule, new \Nethgui\Language\Translator($user, $platform->getLog()));

        try {
            foreach ($moduleWakeupList as $moduleIdentifier) {
                $module = $topModuleDepot->findModule($moduleIdentifier);

                if ($module instanceof \Nethgui\Core\ModuleInterface) {
                    $worldModule->addModule($module);

                    // Module initialization
                    $module->initialize();
                } else {
                    $this->httpError(404, 'Not found', 'Resource not found');
                }


                if ( ! $module instanceof \Nethgui\Core\RequestHandlerInterface) {
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
        } catch (\Nethgui\Exception\HttpException $ex) {
            $statusCode = intval($ex->getHttpStatusCode());
            if ($statusCode >= 400 && $statusCode < 600) {
                $this->httpError($statusCode, $ex->getMessage(), $statusCode . ': ' . $ex->getMessage());
            } else {
                $this->httpError(500, 'Server error', sprintf('Original status %d, %s', $statusCode, $ex->getMessage()));
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
        if ($request->getContentType() === \Nethgui\Client\Request::CONTENT_TYPE_HTML) {
            $worldModule->addModule($menuModule);
            $worldModule->prepareView($view, \Nethgui\Core\ModuleInterface::VIEW_SERVER);
            $redirectUrl = $this->getRedirectUrl($user);
            if ($redirectUrl === FALSE) {
                header("Content-Type: text/html; charset=UTF-8");
                echo new \Nethgui\Renderer\Xhtml($view, 0, new \Nethgui\Core\LoggingCommandReceiver());
                $notificationManager->dismissTransientDialogBoxes();
            } else {
                $this->redirect($redirectUrl);
            }
        } elseif ($request->getContentType() === \Nethgui\Client\Request::CONTENT_TYPE_JSON) {
            $worldModule->prepareView($view, \Nethgui\Core\ModuleInterface::VIEW_CLIENT);
            header("Content-Type: application/json; charset=UTF-8");
            echo new \Nethgui\Renderer\Json($view);
            $notificationManager->dismissTransientDialogBoxes();
        }
    }

    /**
     * Check if a redirect condition has been set and calculate the URL.
     *
     * @param \Nethgui\Client\UserInterface $user
     * @return string|bool The URL where to redirect the user
     */
    private function getRedirectUrl(\Nethgui\Client\UserInterface $user)
    {
        return FALSE;
    }

    /**
     * Creates a new \Nethgui\Client\Request object from current HTTP request.
     * @param string $defaultModuleIdentifier
     * @param array $parameters
     * @return Nethgui_Core_Request
     */
    private function createRequest($arguments)
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
                    throw new \Nethgui\Exception\HttpException('Bad Request', 400, 1322148404);
                }
            } else {
                // Use PHP global:
                $data = $_POST;
            }
        }

        // XXX: This is a non-compliant HTTP Accept-header parsing:
        $httpAccept = isset($_SERVER['HTTP_ACCEPT']) ? trim(array_shift(explode(',', $_SERVER['HTTP_ACCEPT']))) : FALSE;

        if ($httpAccept == 'application/json')
            $contentType = \Nethgui\Client\Request::CONTENT_TYPE_JSON;
        else {
            // Standard  POST request.
            $contentType = \Nethgui\Client\Request::CONTENT_TYPE_HTML;
        }


        // TODO: retrieve user state from Session
        $user = new \Nethgui\Client\AlwaysAuthenticatedUser();

        $instance = new \Nethgui\Client\Request($user, $data, $submitted, $arguments, array(
                'XML_HTTP_REQUEST' => $isXmlHttpRequest,
                'CONTENT_TYPE' => $contentType,
            ));

        /*
         * Clear global variables
         */
        $_POST = array();

        return $instance;
    }

    private function httpError($errorCode, $title, $text)
    {
        header(sprintf("HTTP/1.1 %d %s", $errorCode, $title));
        header("Content-Type: text/plain; charset=UTF-8");
        echo $text;
        exit;
    }

}

