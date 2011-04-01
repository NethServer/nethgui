<?php
/**
 * NethGui
 *
 * @package NethGuiFramework
 */

/**
 * NethGui Framework singleton.
 *
 * @package NethGuiFramework
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
    private $languageCode;
    /**
     * This is a stack of catalog names. Current catalog is the last element
     * of the array.
     * @var array
     */
    private $languageCatalog;

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
            $instance->languageCatalog = array('default');
        }

        return $instance;
    }

    private function __construct(CI_Controller $codeIgniterController)
    {
        spl_autoload_register(get_class($this) . '::autoloader');
        ini_set('include_path', ini_get('include_path') . ':' . realpath(dirname(__FILE__) . '/..'));

        $this->controller = $codeIgniterController;
        $this->dispatcher = new NethGui_Dispatcher($codeIgniterController);
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
     * @param string $viewName Full view name. Follows class naming convention.
     * @param array $viewState Array of view parameters.
     * @param string $languageCatalog Name of language strings catalog.
     * @return string
     */
    public function renderView($viewName, $viewState, $languageCatalog = NULL)
    {
        $ciViewPath = '../../' . str_replace('_', '/', $viewName);

        $absoluteViewPath = realpath(APPPATH . 'views/' . $ciViewPath . '.php');

        if ( ! $absoluteViewPath) {
            log_message('error', "Unable to load `{$viewName}`.");
            return '';
        }

        if ( ! is_null($languageCatalog)) {
            $this->languageCatalog[] = $languageCatalog;
        }

        $viewOutput = $this->controller->load->view($ciViewPath, $viewState, true);

        if ( ! is_null($languageCatalog)) {
            array_pop($this->languageCatalog);
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
            $ciControllerClassName = NethGui_Framework::getInstance()->getControllerName();
            $html = anchor($ciControllerClassName . '/' . $module->getIdentifier(),
                    htmlspecialchars($module->getTitle()),
                    array('class' => 'moduleTitle', 'title' => htmlspecialchars($module->getDescription())
                    )
            );
        }

        return $html;
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
        if ( ! isset($languageCatalog)) {
            $languageCatalog = end($this->languageCatalog);
        }
        if ( ! isset($languageCode)) {
            $languageCode = $this->languageCode;
        }

        if (empty($languageCatalog)
            || empty($languageCode)
        ) {
            $translation = $string;
        } else {
            // TODO pick translated string from language string catalog.
            $translation = $string;
        }

        /**
         * Applies args to string
         */
        return strtr($translation, $args);
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
        require_once($classPath);
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
 * Registers translator function if gettext is not available.
 * @see NethGui_Framework::translate()
 */
if ( ! function_exists('T')) {

    function T($string, $args = array(), $language = NULL, $catalog = NULL)
    {
        return NethGui_Framework::getInstance()->translate($string, $args, $language, $catalog);
    }

}