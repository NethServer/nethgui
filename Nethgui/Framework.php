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
     * Underlying Code Igniter framework controller.
     * @var CI_Controller
     */
    private $controller;
    private $languageCode = 'it';

    /**
     * This is a stack of catalog names. Current catalog is the last element
     * of the array.
     * @var array
     */
    private $languageCatalogStack;
    private $catalogs = array();
    private static $path;

    /**
     * Returns framework singleton instance.
     * @staticvar Nethgui_Framework $instance
     * @return Nethgui_Framework
     */
    static public function getInstance(CI_Controller $codeIgniterController = NULL)
    {
        static $instance;
        if ( ! isset($instance)) {
            $instance = new self($codeIgniterController);
        }
        return $instance;
    }

    private function __construct($codeIgniterController)
    {
        $this->controller = $codeIgniterController;
        $this->languageCatalogStack = array(get_class());
        self::$path = realpath(dirname(__FILE__) . '/..');
        spl_autoload_register(get_class($this) . '::autoloader');
        ini_set('include_path', ini_get('include_path') . ':' . self::$path);
    }

    /**
     * @return CI_Controller
     */
    public function getControllerName()
    {
        return strtolower(get_class($this->controller));
    }

    /**
     * Renders a view passing $viewState as view parameters.
     *
     * If specified, this function sets the default language catalog used
     * by T() translation function.
     *
     * @param string|callable $view Full view name that follows class naming convention or function callback
     * @param array $viewState Array of view parameters.
     * @param string|array $languageCatalog Name of language strings catalog.
     * @return string
     */
    public function renderView($viewName, $viewState, $languageCatalog = NULL)
    {
        if ($viewName === FALSE) {
            return '';
        }

        if ( ! is_null($languageCatalog) && ! empty($languageCatalog)) {
            if (is_array($languageCatalog)) {
                $languageCatalog = array_reverse($languageCatalog);
            }

            $this->languageCatalogStack[] = $languageCatalog;
        }

        if (is_callable($viewName)) {
            // Callback
            $viewOutput = (string) call_user_func_array($viewName, $viewState);
        } else {
            $ciViewPath = '../../../' . str_replace('_', '/', $viewName);

            $absoluteViewPath = realpath(APPPATH . 'views/' . $ciViewPath . '.php');

            if ( ! $absoluteViewPath) {
                $this->logMessage("Unable to load `{$viewName}`.", 'warning');
                return '';
            }

            // PHP script
            $viewOutput = (string) $this->controller->load->view($ciViewPath, $viewState, true);
        }

        if ( ! is_null($languageCatalog) && ! empty($languageCatalog)) {
            array_pop($this->languageCatalogStack);
        }

        return $viewOutput;
    }

    /**
     * @param string|array $path
     * @param array $parameters
     */
    public function buildUrl($path, $parameters = array())
    {
        $fragment = '';

        if (is_array($path)) {
            $path = implode('/', $path);
        }

        $path = explode('/', $path);
        $path = array_reverse($path);
        $segments = array();

        while (list($index, $slice) = each($path)) {
            if ($slice == '.' || ! $slice) {
                continue;
            } elseif ($slice == '..') {
                next($path);
                continue;
            } elseif ($slice[0] == '#') {
                $fragment = $slice;
                continue;
            }

            array_unshift($segments, $slice);
        }

        // FIXME: skip controller segments if url rewriting is active:
        array_unshift($segments, 'index.php', $this->getControllerName());

        if ( ! empty($parameters)) {
            $url = $this->baseUrl($segments) . '?' . http_build_query($parameters);
        } else {
            $url = $this->baseUrl($segments);
        }

        return $url . $fragment;
    }

    /**
     * Prefix the given segments with the URL path to the controller.
     * 
     * @staticvar type $baseUrl
     * @param type $segments
     * @return type
     */
    public function baseUrl($segments = array())
    {
        static $baseUrl;

        if ( ! isset($baseUrl)) {


            $parts = explode('/', $_SERVER['SCRIPT_NAME']);
            $lastPart = $parts[max(0, count($parts) - 1)];
            $nethguiFile = basename(NETHGUI_FILE);

            if ($lastPart == $nethguiFile) {
                array_pop($parts);
            }
            $baseUrl = implode('/', $parts);
        }

        return $baseUrl . '/' . implode('/', $segments);
    }

    /**
     * Prepend the $module path to $path, resulting in a full URL
     * @param Nethgui_Core_ModuleInterface  $module
     * @param array|string $path;
     */
    public function buildModuleUrl(Nethgui_Core_ModuleInterface $module, $path = array())
    {
        if (is_string($path)) {
            $path = array($path);
        }

        do {
            array_unshift($path, $module->getIdentifier());
            $module = $module->getParent();
        } while ( ! is_null($module));

        return $this->buildUrl($path, array());
    }

    /**
     * Translate $string substituting $args
     *
     * Each key in array $args is searched and replaced in $string with
     * correspondent value.
     *
     * @see strtr()
     *
     * @param string $string The string to be translated
     * @param array $args Values substituted in output string.
     * @param string $languageCode The language code
     * @param string|array $catalog The catalog or the catalog list where to search for the translation
     * @return string
     */
    public function translate($string, $args, $languageCode = NULL, $catalog = NULL)
    {
        if ( ! is_string($string)) {
            throw new InvalidArgumentException(sprintf("translate(): unexpected `%s` type!", gettype($string)));
        }

        if ( ! isset($languageCode)) {
            $languageCode = $this->languageCode;
        }

        if (empty($languageCode)) {
            $translation = $string;
        } else {

            if (is_array($catalog)) {
                $catalog = array_reverse($catalog);
            } elseif ( ! empty($catalog)) {
                $catalog = array($catalog);
            } else {
                $catalog = array();
            }

            $translation = $this->lookupTranslation($string, $languageCode, $catalog);
        }

        /**
         * Apply args to string
         */
        if (empty($args)) {
            return $translation;
        }

        /**
         * Automatically susbstitute numeric keys with ${N} placeholders.
         */
        $placeholders = array();
        foreach ($args as $argId => $argValue) {
            if (is_numeric($argId)) {
                $placeholders[sprintf('${%d}', $argId)] = $argValue;
            } else {
                $placeholders[$argId] = $argValue;
            }
        }

        return strtr($translation, $placeholders);
    }

    /**
     * @param string $key The string to be translated
     * @param string $languageCode The language code of the translated string
     * @param array $catalogStack The catalog stack where to start the search
     * @return string The translated string 
     */
    private function lookupTranslation($key, $languageCode, $catalogStack)
    {
        $languageCatalogs = $this->languageCatalogStack;

        if ( ! empty($catalogStack)) {
            $languageCatalogs[] = $catalogStack;
        }

        $translation = NULL;
        $attempts = array();

        while (($catalog = array_pop($languageCatalogs)) !== NULL) {

            if (is_array($catalog)) {
                // push nested catalog stack elements
                $languageCatalogs = array_merge($languageCatalogs, $catalog);
                continue;
            }

            // If catalog is missing load it
            if ( ! isset($this->catalogs[$languageCode][$catalog])) {
                $this->loadLanguageCatalog($languageCode, $catalog);
            }

            // If key exists break
            if (isset($this->catalogs[$languageCode][$catalog][$key])) {
                $translation = $this->catalogs[$languageCode][$catalog][$key];
                break;
            } else {
                $attempts[] = $catalog;
            }
        }

        if ($translation === NULL) {
            // By default prepare an identity-translation
            $translation = $key;
            if (ENVIRONMENT == 'development') {
                $this->logMessage("Missing `$languageCode` translation for `$key`. Catalogs: " . implode(', ', $attempts), 'debug');
            }
        }

        return $translation;
    }

    private function loadLanguageCatalog($languageCode, $languageCatalog)
    {
        $L = array();

        if (preg_match('/[a-z][a-z]/', $languageCode) == 0) {
            throw new InvalidArgumentException('Language code must be a valid ISO 639-1 language code');
        }

        if (preg_match('/[a-z_A-Z0-9]+/', $languageCatalog) == 0) {
            throw new InvalidArgumentException("Language catalog name can contain only alphanumeric or `_` characters. It was `$languageCatalog`.");
        }

        $prefix = array_shift(explode('_', $languageCatalog));
        $filePath = dirname(__FILE__) . '/../' . $prefix . '/Language/' . $languageCode . '/' . $languageCatalog . '.php';
        @include($filePath);

        if (ENVIRONMENT == 'development' && ! empty($L)) {
            $this->logMessage('Loaded catalog ' . $filePath);
        }

        $this->catalogs[$languageCode][$languageCatalog] = &$L;
    }

    /**
     * Set the current language code
     * @param string $code ISO 639-1 language code (2 characters).
     */
    public function setLanguageCode($code)
    {
        $this->languageCode = strtolower(substr($code, 0, 2));
    }

    /**
     * Get the current language code
     * @return string ISO 639-1 language code (2 characters).
     */
    public function getLanguageCode()
    {
        return $this->languageCode;
    }

    /**
     * Get the date format according to the current language
     * @return string
     */
    public function getDateFormat()
    {
        switch ($this->getLanguageCode()) {
            case 'xx': // UNUSED - middle endian
                $format = 'mm-dd-YYYY';
                break;
            case 'yy': // UNUSER - little endian
                $format = 'dd/mm/YYYY';
                break;
            default: // big endian ISO 8601
                $format = 'YYYY-mm-dd';
        }

        return $format;
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
        $classPath = self::$path . '/' . str_replace("_", "/", $className) . '.php';
        include $classPath;
    }

    public function getApplicationPath()
    {
        return realpath(self::$path . '/' . NETHGUI_APPLICATION);
    }

    /**
     * Send a message to logging facility.
     * @param string $message
     * @param string $level
     */
    public function logMessage($message, $level = 'error')
    {
        log_message($level, $message);
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
            $this->redirect('dispatcher/Dashboard');
        }

        $request = Nethgui_Core_Request::getHttpRequest($arguments);

        $user = $request->getUser();

        /*
         * Create models.
         *
         * TODO: get hostConfiguration and topModuleDepot class names
         * from Nethgui_Framework.
         */
        $hostConfiguration = new Nethgui_Core_HostConfiguration($user);
        $appPath = realpath(dirname(__FILE__) . '/../' . NETHGUI_APPLICATION);
        $this->languageCatalogStack[] = basename($appPath);
        $topModuleDepot = new Nethgui_Core_TopModuleDepot($appPath, $hostConfiguration, $user);

        /*
         * TODO: enforce some security policy on Models
         */
        $pdp = new Nethgui_Authorization_PermissivePolicyDecisionPoint();

        $hostConfiguration->setPolicyDecisionPoint($pdp);
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

        $notificationManager = new Nethgui_Module_NotificationArea($user);
        $notificationManager->setHostConfiguration($hostConfiguration);

        $topModuleDepot->registerModule($notificationManager);

        $helpModule = new Nethgui_Module_Help();
        $helpModule->moduleSet = $topModuleDepot;
        $helpModule->setHostConfiguration($hostConfiguration);
        $topModuleDepot->registerModule($helpModule);
        
        $menuModule = new Nethgui_Module_Menu($topModuleDepot->getModules(),$currentModuleIdentifier);
        $menuModule->setHostConfiguration($hostConfiguration);
        $topModuleDepot->registerModule($menuModule);


        // The World module is a non-processing container.
        $worldModule = new Nethgui_Module_World();
        $worldModule->setHostConfiguration($hostConfiguration);

        $view = new Nethgui_Core_View($worldModule);

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
        $hostConfiguration->signalFinalEvents();

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
            if ($redirectUrl !== FALSE) {
                $this->redirect($redirectUrl);
            }
            header("Content-Type: text/html; charset=UTF-8");
            echo new Nethgui_Renderer_Xhtml($view);
        } elseif ($request->getContentType() === Nethgui_Core_Request::CONTENT_TYPE_JSON) {
            $worldModule->prepareView($view, Nethgui_Core_ModuleInterface::VIEW_CLIENT);
            $events = $view->getClientEvents();
            $redirectUrl = $this->getRedirectUrl($user);
            $clientCommands = $this->clientCommandsToArray($user->getClientCommands());
            if ( ! empty($clientCommands)) {
                $events[] = array('ClientCommandHandler', $clientCommands);
            }

            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode($events);
        } else {
            $redirectUrl = FALSE;
        }

        // Dismiss transient dialog boxes only if no redirection or fake redirection occurred:
        if ($redirectUrl === FALSE) {
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
     * @param Nethgui_Core_UserInterface $user
     * @return string|bool The URL where to redirect the user
     */
    private function getRedirectUrl(Nethgui_Core_UserInterface $user)
    {
        foreach ($user->getClientCommands() as $command) {
            if ($command instanceof Nethgui_Client_CommandInterface && $command->isRedirection()) {
                return $command->getRedirectionUrl();
            }
        }

        return FALSE;
    }

    /**
     * Convert the given hash to the array format accepted from UI widgets as
     * "datasource".
     *
     * @param array $h
     * @return array
     */
    public static function hashToDatasource($H)
    {
        $D = array();

        foreach ($H as $k => $v) {
            if (is_array($v)) {
                $D[] = array(self::hashToDatasource($v), $k);
            } elseif (is_string($v)) {
                $D[] = array($k, $v);
            }
        }

        return $D;
    }

}

/**
 * This is a shortcut to Nethgui_Framework::translate() 
 * @see Nethgui_Framework::translate()
 */
if ( ! function_exists('T')) {

    function T($string, $args = array(), $language = NULL, $catalog = NULL, $hsc = TRUE)
    {

        $t = Nethgui_Framework::getInstance()->translate($string, $args, $language, $catalog);

        if ($hsc) {
            $t = htmlspecialchars($t);
        }

        return $t;
    }

}
