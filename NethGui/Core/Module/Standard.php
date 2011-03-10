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
abstract class NethGui_Core_Module_Standard implements NethGui_Core_ModuleInterface, NethGui_Core_RequestHandlerInterface
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
     * @var array
     */
    protected $parameters = array();
    /**
     * Validator configuration
     * @var array
     */
    private $validators = array();
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

    protected function declareParameter($parameterName, $validationRule)
    {
        $this->validators[$parameterName] = $validationRule;
    }

    public function bind(NethGui_Core_RequestInterface $request)
    {
        foreach (array_keys($this->validators) as $parameterName) {
            $this->parameters[$parameterName] = $request->getParameter($parameterName);
        }
    }

    public function validate(NethGui_Core_ValidationReportInterface $report)
    {
        foreach ($this->parameters as $parameter => $value) {
            if ( ! isset($this->validators[$parameter]))
            {
                throw new NethGui_Exception_Validation("Unknown parameter " . $parameter);
            }

            $pattern = $this->validators[$parameter];

            // TODO: implement a real validation
            if (preg_match($pattern, $value) == 0)
            {
                $report->addError($parameter, 'Invalid ' . $parameter);
            }
        }
    }

    /**
     * Default Standard behaviour calls fillResponse()
     * @see fillResponse()
     * @param NethGui_Core_ResponseInterface $response
     */
    public function process(NethGui_Core_ResponseInterface $response)
    {
       $this->fillResponse($response);
    }

    /**
     * Ensure response object has all declared parameters.  Missing parameters
     * are set to FALSE as default.
     * @param NethGui_Core_ResponseInterface $response 
     */
    protected function fillResponse(NethGui_Core_ResponseInterface $response)
    {

        foreach(array_keys($this->validators) as $parameter) {
            if(!isset($this->parameters[$parameter])) {
                $this->parameters[$parameter] = false;
            }
        }

        $response->setData($this->parameters);
    }

    /**
     * @param string $identifier
     * @param NethGui_Core_RequestHandlerInterface $handler
     */
    protected function setRequestHandler($identifier, NethGui_Core_RequestHandlerInterface $handler)
    {
        $this->requestHandlers[$identifier] = $handler;
    }

}

