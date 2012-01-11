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
 * @see #dispatch(Core\RequestInterface $request)
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
     * Identifier of the layout decorator module
     *
     * @var string
     */
    private $decoratorTemplate;

    public function __construct()
    {
        spl_autoload_register(array($this, 'autoloader'));
        if (basename(__DIR__) !== __NAMESPACE__) {
            throw new \LogicException(sprintf('%s: `%s` is an invalid framework filesystem directory! Must be `%s`.', get_class($this), basename(__DIR__), __NAMESPACE__), 1322213425);
        }
        $this->registerNamespace(__DIR__);

        $this->siteUrl = $this->guessSiteUrl();
        $this->basePath = $this->guessBasePath();
        $this->decoratorTemplate = 'Nethgui\Template\Main';
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

    private function guessSiteUrl()
    {
        $tmpSiteUrl = empty($_SERVER['HTTPS']) ? 'http://' : 'https://';
        $tmpSiteUrl .= isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost';
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
        return '/' . implode('/', $parts);
    }

    /**
     * The web site URL without trailing slash
     *
     * @example http://www.example.org:8080
     * @api
     * @param string $url
     * @return Framework
     */
    public function setSiteUrl($url)
    {
        $this->siteUrl = $url;
        return $this;
    }

    /**
     * The path component of an URL with a leading slash
     *
     * @example /my/path/to/the/app
     * @api
     * @param type $basePath
     * @return Framework
     */
    public function setBasePath($basePath)
    {
        $this->basePath = $basePath;
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
     * Translate a namespaced classifier (interface, class) or a namespaced-script-name
     * into a filesystem path.
     *
     * This is equivalent to the autoloader() function
     *
     * @example Nethgui_Template_Help is converted into /abs/path/Nethgui/Template/Help.php
     * @param string $symbol A "namespace" classifier or script file name (without .php extension)
     * @return string The absolute script path of $symbol
     */
    public function absoluteScriptPath($symbol)
    {
        $nsKey = array_head(explode('\\', $symbol));

        $ext = pathinfo($symbol, PATHINFO_EXTENSION) ? '' : '.php';

        if (isset($this->namespaceMap[$nsKey])) {
            return $this->namespaceMap[$nsKey] . '/' . str_replace('\\', '/', $symbol) . $ext;
        }

        return FALSE;
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
     * @param string $currentModuleIdentifier
     * @param array $arguments
     * @return integer
     */
    public function dispatch(Core\RequestInterface $request, &$output = NULL)
    {
        if (array_head($request->getPath()) === FALSE) {
            $redirectUrl = implode('/', array($this->basePath, 'index.php', $this->defaultModuleIdentifier));
            $this->sendHttpResponse('', array('HTTP/1.1 302 Found', sprintf('Location: %s', $redirectUrl)), $output);
            return;
        }

        // TODO: enforce some security policy on Models
        $pdp = new Authorization\PermissivePolicyDecisionPoint();
        $user = $request->getUser();
        $session = $user->getSession();
        $moduleLoader = new Core\ModuleLoader($this->namespaceMap);

        $platform = new System\NethPlatform($user);
        $platform->setPolicyDecisionPoint($pdp);

        $validationErrorsNotification = new Client\ValidationErrorsNotification();

        $moduleLoader->getModule('Notification')->setSession($session);

        $mainModule = new Module\Main($this->decoratorTemplate, $moduleLoader, $this->getFileNameResolver());
        $mainModule->setPlatform($platform);
        $mainModule->initialize();
        $mainModule->bind($request);

        $mainModule->validate($validationErrorsNotification);

        if ( ! $validationErrorsNotification->hasValidationErrors()) {
            $request->setAttribute('validated', TRUE);
            $mainModule->process();
            // Finally, signal "post-process" events (see #506)
            $platform->runEvents('post-process');
        }

        $targetFormat = $request->getExtension();
        $translator = new Language\Translator($user, $platform->getLog(), $this->getFileNameResolver(), array_keys($this->namespaceMap));
        $urlParts = array($this->siteUrl, $this->basePath, 'index.php');
        $rootView = new Client\View($targetFormat, $mainModule, $translator, $urlParts);

        $commandReceiver = new Renderer\HttpCommandReceiver(new Renderer\MarshallingReceiver(new Core\LoggingCommandReceiver()));

        $rootView->setReceiver($commandReceiver);

        // ..transfer contents and commands into the MAIN view:
        $mainModule->prepareView($rootView);

        if ($validationErrorsNotification->hasValidationErrors()) {
            // Only validation errors notification has to be shown: clear
            // all enqueued commands.
            $rootView->clearAllCommands();

            // Validation error http status.
            // See RFC2616, section 10.4 "Client Error 4xx"
            // FIXME: check if we are in FAST-CGI module:
            // @see http://php.net/manual/en/function.header.php
            $rootView->getCommandListFor('/Notification')
                ->showNotification($validationErrorsNotification)
                ->httpHeader("HTTP/1.1 400 Request validation error")
            ;
        }

        // ..Execute commands sent to views. These do not include commands sent to widgets:
        $this->executeViewCommands($rootView);

        if ($targetFormat === 'xhtml' && $commandReceiver->hasRedirect()) {
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

            $headers = array_merge(
                $defaultHeaders, $commandReceiver->getHttpHeaders()
            );
        }

        $this->sendHttpResponse($content, $headers, $output);

        $platform->runEvents('post-response');

        return 0;
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
     * @param \Nethgui\Core\ViewInterface $view
     * @param \Nethgui\Core\CommandReceiverInterface $receiver
     * @return Renderer\Text 
     */
    private function getRenderer($targetFormat, \Nethgui\Core\ViewInterface $view, \Nethgui\Core\CommandReceiverInterface $receiver)
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
     * @param Client\View $view
     */
    private function executeViewCommands(Client\View $view)
    {
        $q = array($view);

        while (count($q) > 0) {
            $view = array_pop($q);

            foreach ($view as $key => $value) {
                if ($value instanceof Client\View) {
                    $q[] = $value;
                }
            }

            if (strlen($view->getModule()->getIdentifier()) == 0) {
                $command = $view->getCommandListFor('/' . array_end(explode('\\', get_class($view->getModule()))));
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

        $submitted = FALSE;
        $postData = array();
        $getData = array();

        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $submitted = TRUE;

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
        } elseif (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'GET') {
            $getData = $_GET;
        }

        // TODO: retrieve user state from Session
        $user = new Client\AlwaysAuthenticatedUser(new Client\Session());

        $attributes = new \ArrayObject();

        $attributes['extension'] = $this->extractTargetFormat($pathInfo);
        $attributes['submitted'] = $submitted;
        $attributes['validated'] = FALSE;

        $instance = new Client\Request($user, $postData, $getData, $pathInfo, $attributes);

        /*
         * Clear global variables
         */
        $_POST = array();
        $_GET = array();

        return $instance;
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
            if (in_array($ext, array('js', 'css', 'xhtml', 'json'))) {
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
    public function printHttpException(\Nethgui\Exception\HttpException $ex)
    {
        header(sprintf('HTTP/1.1 %s %s', $ex->getHttpStatusCode(), $ex->getMessage()));
        header('Content-Type: text/plain; charset=UTF-8');
        echo sprintf("Nethgui - fatal error:\n\n    %s [%s]\n\n\n\n", $ex->getMessage(), $ex->getCode());
        echo "Exception backtrace:\n\n";
        echo $ex->getTraceAsString();

        $prev = $ex->getPrevious();
        if ($prev instanceof \Exception) {
            echo sprintf("\n\nPrevious exception:\n\n    %s [%s]\n\n", $prev->getMessage(), $prev->getCode());
            echo $prev->getTraceAsString();
        }
    }

}

/*
 * Framework global symbols
 */

/*
 * NETHGUI_ENABLE_TARGET_HASH: if set to TRUE pass client names through an hash function
 */
if ( ! defined('NETHGUI_ENABLE_TARGET_HASH')) {
    define('NETHGUI_ENABLE_TARGET_HASH', FALSE);
}

if ( ! defined('NETHGUI_ENABLE_INCLUDE_WIDGET')) {
    define('NETHGUI_ENABLE_INCLUDE_WIDGET', FALSE);
}

if ( ! defined('NETHGUI_ENABLE_HTTP_CACHE_HEADERS')) {
    define('NETHGUI_ENABLE_HTTP_CACHE_HEADERS', TRUE);
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

