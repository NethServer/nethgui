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
 * This is the external interface of the whole framework.
 *
 * Embed Nethgui Framework into any application by instantiating this
 * class, invoking the setter methods and catching the output from the dispatch()
 * method.
 *
 * @see #dispatch(Controller\RequestInterface $request)
 * @api
 */
class Framework
{
    /**
     * Associate each namespace root name to a filesystem directory where
     * the namespace resides.
     * 
     * @var array
     */
    private $namespaceMap;

    /**
     * Index 0 => The complete URL to the web-root without trailing slash (ex: http://www.example.com:8080)
     * Index 1 => The URL part from the web-root to the app-root with trailing slash (ex: /path/to/app-root/)
     * Index 2 => [Optional] The controller script file name or empty string with trailing slash 
     * 
     * @var array
     */
    private $urlParts;

    /**
     * If no module identifier is provided fall back to this value
     *
     * @var string
     */
    private $defaultModuleIdentifier;

    /**
     * Identifier of the layout decorator module
     *
     * @var string
     */
    private $decoratorTemplate;

    /**
     *
     * @var \Nethgui\Module\ModuleLoader;
     */
    private $moduleSet;

    /**
     *
     * @var \Nethgui\Utility\Session
     */
    private $session;

    public function __construct()
    {
        $this->namespaceMap = new \ArrayObject();

        spl_autoload_register(array($this, 'autoloader'));
        if (basename(__DIR__) !== __NAMESPACE__) {
            throw new \LogicException(sprintf('%s: `%s` is an invalid framework filesystem directory! Must be `%s`.', __CLASS__, basename(__DIR__), __NAMESPACE__), 1322213425);
        }
        $this->registerNamespace(__DIR__);

        $this->urlParts = $this->guessUrlParts();

        $this->decoratorTemplate = 'Nethgui\Template\Main';

        $this->log = new \Nethgui\Log\Syslog(E_WARNING | E_ERROR);
        $this->session = new \Nethgui\Utility\Session();
        $this->pdp = new \Nethgui\Authorization\JsonPolicyDecisionPoint($this->getFileNameResolver());
        $this->pdp->setLog($this->log);
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
     * Add a namespace to the framework, where to search for Modules.
     *
     * Declare that the given namespace is a Nethgui extension. It must have a "Module"
     * subpackage.
     *
     * For instance, an "Acme" namespace should have the following directory/package structure
     *
     * - Acme
     *   - Module
     *   - Template
     *   - Language
     *   - Help
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

    private function guessUrlParts()
    {
        $urlParts = array();

        $siteUrl = empty($_SERVER['HTTPS']) ? 'http://' : 'https://';
        $siteUrl .= isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost';
        if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] != '80') {
            $siteUrl .= ':' . $_SERVER['SERVER_PORT'];
        }

        $urlParts[] = $siteUrl;

        $basePath = dirname($_SERVER['SCRIPT_NAME']);

        $urlParts[] = ($basePath === '/') ? '/' : ($basePath . '/');

        if (isset($_SERVER['PATH_INFO'])) {
            //echo 'PATH_INFO: ' . $_SERVER['PATH_INFO'] . "\n";
            $requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $requestController = substr($requestPath, 0, strlen($requestPath) - strlen($_SERVER['PATH_INFO']));
            if ($requestController === $_SERVER['SCRIPT_NAME']) {
                $urlParts[] = basename($_SERVER['SCRIPT_NAME']) . '/';
            }
        } else {
            $urlParts[] = basename($_SERVER['SCRIPT_NAME']) . '/';
        }


