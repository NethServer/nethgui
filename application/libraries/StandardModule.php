<?php

abstract class StandardModule implements ModuleInterface {

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
        if (isset($identifier))
        {
            $this->identifier = $identifier;
        }
        else
        {
            $this->identifier = get_class($this);
        }        
    }

    public function setHostConfiguration(HostConfigurationInterface $hostConfiguration)
    {
        $this->hostConfiguration = $hostConfiguration;
    }

    /**
     *  Overriding methods can read current state from model.
     */
    public function initialize()
    {
        if ($this->initialized === FALSE)
        {
            $this->initialized = TRUE;
        }
        else
        {
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
        return $this->getIdentifier();
    }

    public function getDescription()
    {
        return "";
    }

    public function setParent(ModuleInterface $parentModule)
    {
        $this->parent = $parentModule;
    }

    public function getParent()
    {
        return $this->parent;
    }


    public function bind(RequestInterface $request)
    {
        $this->request = $request;
    }

    public function validate(ValidationReportInterface $report)
    {
        foreach ($this->parameters as $parameter)
        {
            // TODO: do parameter validation
        }
    }

    public function process()
    {

    }

    public function renderView(Response $response)
    {
        if ($response->getViewType() === Response::HTML)
        {
            return '<h2>' . $this->getTitle() . '</h2><div class="moduleDescription">' . $this->getDescription() . '</div>';
        }
    }

    protected function renderCodeIgniterView(Response $response, $viewName, $viewState = array())
    {
        $viewState['module'] = $this;
        $viewState['response'] = $response;
        $viewState['parameter'] = array();
        $viewState['id'] = array();
        $viewState['name'] = array();

        foreach($this->parameters as $parameterName => $parameterValue)
        {
            $viewState['id'][$parameterName] = htmlspecialchars($response->getWidgetId($this, $parameterName));
            $viewState['name'][$parameterName] = htmlspecialchars($response->getParameterName($this, $parameterName));
            $viewState['parameter'][$parameterName] = htmlspecialchars($parameterValue);
        }
        return CI_Controller::get_instance()->load->view($viewName, $viewState, TRUE);
    }

}

