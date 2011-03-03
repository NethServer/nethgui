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
abstract class NethGui_Core_StandardModule implements NethGui_Core_ModuleInterface
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
    protected $hostConfiguration;
    /**
     *
     * @var RequestInterface
     */
    protected $request;
    /**
     *
     * @var array
     */
    protected $parameters = array();

    /**
     * @param string $identifier
     */
    public function __construct($identifier = NULL)
    {
        if (isset($identifier)) {
            $this->identifier = $identifier;
        } else {
            $this->identifier = get_class($this);
        }
    }

    public function setHostConfiguration(NethGui_Core_HostConfigurationInterface $hostConfiguration)
    {
        $this->hostConfiguration = $hostConfiguration;
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
        $this->request = $request;
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

    public function renderView(NethGui_Core_Response $response)
    {
        if ($response->getViewType() === NethGui_Core_Response::HTML) {
            return '<h2>' . $this->getTitle() . '</h2><div class="moduleDescription">' . $this->getDescription() . '</div>';
        }
    }

    protected function renderCodeIgniterView(NethGui_Core_Response $response, $viewName, $viewState = array())
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
        return CI_Controller::get_instance()->load->view('../../NethGui/View/' . $viewName, $viewState, TRUE);
    }

}

