<?php
/**
 * Nethgui
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
abstract class Nethgui_Core_Module_Standard extends Nethgui_Core_Module_Abstract implements Nethgui_Core_RequestHandlerInterface, Nethgui_Core_EventObserverInterface
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
     * Accept a value that represents a collection of any thing
     */
    const VALID_ANYTHING_COLLECTION = 104;

    /**
     * Accept a value that represents a collection of any Unix usernames
     */
    const VALID_USERNAME_COLLECTION = 105;

    /**
     * Accept positive integer
     */
    const VALID_POSITIVE_INTEGER = 106;

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
     * @var Nethgui_Core_ParameterSet
     */
    protected $parameters;

    /**
     * @var array
     */
    private $requiredEvents = array();

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
     * This array holds the names of parameters with validation errors.
     * @see prepareView()
     * @var array
     */
    private $invalidParameters = array();

    /**
     *
     * @var Nethgui_Core_RequestInterface
     */
    private $request;

    /**
     * @param string $identifier
     */
    public function __construct($identifier = NULL)
    {
        parent::__construct($identifier);
        $this->parameters = new Nethgui_Core_ParameterSet();
        $this->immutables = new ArrayObject();
        $this->autosave = TRUE;
    }

    /**
     * Declare a Module parameter.
     *
     * - A parameter is validated through $validationRule. It obtains its value
     *   from $valueProvider.
     * - A value provider can be a callback function or an adapter object.
     * - The callback function can return the parameter value or an adapter 
     *   itself. 
     *
     * NOTE: If you are using an adapter keep in mind that the
     * Host Configuration link is available after initialization only: don't
     * call in class constructor in this case!
     *
     * @see Nethgui_Core_HostConfigurationInterface::getIdentityAdapter()
     * @see Nethgui_Core_HostConfigurationInterface::getMapAdapter()
     *
     * @param string $parameterName The name of the parameter
     * @param mixed $validator Optional - A regular expression catching the correct value format OR An constant-integer corresponding to a predefined validator OR boolean FALSE for a readonly parameter
     * @param mixed $valueProvider Optional - A callback function, an adapter instance or an array of arguments to create an adapter
     */
    protected function declareParameter($parameterName, $validator = FALSE, $valueProvider = NULL)
    {
        if (is_string($validator) && $validator[0] == '/') {
            $validator = $this->getValidator()->regexp($validator);
        } elseif ($validator === FALSE) {
            $validator = $this->getValidator()->forceResult(FALSE);
        } elseif (is_integer($validator)) {
            $validator = $this->createValidatorFromInteger($validator);
        }

        // At this point $validator MUST be an object implementing the right interface
        if ($validator instanceof Nethgui_Core_ValidatorInterface) {
            $this->validators[$parameterName] = $validator;
        } else {
            throw new Nethgui_Exception_Validation("Invalid validator value for parameter `" . $parameterName . '` in module `' . get_class($this) . '`.');
        }

        if (is_callable($valueProvider)) {
            // Create a read-only Map Adapter using $valueProvider as read-callback
            $this->parameters->register($this->getHostConfiguration()->getMapAdapter($valueProvider, NULL, array()), $parameterName);
        } elseif ($valueProvider instanceof Nethgui_Adapter_AdapterInterface) {
            $this->parameters->register($valueProvider, $parameterName);
        } elseif (is_array($valueProvider)) {
            $this->parameters[$parameterName] = $this->getAdapterForParameter($parameterName, $valueProvider);
        } elseif (is_null($valueProvider)) {
            $this->parameters[$parameterName] = NULL;
        } else {
            throw new InvalidArgumentException('Invalid `valueProvider` argument');
        }
    }

    /**
     * The given $eventName is required to be signalled after any database change
     * @param string $eventName
     * @param array $eventArgs
     * @param Nethgui_Core_EventObserverInterface $observer Optional
     */
    protected function requireEvent($eventName, $eventArgs = array(), $observer = NULL)
    {
        if (is_string($eventName))
        {
            $this->requiredEvents[] = array($eventName, $eventArgs, is_null($observer) ? $this : $observer);
        }
    }

    public function notifyEventCompletion($eventName, $args, $exitStatus, $output)
    {
        $messageArgs = array('${0}' => $eventName);
        $index = 1;
        foreach ($args as $value) {
            $messageArgs['${' . $index . '}'] = $value;
            $index ++;
        }

        if ($exitStatus === FALSE) {
            $type = Nethgui_Core_DialogBox::NOTIFY_WARNING;
            $messageTemplate = $eventName . '_failure';
        } else {
            $type = Nethgui_Core_DialogBox::NOTIFY_SUCCESS;
            $messageTemplate = $eventName . '_success';
        }

        $actions = $this->prepareDialogBoxActions($eventName, $args, $exitStatus, $output, $type);

        $this->getRequest()->getUser()->showDialogBox($this, array($messageTemplate, $messageArgs), $actions, $type);
    }

    /**
     * This method receives the same arguments given to notifyEventCompletion()
     * and should return the action definitions to display a dialog box.
     *
     * @see Nethgui_Core_EventObserverInterface::notifyEventCompletion
     * @see Nethgui_Core_DialogBox
     * @param string $eventName
     * @param array $args
     * @param boolean $exitStatus
     * @param array $output
     * @param &integer $type Output parameter to change the dialog box type
     * @return array
     */
    protected function prepareDialogBoxActions($eventName, $args, $exitStatus, $output, &$type)
    {
        return array();
    }

    /**
     * Event signalling routine
     * @see requireEvent
     */
    protected function signalAllEventsFinally()
    {
        foreach ($this->requiredEvents as $eventCall) {
            $this->getHostConfiguration()->signalEventFinally($eventCall[0], $eventCall[1], $eventCall[2]);
        }
    }

    /**
     * Creates a Validator object that checks against $ruleCode.
     *
     * @param integer $ruleCode
     * @return Nethgui_Core_Validator
     */
    private function createValidatorFromInteger($ruleCode)
    {
        $validator = $this->getValidator();

        switch ($ruleCode) {
            case self::VALID_ANYTHING:
                return $validator->forceResult(TRUE);

            case self::VALID_ANYTHING_COLLECTION:
                return $validator->collectionValidator($this->getValidator()->forceResult(TRUE));

            case self::VALID_USERNAME_COLLECTION:
                return $validator->collectionValidator($this->getValidator()->username());

            case self::VALID_SERVICESTATUS:
                return $validator->memberOf('enabled', 'disabled');

            case self::VALID_USERNAME:
                return $validator->username();

            case self::VALID_NOTEMPTY:
                return $validator->notEmpty();

            case self::VALID_IP:
            case self::VALID_IPv4:
                return $validator->ipV4Address();

            case self::VALID_POSITIVE_INTEGER:
                return $validator->integer()->positive();

            case self::VALID_IP_OR_EMPTY:
            case self::VALID_IPv4_OR_EMPTY:
                return $validator->orValidator($this->getValidator()->ipV4Address(), $this->getValidator()->isEmpty());
        }

        throw new InvalidArgumentException('Unknown standard validator code: ' . $ruleCode);
    }

    /**
     * @todo Rename to createValidator()
     * @return Nethgui_Core_Validator
     */
    protected function getValidator()
    {
        return new Nethgui_Core_Validator();
    }

    /**
     * Helps in creation of complex adapters.
     * @param array|callable $args
     * @return Nethgui_Adapter_AdapterInterface
     */
    private function getAdapterForParameter($parameterName, $args)
    {
        $readerCallback = 'read' . ucfirst($parameterName);
        $writerCallback = 'write' . ucfirst($parameterName);
            
        if (is_callable(array($this, $readerCallback))) { // NOTE: writer is optional, see MultipleAdapter
            if (empty($args)) {
                $args = array();
            }
            /*
             * Perhaps we have callbacks defined but only one serializer;
             * in this case wrap $args into an array.
             *
             * If first argument is a string, it contains the $database name.
             */
            if (is_array($args) && isset($args[0]) && (is_string($args[0]) || $args[0] instanceof ArrayAccess)) {
                $args = array($args);
            }

            $adapterObject = $this->getHostConfiguration()->getMapAdapter(
                array($this, $readerCallback), array($this, $writerCallback), $args
            );
        } elseif (isset($args[0], $args[1])) {
            // Get an identity adapter:
            $database = $args[0];
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
     * associated to the Module receives immutable parameters only in
     * SERVER view.
     *
     * Enumerations and constants are examples of immutable parameters.
     *
     * @deprecated
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

    /**
     *
     * @deprecated
     * @param type $immutableName
     * @return type
     */
    protected function getImmutableValue($immutableName)
    {
        return $this->immutables[$immutableName];
    }

    public function bind(Nethgui_Core_RequestInterface $request)
    {
        $this->request = $request;
        foreach ($this->parameters->getKeys() as $parameterName) {
            if ($request->hasParameter($parameterName)) {
                $this->parameters[$parameterName] = $request->getParameter($parameterName);
            }
        }
    }

    public function validate(Nethgui_Core_ValidationReportInterface $report)
    {
        foreach ($this->parameters->getKeys() as $parameterName) {
            if ( ! $this->getRequest()->hasParameter($parameterName)) {
                continue; // missing parameters are not validated
            }

            if ( ! isset($this->validators[$parameterName])) {
                throw new Nethgui_Exception_Validation("Do not know how to validate `" . $parameterName . "`");
            }

            $validator = $this->validators[$parameterName];

            $isValid = $validator->evaluate($this->parameters[$parameterName]);
            if ($isValid !== TRUE) {
                $report->addValidationError(new Nethgui_Core_ModuleSurrogate($this), $parameterName, $validator->getFailureInfo());
                $this->invalidParameters[] = $parameterName;
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
                $this->signalAllEventsFinally();
            }
        }
    }

    public function prepareView(Nethgui_Core_ViewInterface $view, $mode)
    {
        parent::prepareView($view, $mode);
        $view->copyFrom($this->parameters);
        if ($mode === self::VIEW_SERVER) {
            $view->copyFrom($this->immutables);
            $view['__invalidParameters'] = $this->invalidParameters;
        }
    }

    /**
     *
     * @return Nethgui_Core_RequestInterface
     */
    protected function getRequest()
    {
        return $this->request;
    }

    protected function hasInputForm() {
        return $this->getParent() !== NULL;
    }

}

