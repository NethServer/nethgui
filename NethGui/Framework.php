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
     * Pointer to current dispatcher.
     * @var NethGui_Dispatcher
     */
    private $dispatcher;
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
        $this->dispatcher = new NethGui_Dispatcher($codeIgniterController);
        $this->languageCatalogStack = array(get_class());
    }

    /**
     *
     * @return NethGui_Dispatcher
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
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
            $html = anchor($ciControllerClassName . '/' . $module->getIdentifier(),
                    htmlspecialchars($module->getTitle()),
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
     * @param string $languageCode The language to translate the string into.
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

        $filePath = dirname(__FILE__) . '/Language/' . $languageCode . '/' . $languageCatalog . '.php';

        @include($filePath);

        if (ENVIRONMENT == 'development' && ! empty($L)) {
            $this->logMessage('Loaded catalog ' . $filePath);
        }

        $this->catalogs[$languageCode][$languageCatalog] = &$L;
    }

    /**
     * 
     * @param string $code ISO 639-1 language code (2 characters).
     */
    public function setLanguageCode($code)
    {
        $this->languageCode = strtolower(substr($code, 0, 2));
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