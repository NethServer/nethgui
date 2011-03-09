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

    private function getView($viewName, $viewParameters)
    {
        $viewFile = APPPATH . 'views/' . $viewName;
        if(! file_exists($viewFile)) {
            // TODO: log a warning.
            return '';
        }
        return $this->controller->load->view($viewName, $viewParameters, true);
    }

    public function renderView($viewName, $viewState)
    {
        $viewName = str_replace('_', '/', $viewName);
        return NethGui_Framework::getInstance()->getView('../../' . $viewName . '.php', $viewState);
    }

    public function renderResponse(NethGui_Core_Response $response) {

        $viewData = $response->getData();
        $viewName = $response->getViewName();

        $viewState['response'] = $response;
        $viewState['id'] = array();
        $viewState['name'] = array();
        //$viewState['data'] = $response->getWholeData();
        $viewState['module'] = $response->getModule();

        $viewState['framework'] = $this;

        $viewState['self'] = &$viewState;

        // Put all view data into id, name, parameter helper arrays.
        if (is_array($viewData)
            OR $viewData instanceof Traversable) {
            foreach ($viewData as $parameterName => $parameterValue) {
                $viewState['id'][$parameterName] = htmlspecialchars($response->getWidgetId($parameterName));
                $viewState['name'][$parameterName] = htmlspecialchars($response->getParameterName($parameterName));
                $viewState['parameter'][$parameterName] = htmlspecialchars($parameterValue);
            }
        }

        return $this->renderView($viewName, $viewState);
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
