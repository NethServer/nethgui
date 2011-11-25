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

    /**
     * Associate each namespace root name to a filesystem directory where
     * the namespace resides.
     * 
     * @var array
     */
    private $namespaceMap = array();

    /**
     * The complete URL to the web-root without trailing slash
     *
     * @example http://www.example.com:8080
     *
     * @var string
     */
    private $siteUrl;

    /**
     * The URL part from the web-root to the app-root with trailing slash
     *
     * @example /path/to/app-root/
     *
     * @var string
     */
    private $basePath;

    /**
     * If no module identifier is provided fall back to this value
     *
     * @var string
     */
    private $defaultModuleIdentifier;

    /**
     * Sends a 303 status redirect to $url.
     * @param type $url
     */
    public function __construct()
    {
        if (basename(__DIR__) !== __NAMESPACE__) {
            throw new \LogicException(sprintf('%s: `%s` is an invalid framework filesystem directory! Must be `%s`.', get_class($this), basename(__DIR__), __NAMESPACE__), 1322213425);
        }
        $this->siteUrl = $this->guessSiteUrl();
        $this->basePath = $this->guessBasePath();
        spl_autoload_register(array($this, 'autoloader'));

        $this->registerNamespace(__DIR__);
    }

    /**
     * Add a namespace to the framework, where to search for Modules.
     *
     * Note: classes and interfaces from a registered namespace are autoloaded.
     * 
     * @api
     * @see autoloader()
     * @param string $namespacePath The absolute path to the namespace root directory
     * @return Framework
     */
    public function registerNamespace($namespacePath)
    {
        $nsRoot = dirname($namespacePath);
        $nsName = basename($namespacePath);
        $this->namespaceMap[$nsName] = $nsRoot;
        return $this;
    }

    private function guessSiteUrl()
    {
        $tmpSiteUrl = empty($_SERVER['HTTPS']) ? 'http://' : 'https://';
        $tmpSiteUrl .= $_SERVER['SERVER_NAME'];
        if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] != '80') {
            $tmpSiteUrl .= ':' . $_SERVER['SERVER_PORT'];
        }
        return $tmpSiteUrl;
    }

    private function guessBasePath()
    {
        $parts = array_values(array_filter(explode('/', $_SERVER['SCRIPT_NAME'])));
        $lastPart = $parts[max(0, count($parts) - 1)];
        $nethguiFile = basename($_SERVER['SCRIPT_FILENAME']);

        if ($lastPart == $nethguiFile) {
            array_pop($parts);
        }
        return '/' . implode('/', $parts) . '/';
    }

    public function setSiteUrl($url)
    {
        $this->siteUrl = $url;
        return $this;
    }

    public function setBasePath($basePath)
    {
        $this->basePath = $basePath;
        return $this;
    }

    public function setDefaultModule($moduleIdentifier)
    {
        $this->defaultModuleIdentifier = $moduleIdentifier;
        return $this;
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
        $nsKey = array_head(explode('\\', $className));

        if (isset($this->namespaceMap[$nsKey])) {
            $filePath = $this->namespaceMap[$nsKey] . '/' . str_replace('\\', '/', $className) . '.php';
            include $filePath;
        }
    }

    /**
     * This is equivalent to the autoloader() function
     *
     * @example Nethgui_Template_Help
     * @param string $symbol A "namespace" script file name (without .php extension)
     * @return string The absolute script path of $symbol
     */
    public function absoluteScriptPath($symbol)
    {
        $nsKey = array_head(explode('\\', $symbol));

        if (isset($this->namespaceMap[$nsKey])) {
            return $this->namespaceMap[$nsKey] . '/' . str_replace('\\', '/', $symbol) . '.php';
        }

        return FALSE;
    }

    /**
     * @deprecated
     * @param type $errorCode
     * @param type $title
     * @param type $text 
     */
    private function httpError($errorCode, $title, $text)
    {
        header(sprintf("HTTP/1.1 %d %s", $errorCode, $title));
        header("Content-Type: text/plain; charset=UTF-8");
        echo $text;
        exit;
    }

    /**
     * Forwards control to Modules and creates output views.
     *
     * @api
     * @param string $currentModuleIdentifier
     * @param array $arguments
     */
    public function dispatch(Core\RequestInterface $request)
    {
        /*
         * TODO: enforce some security policy on Models
         */
        $pdp = new Authorization\PermissivePolicyDecisionPoint();

        $currentModuleIdentifier = array_head($request->getArguments());

        if (empty($currentModuleIdentifier)) {
            $currentModuleIdentifier = $this->defaultModuleIdentifier;
        }

        $user = $request->getUser();

        $platform = new System\NethPlatform($user);
        $platform->setPolicyDecisionPoint($pdp);


        $modules = array();

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
        $notificationManager = new Module\NotificationArea($user);
        $notificationManager->setPlatform($platform);

        // Configrue The World module:
        $worldModule = new Module\World();
        $worldModule->setPlatform($platform);


        $translator = new Language\Translator($user, $platform->getLog(), array($this, 'absoluteScriptPath'), array_keys($this->namespaceMap));
        $view = new Client\View($worldModule, $translator, $this->siteUrl, $this->basePath, 'index.php');

        try {
            foreach ($moduleWakeupList as $moduleIdentifier) {
                $module = $this->getModuleInstance($moduleIdentifier);
                $module->setPlatform($platform);
                $module->initialize();

                $worldModule->addModule($module);

                if ( ! $module instanceof Core\RequestHandlerInterface) {
                    continue;
                }

                // Pass request parameters to the handler
                $module->bind(
                    $request->getParameterAsInnerRequest(
                        $moduleIdentifier, ($moduleIdentifier === $currentModuleIdentifier) ? array_rest($request->getArguments()) : array()
                    )
                );

                // Validate request
                $module->validate($notificationManager);

                // From now on, skip process() step, if any $module has added some validation errors.
                if ($notificationManager->hasValidationErrors()) {
                    continue;
                }

                $module->process();
            }
        } catch (Exception\HttpException $ex) {
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

        $nsMap = $this->namespaceMap;
        $resolver = function ($symbol) use ($nsMap) {
                $nsKey = array_head(explode('\\', $symbol));

                if (isset($nsMap[$nsKey])) {
                    $filePath = $nsMap[$nsKey] . '/' . str_replace('\\', '/', $symbol) . '.php';
                    include $filePath;
                }

                return FALSE;
            };

        /*
         * Prepare the views and render into Xhtml or Json
         */
        if ($request->getContentType() === Core\RequestInterface::CONTENT_TYPE_HTML) {
            $worldModule->prepareView($view, Core\ModuleInterface::VIEW_SERVER);
            header("Content-Type: text/html; charset=UTF-8");
            echo new Renderer\Xhtml($view, array($this, 'absoluteScriptPath'), 0, new Core\LoggingCommandReceiver());
        } elseif ($request->getContentType() === Core\RequestInterface::CONTENT_TYPE_JSON) {
            $worldModule->prepareView($view, Core\ModuleInterface::VIEW_CLIENT);
            header("Content-Type: application/json; charset=UTF-8");
            echo new Renderer\Json($view);
        }
        $notificationManager->dismissTransientDialogBoxes();
    }

    /**
     * Create an instance of $className, checking for valid identifier.
     *
     * @param string $className
     * @return Core\ModuleInterface
     */
    private function getModuleInstance($identifier)
    {
        static $instanceCache = array();

        // Module is already instantiated, return it:
        if (isset($instanceCache[$identifier])) {
            return $instanceCache[$identifier];
        }

        $namespaces = array_keys($this->namespaceMap);

        // Resolve module class namespaces LIFO
        while ($nsName = array_pop($namespaces)) {
            $className = $nsName . '\Module\\' . $identifier;

            if ( ! @class_exists($className)) {
                continue;
            }
            
            $moduleInstance = new $className();

            if ($moduleInstance instanceof Core\TopModuleInterface) {
                $instanceCache[$identifier] = $moduleInstance;
                return $moduleInstance;
            }
        }

        throw new \RuntimeException(sprintf("%s: `%s` is an unknown module identifier", get_class($this), $identifier), 1322231262);
    }

    /**
     * Create a default Request object for dispatch()
     *
     * @see disparch()
     * @param integer $type - Not used
     * @return Core\RequestInterface
     * @api
     */
    public function createRequest($type = NULL)
    {
        return $this->createRequestModApache();
    }

    /**
     * Creates a new Client\Request object from current HTTP request.
     *
     * 
     *
     * @param array $parameters
     * @return Core\RequestInterface
     */
    private function createRequestModApache()
    {
        $pathInfo = array();

        if (isset($_SERVER['PATH_INFO']) && $_SERVER['PATH_INFO'] != '/') {
            $pathInfo = array_rest(explode('/', $_SERVER['PATH_INFO']));

            foreach ($pathInfo as $pathPart) {
                if ($pathPart === '.'
                    || $pathPart === '..'
                    || $pathPart === '') {
                    throw new Exception\HttpException('Bad Request', 400, 1322217901);
                }
            }
        }

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
                    throw new Exception\HttpException('Bad Request', 400, 1322148404);
                }
            } else {
                // Use PHP global:
                $data = $_POST;
            }
        }

        // XXX: This is a non-compliant HTTP Accept-header parsing:
        $httpAccept = isset($_SERVER['HTTP_ACCEPT']) ? trim(array_shift(explode(',', $_SERVER['HTTP_ACCEPT']))) : FALSE;

        if ($httpAccept == 'application/json')
            $contentType = Core\RequestInterface::CONTENT_TYPE_JSON;
        else {
            // Standard  POST request.
            $contentType = Core\RequestInterface::CONTENT_TYPE_HTML;
        }


        // TODO: retrieve user state from Session
        $user = new Client\AlwaysAuthenticatedUser();

        $instance = new Client\Request($user, $data, $submitted, $pathInfo, array(
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

/*
 * NETHGUI_ENABLE_TARGET_HASH: if set to TRUE pass client names through an hash function
 */
if ( ! defined('NETHGUI_ENABLE_TARGET_HASH')) {
    define('NETHGUI_ENABLE_TARGET_HASH', FALSE);
}

function array_head($arr)
{
    return reset($arr);
}

function array_end($arr)
{
    return end($arr);
}

function array_rest($arr)
{
    array_shift($arr);
    return $arr;
}