<?php
/**
 * NethGui
 *
 * @package Core
 * @subpackage Module
 */

/**
 * A Standard Module connects the Configuration Database and the View layer,
 * performing data validation and processing.
 *
 * @package Core
 * @subpackage Module
 */
abstract class NethGui_Core_Module_Standard extends NethGui_Core_Module_Abstract implements NethGui_Core_RequestHandlerInterface
{
    /**
     * A valid service status is a 'disabled' or 'enabled' string.
     */
    const VALID_SERVICESTATUS = 100;

    /**
     * A valid *nix username token
     */
    const VALID_USERNAME = 101;

    /**
     * A not empty value
     */
    const VALID_NOTEMPTY = 102;
    
    
    /**
     * Accepts any value
     */
    const VALID_ANYTHING = 103;

    /**
     * A valid IPv4 address like '192.168.1.1' 
     */
    const VALID_IPv4 = 200;

    /**
     * A valid IPv4 address like '192.168.1.1' ore empty
     */
    const VALID_IPv4_OR_EMPTY = 201;

    /**
     * Alias for VALID_IPv4 
     */
    const VALID_IP = 202;

    /**
     * Alias for VALID_IPv4_OR_EMPTY
     */
    const VALID_IP_OR_EMPTY = 203;

    /**
     * This collection holds the parameter values as primitive datatype or adapter objects.
     * @var NethGui_Core_ParameterSet
     */
    protected $parameters;
    /**
     * @var array
     */
    private $requiredEvents = array();
    /**
     * 
     * @var array
     */
    private $submitDefaults = array();
    /**
     * Set to FALSE if you want to inhibit saving of parameters.
     * @var bool
     */
    protected $autosave;
    /**
     * @var ArrayObject
     */
    private $immutables;
    /**
     * Validator configuration. Holds declared parameters.
     * @var array
     */
    private $validators = array();
    /**
     * This array holds the names of parameters passed by Request during bind().
     * Only those parameters will be validated.
     *
     * @var array
     */
    private $parameterValidationList = array();
    /**
     * This array holds the names of parameters with validation errors.
     * @see prepareView()
     * @var array
     */
    private $invalidParameters = array();
    /**
     *
     * @var NethGui_Core_UserInterface
     */
    private $user;

    /**
     * @param string $identifier
     */
    public function __construct($identifier = NULL)
    {
        parent::__construct($identifier);
        $this->parameters = new NethGui_Core_ParameterSet();
        $this->immutables = new ArrayObject();
        $this->autosave = TRUE;
    }

    /**
     * Declare a Module parameter.
     *
     * A parameter is validated through $validationRule and optionally linked to
     * one or more database values through an $adapter.
     *
     * If the parameter is using an adapter keep in mind that the
     * Host Configuration link is available after initialization only: don't
     * call in class constructor in this case!
     *
     * @see NethGui_Core_HostConfigurationInterface::getIdentityAdapter()
     * @see NethGui_Core_HostConfigurationInterface::getMapAdapter()
     *
     * @param string $parameterName The name of the parameter
     * @param string $validator Optional - A regular expression catching the correct value format
     * @param NethGui_Adapter_AdapterInterface|array $adapter Optional - An adapter instance or an array of arguments to create it
     * @param mixed $onSubmitDefaultValue Optional - Value to assign if parameter is missing when binding a submitted request
     */
    protected function declareParameter($parameterName, $validator = FALSE, $adapter = NULL, $onSubmitDefaultValue = NULL)
    {
        if (is_string($validator) && $validator[0] == '/') {
            $validator = $this->getValidator()->regexp($validator);
        } elseif ($validator === FALSE) {
            $validator = $this->getValidator()->forceResult(FALSE);
        } elseif (is_integer($validator)) {
            $validator = $this->createValidatorFromInteger($validator);
        }

        // At this point $validator MUST be an object implementing the right interface
        if ($validator instanceof NethGui_Core_ValidatorInterface) {
            $this->validators[$parameterName] = $validator;
        } else {
            throw new NethGui_Exception_Validation("Invalid validator value for parameter `" . $parameterName . '` in module `' . get_class($this) . '`.');
        }

        if ($adapter instanceof NethGui_Adapter_AdapterInterface) {
            $this->parameters[$parameterName] = $adapter;
        } elseif (is_array($adapter)) {
            $this->parameters[$parameterName] = $this->getAdapterForParameter($parameterName, $adapter);
        } else {
            $this->parameters[$parameterName] = NULL;
        }

        if ( ! is_null($onSubmitDefaultValue)) {
            $this->submitDefaults[$parameterName] = $onSubmitDefaultValue;
        }
    }

