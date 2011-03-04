<?php
/**
 * NethGui
 *
 * @package NethGuiFramework
 */

/**
 * TODO: describe class
 *
 * @package NethGuiFramework
 * @subpackage StandardImplementation
 */
abstract class NethGui_Core_StandardModule implements NethGui_Core_ModuleInterface, NethGui_Core_RequestHandlerInterface
{

    /**
     * @var string
     */
    private $identifier;
    /**
     *
     * @var ModuleInterface;
     */
    private $parent;
    /*
     * @var bool
     */
    private $initialized = FALSE;
    /**
     * @var HostConfigurationInterface
     */
    private $hostConfiguration;
    /**
     *
     * @var array
     */
    protected $parameters = array();
    /**
     * @see NethGui_Core_RequestHandlerInterface
     * @var array
     */
    private $requestHandlers = array();

    /**
     * @param string $identifier
     */
    public function __construct($identifier = NULL)
    {
        if (isset($identifier)) {
            $this->identifier = $identifier;
        } else {
            $this->identifier = array_pop(explode('_', get_class($this)));
        }
    }

    public function setHostConfiguration(NethGui_Core_HostConfigurationInterface $hostConfiguration)
    {
        $this->hostConfiguration = $hostConfiguration;
    }

    /**
     * @return NethGui_Core_HostConfigurationInterface
     */
    protected function getHostConfiguration()
    {
        return $this->hostConfiguration;
    }

    /**
     *  Overriding methods can read current state from model.
     */
    public function initialize()
    {
        if ($this->initialized === FALSE) {
            $this->initialized = TRUE;
        } else {
            throw new Exception("Double Module initialization is forbidden.");
        }
    }

    public function isInitialized()
    {
        return $this->initialized;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function getTitle()
    {
        return array_pop(explode('_', $this->getIdentifier()));
    }

    public function getDescription()
    {
        return "";
    }

    public function setParent(NethGui_Core_ModuleInterface $parentModule)
    {
        $this->parent = $parentModule;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function bind(NethGui_Core_RequestInterface $request)
    {
        foreach ($request->getParameters() as $parameterName) {
            $this->parameter[$parameterName] = $request->getParameter($parameterName);
        }
    }

    public function validate(NethGui_Core_ValidationReportInterface $report)
    {
        foreach ($this->parameters as $parameter) {
            // TODO: do parameter validation
        }
    }

    public function process()
    {

    }

    /**
     * Returns an appropriate view string based on $response type.
     * @see NethGui_Core_ResponseInterface::getViewType()
     * @param NethGui_Core_ResponseInterface $response
     * @return string
     */
    public function renderView(NethGui_Core_ResponseInterface $response)
    {
        $viewType = $response->getViewType();

        if (
            $viewType === NethGui_Core_ResponseInterface::HTML
            && method_exists($this, 'renderViewHtml')
        ) {
            return $this->renderViewHtml($response);
            //
        } elseif (
            $viewType === NethGui_Core_ResponseInterface::JS
            && method_exists($this, 'renderViewJavascript')
        ) {
            return $this->renderViewJavascript($response);
            //
        } elseif (
            $viewType === NethGui_Core_ResponseInterface::CSS
            && method_exists($this, 'renderViewCss')
        ) {
            return $this->renderViewCss($response);
            //
        } elseif ($viewType === NethGui_Core_ResponseInterface::HTML) {
            return '<h2>' . $this->getTitle() . '</h2><div class="moduleDescription">' . $this->getDescription() . '</div>';
            //
        }

        return "";
    }

    protected function renderCodeIgniterView(NethGui_Core_ResponseInterface $response, $viewName, $viewState = array())
    {
        $viewState['module'] = $this;
        $viewState['response'] = $response;
        $viewState['parameter'] = array();
        $viewState['id'] = array();
        $viewState['name'] = array();

        foreach ($this->parameters as $parameterName => $parameterValue) {
            $viewState['id'][$parameterName] = htmlspecialchars($response->getWidgetId($this, $parameterName));
            $viewState['name'][$parameterName] = htmlspecialchars($response->getParameterName($this, $parameterName));
            $viewState['parameter'][$parameterName] = htmlspecialchars($parameterValue);
        }
        return NethGui_Framework::getInstance()->getView('../../NethGui/View/' . $viewName, $viewState);
    }

    /**
     * @param string $identifier
     * @param NethGui_Core_RequestHandlerInterface $handler
     */
    protected function addRequestHandler($identifier, NethGui_Core_RequestHandlerInterface $handler)
    {
        $this->requestHandlers[$identifier] = $handler;
    }

}

