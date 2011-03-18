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
abstract class NethGui_Core_Module_Standard implements NethGui_Core_ModuleInterface
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

    protected $constants = array();

    /**
     * Validator configuration. Holds declared parameters.
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

    /**
     * Declare a Module parameter.
     * 
     * @param string $parameterName
     * @param string $validationRule A regular expression catching the correct value format
     * @param mixed $defaultValue Value to assign if parameter is missing during binding
     */
    protected function declareParameter($parameterName, $validationRule, $defaultValue = NULL)
    {
        $this->validators[$parameterName] = $validationRule;
        $this->parameters[$parameterName] = $defaultValue;
    }

    public function bind(NethGui_Core_RequestInterface $request)
    {
        foreach (array_keys($this->parameters) as $parameterName) {
            if ($request->hasParameter($parameterName)) {
                $this->parameters[$parameterName] = $request->getParameter($parameterName);
            }
        }
    }

    public function validate(NethGui_Core_ValidationReportInterface $report)
    {
        foreach ($this->parameters as $parameter => $value) {
            if ( ! isset($this->validators[$parameter]))
            {
                throw new NethGui_Exception_Validation("Unknown parameter " . $parameter);
            }

            // TODO: implement a real validation
            $validator = $this->validators[$parameter];
            if($validator === false) {
                // PASS...
            } elseif (is_string($validator) && $validator[0] == '/') {
                if (preg_match($validator, strval($value)) == 0) {
                    $report->addError($this, $parameter, 'Invalid ' . $parameter);
                }
            } else {
                throw new NethGui_Exception_Validation("Invalid validator value for parameter `" . $parameter . '` in module `' . get_class($this) . '`.');
            }
        }
    }

    /**
     * Do nothing
     */
    public function process()
    {

    }

    /**
     * Transfers module parameters to view.
     * 
     * @param NethGui_Core_ViewInterface $response
     */
    public function prepareView(NethGui_Core_ViewInterface $view)
    {
        $view->copyFrom($this->parameters);
        $view->copyFrom($this->constants);
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

