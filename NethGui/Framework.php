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
     * Underlying framework controller
     * @var CI_Controller
     */
    private $controller;

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
     * Loads a view passing $viewState as view parameters.
     * 
     * @param string $viewName Full view name. Follows class naming convention.
     * @param array $viewState Array of view parameters.
     * @return string
     */
    public function renderView($viewName, $viewState)
    {
        $ciViewPath = '../../' . str_replace('_', '/', $viewName);

        $absoluteViewPath = realpath(APPPATH . 'views/' . $ciViewPath . '.php');
        
        if(!$absoluteViewPath) {
            // TODO: log a warning.
            return '';
        }

        return $this->controller->load->view($ciViewPath, $viewState, true);
    }

    /**
     * Renders
     *
     * @param NethGui_Core_Response $response
     * @return string
     */
    public function renderResponse(NethGui_Core_Response $response) {

        $viewState['response'] = $response;
        $viewState['id'] = array();
        $viewState['name'] = array();

        $viewState['module'] = $response->getModule();

        $viewState['framework'] = $this;

        // Add a reference to forward current view state into inner views.
        $viewState['self'] = &$viewState;


        $responseData = $response->getData();
        // Put all view data into id, name, parameter helper arrays.
        if (is_array($responseData)
            OR $responseData instanceof Traversable) {
            foreach ($responseData as $parameterName => $parameterValue) {
                $viewState['id'][$parameterName] = htmlspecialchars($response->getWidgetId($parameterName));
                $viewState['name'][$parameterName] = htmlspecialchars($response->getParameterName($parameterName));
                $viewState['parameter'][$parameterName] = htmlspecialchars($parameterValue);
            }
        }

        return $this->renderView($response->getViewName(), $viewState);
    }


    /**
     * Class autoloader
     *
     * This function is registered as SPL class autoloader.
     *
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

}
