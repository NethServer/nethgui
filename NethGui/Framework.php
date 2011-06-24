<?php
/**
 * @package NethGui
 */

/**
 * @package NethGui
 */
class NethGui_Framework
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

    /**
     * Returns framework singleton instance.
     * @staticvar NethGui_Framework $instance
     * @return NethGui_Framework
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
        spl_autoload_register(get_class($this) . '::autoloader');
        ini_set('include_path', ini_get('include_path') . ':' . realpath(dirname(__FILE__) . '/..'));

        $this->controller = $codeIgniterController;
        $this->languageCatalogStack = array(get_class());
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
            $ciViewPath = '../../' . str_replace('_', '/', $viewName);

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
     * @see anchor()
     * @param NethGui_Core_ModuleInterface $module
     * @return <type>
     */
    public function renderModuleAnchor(NethGui_Core_ModuleInterface $module)
    {
        $html = '';

        if (strlen($module->getTitle()) == 0) {
            return '';
        }

        if ( ! $module instanceof NethGui_Core_RequestHandlerInterface) {
            $html = '<div class="moduleTitle" title="' . htmlspecialchars(T($module->getDescription())) . '"><a href="#">' . htmlspecialchars(T($module->getTitle())) . '</a></div>';
        } else {
            $ciControllerClassName = $this->getControllerName();

            $moduleTitle = $module->getTitle();
            if ($module instanceof NethGui_Core_LanguageCatalogProvider) {
                $catalog = $module->getLanguageCatalog();
                $moduleTitle = $this->translate($moduleTitle, array(), NULL, $catalog);
            }

            $html = anchor($ciControllerClassName . '/' . $module->getIdentifier(), htmlspecialchars($moduleTitle), array('class' => 'moduleTitle ' . $module->getIdentifier(), 'title' => htmlspecialchars($module->getDescription())
                )
            );
        }

        return $html;
    }

    /**
     * @param string|array $path
     * @param array $parameters
     */
    public function buildUrl($path, $parameters)
    {
        if (is_array($path)) {
            $path = implode('/', $path);
        }

        $path = explode('/', $path);

        // FIXME: the controller name must not be added
        // if url-rewriting (or similar) is enabled
        array_unshift($path, $this->getControllerName());

        $path = array_reverse($path);

        $segments = array();

        while (list($index, $slice) = each($path)) {
            if ($slice == '..') {
                next($path);
                continue;
            }

            array_unshift($segments, $slice);
        }

        if ( ! empty($parameters)) {
            $url = site_url($segments) . '?' . http_build_query($parameters);
        } else {
            $url = site_url($segments);
        }
        return $url;
    }

    /**
     * Prepend the $module path to $path, resulting in a full URL
     * @param NethGui_Core_ModuleInterface  $module
     * @param array|string $path;
     */
    public function buildModuleUrl(NethGui_Core_ModuleInterface $module, $path)
    {
        if(is_string($path)) {
            $path = array($path);
        }
        
        do {
            array_unshift($path, $module->getIdentifier());
            $module = $module->getParent();
        } while ( ! is_null($module));

        return NethGui_Framework::getInstance()->buildUrl($path, array());
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
        return strtr($translation, $args);
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
            }

            // If key is missing lookup in previous catalog
            $catalog = prev($languageCatalogs);
        }

        if ($translation === NULL) {
            // By default prepare an identity-translation
            $translation = $key;
            if (ENVIRONMENT == 'development') {
                $this->logMessage("Missing `$languageCode` translation for `$key`. Catalogs: " . implode(', ', $languageCatalogs), 'debug');
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
     * Class autoloader
     *
     * This function is registered as SPL class autoloader.
     *
     * @todo XXX Check for class names cheating!
     * @param string $className
     * @return void
     */
    static public function autoloader($className)
    {
        /* Skip CodeIgniter "namespace" */
        if (substr($className, 0, 3) == 'CI_') {
            return;
        }
        $classPath = str_replace("_", "/", $className) . '.php';
        require($classPath);
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
            $this->redirect('dispatcher/Security');
        }

        $request = NethGui_Core_Request::getHttpRequest($arguments);

        $user = $request->getUser();

        /*
         * Create models.
         *
         * TODO: get hostConfiguration and topModuleDepot class names
         * from NethGui_Framework.
         */
        $hostConfiguration = new NethGui_Core_HostConfiguration($user);
        // TODO: set a configuration parameter for the application path
        $appPath = realpath(dirname(__FILE__) . '/../NethService');
        $this->languageCatalogStack[] = basename($appPath);
        $topModuleDepot = new NethGui_Core_TopModuleDepot($appPath, $hostConfiguration, $user);

        /*
         * TODO: enforce some security policy on Models
         */
        $pdp = new NethGui_Authorization_PermissivePolicyDecisionPoint();

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

        $notificationManager = new NethGui_Module_NotificationArea($user);
        $notificationManager->setHostConfiguration($hostConfiguration);

        $topModuleDepot->registerModule($notificationManager);

        // The World module is a non-processing container.
        $worldModule = new NethGui_Module_World();
        $worldModule->setHostConfiguration($hostConfiguration);

        $view = new NethGui_Core_View($worldModule);

        $processExitCode = NULL;

        foreach ($moduleWakeupList as $moduleIdentifier) {
            $module = $topModuleDepot->findModule($moduleIdentifier);

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

        $worldModule->addModule($notificationManager);

        // Raise asynchronous events
        $eventStatus = $hostConfiguration->raiseAsyncEvents();
        if ($eventStatus === TRUE)
        {
            // If at least one event occurred, show a successful dialog box:
            $user->showDialogBox($worldModule, 'All changes have been saved');
        } elseif ($eventStatus === FALSE) {
            $user->showDialogBox($worldModule, 'Some error occurred. Check the system log for details.', array(), NethGui_Core_DialogBox::NOTIFY_WARNING);
        }


        if ($request->getContentType() === NethGui_Core_Request::CONTENT_TYPE_HTML) {
            $redirect = $user->getRedirect();
            if ( ! is_null($redirect)) {
                list($module, $path) = $redirect;
                $this->redirect($this->buildModuleUrl($module, $path));
            }
            $worldModule->addModule(new NethGui_Module_Menu($topModuleDepot->getModules()));
            header("Content-Type: text/html; charset=UTF-8");
            $worldModule->prepareView($view, NethGui_Core_ModuleInterface::VIEW_REFRESH);
            echo $view->render();
        } elseif ($request->getContentType() === NethGui_Core_Request::CONTENT_TYPE_JSON) {
            header("Content-Type: application/json; charset=UTF-8");
            $worldModule->prepareView($view, NethGui_Core_ModuleInterface::VIEW_UPDATE);
            echo json_encode($view->getArrayCopy());
        }

        // Control reaches this point only if no redirect occurred.
        $notificationManager->dismissTransientDialogBoxes();
    }

}

/**
 * This is a shortcut to NethGui_Framework::translate() 
 * @see NethGui_Framework::translate()
 */
if ( ! function_exists('T')) {

    function T($string, $args = array(), $language = NULL, $catalog = NULL, $hsc = TRUE)
    {

        $t = NethGui_Framework::getInstance()->translate($string, $args, $language, $catalog);

        if ($hsc) {
            $t = htmlspecialchars($t);
        }

        return $t;
    }

}