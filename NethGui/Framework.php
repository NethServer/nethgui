<?php
/**
 * @package NethGui
 */

/**
 * @package NethGui
 */
final class NethGui_Framework
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

    private function __construct(CI_Controller $codeIgniterController)
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
     * @param string $languageCatalog Name of language strings catalog.
     * @return string
     */
    public function renderView($viewName, $viewState, $languageCatalog = NULL)
    {
        if ( ! is_null($languageCatalog)) {
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

            // PHP view
            $viewOutput = (string) $this->controller->load->view($ciViewPath, $viewState, true);
        }

        if ( ! is_null($languageCatalog)) {
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

        $currentModule = FALSE;

        if ($module === $currentModule) {
            $html = '<span class="moduleTitle current" title="' . htmlspecialchars($module->getDescription()) . '">' . htmlspecialchars($module->getTitle()) . '</span>';
        } else {
            $ciControllerClassName = $this->getControllerName();

            $moduleTitle = $module->getTitle();
            if ($module instanceof NethGui_Core_LanguageCatalogProvider) {
                $catalog = $module->getLanguageCatalog();
                $moduleTitle = T($moduleTitle, array(), NULL, $catalog);
            }

            $html = anchor($ciControllerClassName . '/' . $module->getIdentifier(),
                    htmlspecialchars($moduleTitle),
                    array('class' => 'moduleTitle', 'title' => htmlspecialchars($module->getDescription())
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
     * @param string $languageCatalog The catalog where to search the translation
     * @return string
     */
    public function translate($string, $args, $languageCode = NULL, $languageCatalog = NULL)
    {
        if ( ! isset($languageCode)) {
            $languageCode = $this->languageCode;
        }

        if (empty($languageCode)) {
            $translation = $string;
        } else {
            // TODO (feature115) pick translated string from
            // language string catalog.
            $translation = $this->lookupTranslation($string, $languageCode, $languageCatalog);
        }

        /**
         * Apply args to string
         */
        return strtr($translation, $args);
    }

    private function lookupTranslation($key, $languageCode, $languageCatalog)
    {
        $languageCatalogs = $this->languageCatalogStack;

        if ( ! is_null($languageCatalog)) {
            $languageCatalogs[] = $languageCatalog;
        }

        $languageCatalog = end($languageCatalogs);

        $translation = NULL;

        do {

            // If catalog is missing load it
            if ( ! isset($this->catalogs[$languageCode][$languageCatalog])) {
                $this->loadLanguageCatalog($languageCode, $languageCatalog);
            }

            // If key exists break
            if (isset($this->catalogs[$languageCode][$languageCatalog][$key])) {
                $translation = $this->catalogs[$languageCode][$languageCatalog][$key];
                break;
            }

            // If key is missing lookup in previous catalog
            $languageCatalog = prev($languageCatalogs);
        } while ($languageCatalog);

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
    public static function logMessage($message, $level = 'error')
    {
        log_message($level, $message);
    }

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
            redirect('dispatcher/Security');
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
        $topModuleDepot = new NethGui_Core_TopModuleDepot($hostConfiguration, $user);

        /*
         * TODO: enforce some security policy on Models
         */
        $pdp = new NethGui_Authorization_PermissivePolicyDecisionPoint();

        $hostConfiguration->setPolicyDecisionPoint($pdp);
        $topModuleDepot->setPolicyDecisionPoint($pdp);

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

        if (is_integer($processExitCode)) {
            set_status_header($processExitCode);
        }

        $worldModule->addModule(new NethGui_Core_Module_ValidationReport($report));

        if ($request->getContentType() === NethGui_Core_Request::CONTENT_TYPE_HTML) {
            if (is_array($processExitCode)) {
                // XXX: complete redirect protocol
                redirect($processExitCode[1][0], 'location', $processExitCode[0]);
            } else {
                $worldModule->addModule(new NethGui_Core_Module_Menu($topModuleDepot->getModules()));
                $worldModule->addModule(new NethGui_Core_Module_BreadCrumb($topModuleDepot, $currentModuleIdentifier));

                header("Content-Type: text/html; charset=UTF-8");
                $worldModule->prepareView($view, NethGui_Core_ModuleInterface::VIEW_REFRESH);
                echo $view->render();
            }
        } elseif ($request->getContentType() === NethGui_Core_Request::CONTENT_TYPE_JSON) {
            header("Content-Type: application/json; charset=UTF-8");
            $worldModule->prepareView($view, NethGui_Core_ModuleInterface::VIEW_UPDATE);
            echo json_encode($view->getArrayCopy());
        }
    }

}

/**
 * This is a shortcut to NethGui_Framework::translate() 
 * @see NethGui_Framework::translate()
 */
if ( ! function_exists('T')) {

    function T($string, $args = array(), $language = NULL, $catalog = NULL)
    {
        return NethGui_Framework::getInstance()->translate($string, $args, $language, $catalog);
    }

}