    /**
     * Signal the given event after at least one successful database write operation occurred.
     * @param string $eventName 
     */
    protected function requireEvent($eventName, $eventArgs = array(), $eventCallback = NULL)
    {
        if (is_string($eventName))
        {
            $this->requiredEvents[] = array($eventName, $eventArgs, $eventCallback);
        }
    }

    protected function signalAllEventsAsync()
    {
        foreach ($this->requiredEvents as $eventCall) {
            $this->getHostConfiguration()->signalEventAsync($eventCall[0], $eventCall[1], $eventCall[2]);
        }
    }

    /**
     * Creates a Validator object that checks against $ruleCode.
     *
     * @param integer $ruleCode
     * @return NethGui_Core_Validator
     */
    private function createValidatorFromInteger($ruleCode)
    {
        $validator = $this->getValidator();

        switch ($ruleCode) {
            case self::VALID_ANYTHING:
                return $validator->forceResult(TRUE);
            
            case self::VALID_SERVICESTATUS:
                return $validator->memberOf('enabled', 'disabled');

            case self::VALID_USERNAME:
                return $validator->username();

            case self::VALID_NOTEMPTY:
                return $validator->notEmpty();

            case self::VALID_IP:
            case self::VALID_IPv4:
                return $validator->ipV4Address();

            case self::VALID_IP_OR_EMPTY:
            case self::VALID_IPv4_OR_EMPTY:
                return $validator->orValidator($this->getValidator()->ipV4Address(), $this->getValidator()->isEmpty());
        }

        throw new InvalidArgumentException('Unknown standard validator code: ' . $ruleCode);
    }

    /**
     * @return NethGui_Core_Validator
     */
    protected function getValidator()
    {
        return new NethGui_Core_Validator();
    }

    /**
     * Helps in creation of complex adapters.
     * @param array $args
     * @return NethGui_Adapter_AdapterInterface
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
                        array($this, $readerCallback), array($this, $writerCallback), $args
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

        if (is_object($immutableValue)) {
            $immutableValue = clone $immutableValue;
        }

        $this->immutables[$immutableName] = $immutableValue;
    }

    protected function getImmutableValue($immutableName)
    {
        return $this->immutables[$immutableName];
    }

    public function bind(NethGui_Core_RequestInterface $request)
    {
        $this->user = $request->getUser();
        foreach ($this->parameters as $parameterName => $parameterValue) {
            if ($request->hasParameter($parameterName)) {
                $this->parameters[$parameterName] = $request->getParameter($parameterName);
                $this->parameterValidationList[] = $parameterName;
            } elseif ($request->isSubmitted()
                && isset($this->submitDefaults[$parameterName])) {
                $this->parameters[$parameterName] = $this->submitDefaults[$parameterName];
            }
        }
    }

    public function validate(NethGui_Core_ValidationReportInterface $report)
    {
        foreach ($this->parameterValidationList as $parameter) {
            if ( ! isset($this->validators[$parameter])) {
                throw new NethGui_Exception_Validation("Unknown parameter " . $parameter);
            }

            $validator = $this->validators[$parameter];

            $isValid = $validator->evaluate($this->parameters[$parameter]);
            if ($isValid !== TRUE) {
                $report->addValidationError($this, $parameter, $validator->getMessage());
                $this->invalidParameters[] = $parameter;
            }
        }
    }

    /**
     * Do nothing
     */
    public function process()
    {
        if ($this->autosave === TRUE) {
            $changes = $this->parameters->save();
            if ($changes > 0) {
                $this->signalAllEventsAsync();
            }
        }
    }

    public function prepareView(NethGui_Core_ViewInterface $view, $mode)
    {
        parent::prepareView($view, $mode);
        $view->copyFrom($this->parameters);
        if ($mode === self::VIEW_REFRESH) {
            $view->copyFrom($this->immutables);
            $view['__invalidParameters'] = $this->invalidParameters;
        }
    }

    /**
     * @return NethGui_Core_UserInterface
     */
    protected function getUser()
    {
        return $this->user;
    }

}