        //echo '<!-- ' . implode('    ', $urlParts) . "-->\n";
        return $urlParts;
    }

    /**
     * The web site URL without trailing slash
     *
     * @example http://www.example.org:8080
     * @api
     * @param string $siteUrl
     * @return Framework
     */
    public function setSiteUrl($siteUrl)
    {
        $this->urlParts[0] = $siteUrl;
        return $this;
    }

    /**
     * The path component of an URL with a leading and trailing slash.
     * 
     * An empty path collapse to a single slash "/".
     *
     * @example /my/path/to/the/app/
     * @api
     * @param type $basePath
     * @return Framework
     */
    public function setBasePath($basePath)
    {
        $this->urlParts[1] = $basePath;
        return $this;
    }

    /**
     *
     * @api
     * @param string $moduleIdentifier
     * @return Framework
     */
    public function setDefaultModule($moduleIdentifier)
    {
        $this->defaultModuleIdentifier = $moduleIdentifier;
        return $this;
    }

    /**
     * The script or function that decorates the current module output
     *
     * @api
     * @param string|callable $template
     * @return Framework
     */
    public function setDecoratorTemplate($template)
    {
        $this->decoratorTemplate = $template;
        return $this;
    }

    /**
     * Change the level of details in the log output
     *
     * @api
     * @param integer $level The standard PHP error mask
     * @return \Nethgui\Framework
     */
    public function setLogLevel($level)
    {
        $this->log->setLevel($level);
        return $this;
    }

    /**
     * Translate a namespaced classifier (interface, class) or a namespaced-script-name
     * into an absolute filesystem path.
     *
     * This is equivalent to the autoloader() function
     *
     * @example Nethgui\Template\Help is converted into /abs/path/Nethgui/Template/Help.php
     * @param string $symbol A "namespace" classifier or script file name
     * @return string The absolute script path of $symbol
     */
    public function absoluteScriptPath($symbol)
    {
        // fix namespace backslashes:
        $symbol = str_replace('\\', '/', $symbol);
        $nsKey = array_head(explode('/', $symbol));

        if ( ! isset($this->namespaceMap[$nsKey])) {
            return FALSE;
        }

        $absolutePath = $this->namespaceMap[$nsKey] . '/' . $symbol;
        if (pathinfo($symbol, PATHINFO_EXTENSION) === '') {
            $absolutePath .= '.php';
        }
        return $absolutePath;
    }

    private function getFileNameResolver()
    {
        return array($this, 'absoluteScriptPath');
    }

    /**
     * Forwards control to Modules and creates output views.
     *
     * This is the framework "main()" function / entry point.
     *
     * @api
     * @param \Nethgui\Controller\RequestInterface $request
     * @param array $arguments Optional - This array is filled with the output, instead of echo()ing it
     * @return integer
     */
    public function dispatch(\Nethgui\Controller\RequestInterface $request, &$output = NULL)
    {
        try {
            $this->initializeModuleLoader();
            $this->enforceAuthorization();
            return $this->processRequest($request, $output);
        } catch (\Nethgui\Exception\HttpException $ex) {
            // no processing is required, rethrow:
            throw $ex;
        } catch (\Nethgui\Exception\AuthorizationException $ex) {
            if ($request->getExtension() === 'xhtml' && ! $request->isMutation() && ! $request->getUser()->isAuthenticated()) {
                return $this->processRequest($this->createLoginRequest($request), $output);
            } else {
                $this->log->error(sprintf('%s: [%d] %s', __CLASS__, $ex->getCode(), $ex->getMessage()));
                throw new \Nethgui\Exception\HttpException('Forbidden', 403, 1327681977, $ex);
            }
        } catch (\RuntimeException $ex) {
            $this->log->error(sprintf('%s: [%d] %s', __CLASS__, $ex->getCode(), $ex->getMessage()));
            throw new \Nethgui\Exception\HttpException('Runtime error', 500, 1366796122, $ex);
        }
    }

    private function createLoginRequest(\Nethgui\Controller\Request $originalRequest)
    {
        //$originalRequest->toArray()
        $r = new \Nethgui\Controller\Request(array('Login' => $originalRequest->toArray()));
        $r->setAttribute('languageCode', $originalRequest->getLanguageCode());
        return $r;
    }

    /**
     * Load all json files in \Authorization subdirs as authorization policies
     * 
     * @return \Nethgui\Framework
     */
    private function enforceAuthorization()
    {
        foreach ($this->namespaceMap as $nsName => $nsPath) {
            $this->pdp->loadPolicy($nsName . '\Authorization\*.json');
        }
        return $this;
    }

    private function initializeModuleLoader()
    {
        $this->moduleSet = new \Nethgui\Module\ModuleLoader();
        $this->moduleSet->setLog($this->log);
        $this->moduleSet->addInstantiateCallback(array($this, 'initializeModule'));
        foreach ($this->namespaceMap as $nsName => $nsRoot) {
            if ($nsName === 'Nethgui') {
                $nsRoot = FALSE;
            }
            $this->moduleSet->setNamespace($nsName . '\\Module', $nsRoot);
        }
        return $this;
    }

    private function processRequest(\Nethgui\Controller\RequestInterface $request, &$output = NULL)
    {
        if ($request instanceof \Nethgui\Utility\SessionConsumerInterface) {
            $request->setSession($this->session);
        }

        $user = $request->getUser();

        if (array_head($request->getPath()) === FALSE) {
            $redirectUrl = implode('', $this->urlParts) . $this->defaultModuleIdentifier;
            $this->sendHttpResponse('', array('HTTP/1.1 302 Found', sprintf('Location: %s', $redirectUrl)), $output);
            return;
        }

        $platform = new \Nethgui\System\NethPlatform($user);
        $platform
            ->setLog($this->log)
            ->setSession($this->session)
            ->setPolicyDecisionPoint($this->pdp)
        ;

        // Enforce authorization policy on moduleSet:
        $authModuleLoader = new \Nethgui\Authorization\AuthorizedModuleSet($this->moduleSet, $user);
        $authModuleLoader->setPolicyDecisionPoint($this->pdp);

        $fileNameResolver = $this->getFileNameResolver();
        $currentModuleIdentifier = array_head($request->getPath());

        $this->moduleSet->addInstantiateCallback(function (Module\ModuleInterface $module) use ($authModuleLoader, $currentModuleIdentifier, $fileNameResolver) {
                $moduleIdentifier = $module->getIdentifier();

                if ($moduleIdentifier === 'Menu') {
                    $module
                        ->setModuleSet($authModuleLoader)
                        ->setCurrentModuleIdentifier($currentModuleIdentifier)
                    ;
                } elseif ($moduleIdentifier === 'Help') {
                    $module
                        ->setModuleSet($authModuleLoader)
                        ->setFileNameResolver($fileNameResolver)
                    ;
                }
            });

        $mainModule = new \Nethgui\Module\Main($this->decoratorTemplate, $authModuleLoader);
        $mainModule->setPlatform($platform);
        $mainModule->setPolicyDecisionPoint($this->pdp);

        $mainModule->initialize();
        $mainModule->bind($request);

        $validationErrorsNotification = new \Nethgui\Module\Notification\ValidationErrorsNotification();

        $mainModule->validate($validationErrorsNotification);

        if ( ! $validationErrorsNotification->hasValidationErrors()) {
            $request->setAttribute('isValidated', TRUE);
            $mainModule->process();
            // Run the "post-process" event queue (see #506)
            $platform->runEvents('post-process');
        }

        $targetFormat = $request->getFormat();
        $translator = new \Nethgui\View\Translator($request->getLanguageCode(), $this->getFileNameResolver(), array_keys(iterator_to_array($this->namespaceMap)));
        $translator->setLog($this->log);
        $rootView = new \Nethgui\View\View($targetFormat, $mainModule, $translator, $this->urlParts);

        $commandReceiver = new \Nethgui\Renderer\HttpCommandReceiver(new \Nethgui\Renderer\MarshallingReceiver(new \Nethgui\View\LoggingCommandReceiver($this->log)));
        $commandReceiver->setLog($this->log);

        $rootView->setReceiver($commandReceiver);

        // ..transfer contents and commands into the MAIN view:
        $mainModule->prepareView($rootView);

        if ($request->isValidated()) {
            // On a valid request honorate the nextPath() semantics:
            $nextPath = $mainModule->nextPath();
            if (is_string($nextPath)) {
                $rootView->getCommandList('/Main')
                    ->sendQuery($rootView->getModuleUrl($nextPath));
            }
        } else {
            // Only validation errors notification has to be shown: clear
            // all enqueued commands.
            //$rootView->clearAllCommands();
            // Validation error http status.
            // See RFC2616, section 10.4 "Client Error 4xx"
            // FIXME: check if we are in FAST-CGI module:
            // @see http://php.net/manual/en/function.header.php
            $rootView->getCommandList('/Notification')
                ->showNotification($validationErrorsNotification)
                ->httpHeader("HTTP/1.1 400 Request validation error")
            ;
        }

        // ..Execute commands sent to views. These do not include commands sent to widgets:
        $this->executeViewCommands($rootView);

        if ($targetFormat === 'xhtml' && $commandReceiver->hasLocation()) {
            $content = '';
            $headers = $commandReceiver->getHttpHeaders();
        } else {
            // Render the view as Xhtml or Json
            $renderer = $this->getRenderer($targetFormat, $rootView, $commandReceiver);
            $content = $renderer->render();
            // execute all non-executed commands:
            $defaultHeaders = array(
                "HTTP/1.1 200 Success",
                sprintf('Content-Type: %s', $renderer->getContentType()) . (
                $renderer->getCharset() ?
                    sprintf('; charset=%s', $renderer->getCharset()) : ''
                )
            );

            $headers = array_merge($defaultHeaders, $commandReceiver->getHttpHeaders());
        }

        // Send response to client
        $this->sendHttpResponse($content, $headers, $output);

        // Accept new requests, by unlocking the session:
        if ($this->session->isStarted()) {
            $this->session->unlock();
        }

        if ($request->isValidated()) {
            // Run the "post-response" event queue (see #506)
            $platform->runEvents('post-response');
        }

        return 0;
    }

    public function initializeModule(Module\ModuleInterface $module)
    {
        if ($module instanceof \Nethgui\Utility\SessionConsumerInterface) {
            $module->setSession($this->session);
        }

        if ($module instanceof Authorization\PolicyEnforcementPointInterface) {
            $module->setPolicyDecisionPoint($this->pdp);
        }
    }

    /**
     *
     * @param string $content
     * @param array $headers
     * @param array $output
     */
    private function sendHttpResponse($content, $headers, &$output)
    {
        if (is_array($output)) {
            // put HTTP response into the output array:
            $output['content'] = $content;
            $output['headers'] = $headers;
        } else {
            // send HTTP response to stdout:
            array_map('header', $headers);
            echo $content;
            flush();
        }
    }

    /**
     *
     * @param string $targetFormat
     * @param \Nethgui\View\ViewInterface $view
     * @param \Nethgui\View\CommandReceiverInterface $receiver
     * @return Renderer\AbstractRenderer
     */
    private function getRenderer($targetFormat, \Nethgui\View\ViewInterface $view, \Nethgui\View\CommandReceiverInterface $receiver)
    {
        if ($targetFormat === 'json') {
            $renderer = new Renderer\Json($view, $receiver);
        } elseif ($targetFormat === 'xhtml') {
            $renderer = new Renderer\Xhtml($view, $this->getFileNameResolver(), 0);
        } else if ($targetFormat === 'js') {
            $renderer = new Renderer\TemplateRenderer($view, $this->getFileNameResolver(), 'application/javascript', 'UTF-8');
        } elseif ($targetFormat === 'css') {
            $renderer = new Renderer\TemplateRenderer($view, $this->getFileNameResolver(), 'text/css', 'UTF-8');
        } else {
            $renderer = new Renderer\TemplateRenderer($view, $this->getFileNameResolver(), 'text/plain', 'UTF-8');
        }

        return $renderer;
    }

    /**
     * Vist all sub-views and execute their commands.
     * 
     * These does not include ALL possible commands!
     * 
     * @param View\View $view
     */
    private function executeViewCommands(View\View $view)
    {
        $q = array($view);

        while (count($q) > 0) {
            $view = array_pop($q);

            foreach ($view as $key => $value) {
                if ($value instanceof View\View) {
                    $q[] = $value;
                }
            }

            if (strlen($view->getModule()->getIdentifier()) == 0) {
                $command = $view->getCommandList('/' . array_end(explode('\\', get_class($view->getModule()))));
            } else {
                $command = $view->getCommandList();
            }

            if ( ! $command->isExecuted()) {
                $command->setReceiver($view)->execute();
            }
        }
    }

    /**
     * Create a default Request object for dispatch()
     *
     * @api
     * @see dispatch()
     * @param integer $type - Not used
     * @return \Nethgui\Controller\Request
     */
    public function createRequest($type = NULL)
    {
        return $this->createRequestModApache();
    }

    /**
     * Creates a new \Nethgui\Controller\Request object from
     * current HTTP request.
     *
     * @param array $parameters
     * @return \Nethgui\Controller\Request
     */
    private function createRequestModApache()
    {
        if (ini_get("magic_quotes_gpc")) {
            throw new \LogicException("magic_quotes_gpc directive must be disabled!", 1377176328);
        }

        $isMutation = FALSE;
        $postData = array();
        $getData = $_GET;
        $log = new \Nethgui\Log\Syslog();
        $pathInfo = array();
        $languageCode = '';
        $languageCodeDefault = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);

        // Split PATH_INFO
        if (isset($_SERVER['PATH_INFO']) && $_SERVER['PATH_INFO'] != '/') {
            $pathInfo = array_rest(explode('/', $_SERVER['PATH_INFO']));

            $pathHead = array_head($pathInfo);

            // FIXME: read the language codes from Language/ subdirs
            if ( ! in_array($pathHead, array('en', 'it'))) {
                throw new Exception\HttpException('Language not found', 404, 1377519247);
            }
            $languageCode = $pathHead;
            $pathInfo = array_rest($pathInfo);


            foreach ($pathInfo as $pathPart) {
                if ($pathPart === '.' || $pathPart === '..' || $pathPart === '') {
                    throw new Exception\HttpException('Bad Request', 400, 1322217901);
                }
            }
        }

        // Append the language code to the url parts:
        $this->urlParts[] = ($languageCode ? $languageCode : $languageCodeDefault) . '/';

        // Extract the requested output format (xhtml, json...)
        $format = $this->extractTargetFormat($pathInfo);

        // Transform the splitted PATH_INFO into a nested array, where each
        // PATH_INFO part is the key of a nested level:
        $pathInfoMod = array();
        $cur = &$pathInfoMod;
        foreach ($pathInfo as $pathPart) {
            $cur[$pathPart] = array();
            $cur = &$cur[$pathPart];
        }

        // FIXME:
        // Copy root level scalar GET variables into the current module for backward
        // compatibility:
        $cur = array_merge(array_filter($_GET, 'is_string'), $cur);

        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $isMutation = TRUE;

            if (isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] == 'application/json; charset=UTF-8') {
                // Decode RAW request
                $postData = json_decode($GLOBALS['HTTP_RAW_POST_DATA'], true);

                if (is_null($postData)) {
                    throw new \Nethgui\Exception\HttpException('Bad Request', 400, 1322148404);
                }
            } else {
                // Use PHP global:
                $postData = $_POST;
            }
        }

        $R = array_replace_recursive($pathInfoMod, $getData, $postData);
        $request = new \Nethgui\Controller\Request($R);
        $request->setLog($log)
            ->setAttribute('isMutation', $isMutation)
            ->setAttribute('format', $format)
            ->setAttribute('languageCode', $languageCode)
            ->setAttribute('languageCodeDefault', $languageCodeDefault)
        ;

        return $request;
    }

    private function extractTargetFormat(&$pathInfo)
    {
        $lastPart = array_pop($pathInfo);

        $ext = '';

        if ( ! is_string($lastPart)) {
            return '';
        }

        $dotPos = strrpos($lastPart, '.');

        if ($dotPos !== FALSE) {
            $ext = substr($lastPart, $dotPos + 1);

            // TODO: register handled extension elsewhere:
            if (in_array($ext, array('js', 'css', 'xhtml', 'json', 'txt'))) {
                $lastPart = substr($lastPart, 0, $dotPos);
            } else {
                $ext = 'xhtml';
            }
        } else {
            $ext = 'xhtml';
        }

        $pathInfo[] = $lastPart;

        if (preg_match('/[a-z][a-zA-Z]*/', $ext) === 0) {
            throw new \Nethgui\Exception\HttpException('Bad Request', 400, 1324457459);
        }

        return $ext;
    }

    /**
     * Send a plain text formatted string as server-response.
     *
     * @param \Nethgui\Exception\HttpException $ex
     * @return void;
     * @api
     */
    public function printHttpException(\Nethgui\Exception\HttpException $ex, $backtrace = TRUE)
    {
        header(sprintf('HTTP/1.1 %s %s', $ex->getHttpStatusCode(), $ex->getMessage()));
        header('Content-Type: text/plain; charset=UTF-8');
        echo sprintf("Nethgui:\n\n    %d - %s [%s]\n\n\n\n", $ex->getHttpStatusCode(), $ex->getMessage(), $ex->getCode());

        if ($backtrace) {
            echo sprintf("Exception backtrace:\n\n%s\n\n", $ex->getTraceAsString());
            $prev = $ex->getPrevious();
            if ($prev instanceof \Exception) {
                echo sprintf("Previous exception:\n\n    %s [%s]\n\n", $prev->getMessage(), $prev->getCode());
                echo $prev->getTraceAsString();
            }
        }
    }

}
/*
 * Framework global symbols
 */

