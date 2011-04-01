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
     * @var NethGui_Core_ParameterSet
     */
    protected $parameters;
    /**
     * 
     * @var array
     */
    private $submitDefaults = array();
    protected $autosave;
    /**
     * @var ArrayObject
     */
    private $immutables;
    /**
     *
     * @var NethGui_Core_RequestInterface
     */
    protected $request;
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
        $this->parameters = new NethGui_Core_ParameterSet();
        $this->immutables = new ArrayObject();

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

        $this->autosave = TRUE;
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
     * @param string $parameterName The name of the parameter
     * @param string $validationRule Optional - A regular expression catching the correct value format
     * @param NethGui_Core_AdapterInterface|array $adapter Optional - An adapter instance or an array of arguments to create it
     * @param mixed $onSubmitDefaultValue Optional - Value to assign if parameter is missing when binding a submitted request
     */
    protected function declareParameter($parameterName, $validationRule = FALSE, $adapter = NULL, $onSubmitDefaultValue = NULL)
    {
        $this->validators[$parameterName] = $validationRule;

        if ($adapter instanceof NethGui_Core_AdapterInterface) {
            $this->parameters->register($adapter, $parameterName);
        } elseif (is_array($adapter)) {
            $this->parameters->register($this->getAdapterForParameter($parameterName, $adapter), $parameterName);
        } else {
            $this->parameters->offsetSet($parameterName, NULL);
        }

        if ( ! is_null($onSubmitDefaultValue)) {
            $this->submitDefaults[$parameterName] = $onSubmitDefaultValue;
        }
    }

    /**
     * Helps in creation of complex adapters.
     * @param array $args
     * @return NethGui_Core_AdapterInterface
     */
    private function getAdapterForParameter($parameterName, $args)
    {
        $readerCallback = 'read' . ucfirst($parameterName);
        $writerCallback = 'write' . ucfirst($parameterName);

        $hasCallbacks = method_exists($this, $readerCallback)
            && method_exists($this, $writerCallback);

        if ($hasCallbacks) {
            /*
             * Perhaps we have callbacks defined but only one serializer;
             * in this case wrap $args into an array.
             *
             * If first argument is a string, it contains the $database name.
             */
            if (is_string($args[0])) {
                $args = array($args);
            }

            if (is_array($args)) {
                $adapterObject = $this->getHostConfiguration()->getMapAdapter(
                        array($this, $readerCallback),
                        array($this, $writerCallback),
                        $args
                );
            }
        } elseif (isset($args[0], $args[1])) {
            // Get an identity adapter:
            $database = (string) $args[0];
            $key = (string) $args[1];
            $prop = isset($args[2]) ? (string) $args[2] : NULL;
            $separator = isset($args[3]) ? (string) $args[3] : NULL;

            $adapterObject = $this->getHostConfiguration()->getIdentityAdapter($database, $key, $prop, $separator);
        }

        if (is_null($adapterObject)) {
            throw new Exception("Cannot create an adapter for parameter `" . $parameterName . "`");
        }

        return $adapterObject;
    }

    /**
     * Immutable parameters cannot be changed after declaration.  The View
     * associated to the Module receives immutable parameters only if
     * REFRESHing.
     *
     * Enumerations and constants are examples of immutable parameters.
     *
     * @see prepareView()
     * @param string $immutableName
     * @param mixed $immutableValue
     */
    protected function declareImmutable($immutableName, $immutableValue)
    {
        if (isset($this->immutables[$immutableName])) {
            throw new Exception('Immutable `' . $immutableName . '` is already declared.');
        }

        $this->immutables[$immutableName] = $immutableValue;
    }

    public function bind(NethGui_Core_RequestInterface $request)
    {
        foreach ($this->parameters as $parameterName => $parameterValue) {
            if ($request->hasParameter($parameterName)) {
                $this->parameters[$parameterName] = $request->getParameter($parameterName);
            } elseif ($request->isSubmitted()
                && isset($this->submitDefaults[$parameterName])) {
                $this->parameters[$parameterName] = $this->submitDefaults[$parameterName];
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

            // TODO: implement a real validation see issue #12
            $validator = $this->validators[$parameter];

            if ($validator === FALSE) {
                // PASS...
            } elseif (is_string($validator) && $validator[0] == '/') {
                if (preg_match($validator, strval($value)) == 0) {
                    $report->addError($this, $parameter, 'Invalid `' . $parameter . '`');
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
        if ($this->autosave === TRUE) {
            $this->parameters->save();
        }
    }

    /**
     * Transfers module parameters to view.
     * 
     * @param NethGui_Core_ViewInterface $response
     */
    public function prepareView(NethGui_Core_ViewInterface $view, $mode)
    {
        $view->copyFrom($this->parameters);
        if ($mode == self::VIEW_REFRESH) {
            $view->copyFrom($this->immutables);
        }
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