if ( ! defined('NETHGUI_ENABLE_TARGET_HASH')) {
    // TRUE: pass client names through an hash function
    define('NETHGUI_ENABLE_TARGET_HASH', TRUE);
}

if ( ! defined('NETHGUI_ENABLE_INCLUDE_WIDGET')) {
    // TRUE: widget javascript code is included automatically by Module\Main
    define('NETHGUI_ENABLE_INCLUDE_WIDGET', FALSE);
}

if ( ! defined('NETHGUI_ENABLE_HTTP_CACHE_HEADERS')) {
    // TRUE: send cache headers
    define('NETHGUI_ENABLE_HTTP_CACHE_HEADERS', TRUE);
}

if ( ! defined('NETHGUI_DEBUG')) {
    // TRUE: more verbose logs, non-minified js and css
    define('NETHGUI_DEBUG', FALSE);
}

/**
 *
 * @param array $arr
 * @return mixed The first element of the array or FALSE if the array is empty
 */
function array_head($arr)
{
    return reset($arr);
}

/**
 *
 * @param array $arr
 * @return mixed The last element of the array or FALSE if the array is empty
 */
function array_end($arr)
{
    return end($arr);
}

/**
 *
 * @param array $arr
 * @return array A new array corresponding to the original without the head element
 */
function array_rest($arr)
{
    array_shift($arr);
    return $arr;
}

