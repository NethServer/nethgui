<?php
namespace Nethgui\Core\Module;

/*
 * Copyright (C) 2011 Nethesis S.r.l.
 * 
 * This script is part of NethServer.
 * 
 * NethServer is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * NethServer is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with NethServer.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * A Standard Module connects the Configuration Database and the View layer,
 * performing data validation and processing.
 * 
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 * @api
 */
abstract class Standard extends AbstractModule implements \Nethgui\Core\RequestHandlerInterface, \Nethgui\System\EventObserverInterface
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
     * Valid host name
     *
     * @see #478
     */
    const VALID_HOSTNAME = 107;

    /**
     * Valid host name or ip address
     *
     * @see #478
     */
    const VALID_HOSTADDRESS = 108;


    /**
     * Valid date
     *
     * @see #513
     */
    const VALID_DATE = 109;

    /**
     * Valid time
     *
     * @see #513
     */
    const VALID_TIME = 110;

    /**
     * Boolean validator.
     * 
     * '', '0', FALSE are FALSE boolean values. Other values are TRUE.
     */
    const VALID_BOOLEAN = 111;

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
     * A valid TCP/UDP port number 0-65535
     */
    const VALID_PORTNUMBER = 204;

    /**
     * A choice between 'yes' and 'no' values
     */
    const VALID_YES_NO = 205;

    /**
     * A valid ipv4 netmask address like '255.255.255.0'
     */
    const VALID_IPv4_NETMASK = 206;

    /**
     * Alias for VALID_IPv4_NETMASK
     */
    const VALID_NETMASK = 207;

    /**
     * A valid mac address like 00:16:3E:78:7A:7B 
     */
    const VALID_MACADDRESS = 208;

    /**



      /**
     * This collection holds the parameter values as primitive datatype or adapter objects.
     * @var \Nethgui\Core\ParameterSet
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
     * @var \Nethgui\Core\RequestInterface
     */
    private $request;

    /**
     * Keeps arguments to show dialog boxes in prepareView
     * @var array
     */
    private $dialogBoxes = array();

    /**
     * @param string $identifier
     */
    public function __construct($identifier = NULL)
    {
        parent::__construct($identifier);
        $this->parameters = new \Nethgui\Core\ParameterSet();
        $this->autosave = TRUE;
        $this->request = NullRequest::getInstance();
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
     * @see \Nethgui\System\PlatformInterface::getIdentityAdapter()
     * @see \Nethgui\System\PlatformInterface::getMapAdapter()
     *
     * @param string $parameterName The name of the parameter
     * @param mixed $validator Optional - A regular expression catching the correct value format OR An constant-integer corresponding to a predefined validator OR boolean FALSE for a readonly parameter
     * @param mixed $valueProvider Optional - A callback function, an adapter instance or an array of arguments to create an adapter
     */
    protected function declareParameter($parameterName, $validator = FALSE, $valueProvider = NULL)
    {
        if (is_string($validator) && $validator[0] == '/') {
            $validator = $this->createValidator()->regexp($validator);
        } elseif ($validator === FALSE) {
            $validator = $this->createValidator()->forceResult(FALSE);
        } elseif (is_integer($validator)) {
            $validator = $this->createValidatorFromInteger($validator);
        }

        // At this point $validator MUST be an object implementing the right interface
        if ( ! $validator instanceof \Nethgui\Core\ValidatorInterface) {
            throw new \InvalidArgumentException(sprintf('%s: Invalid validator value for parameter `%s`', get_class($this), $parameterName), 1322149486);
        }

        $this->validators[$parameterName] = $validator;

        if (is_callable($valueProvider)) {
            // Create a read-only Map Adapter using $valueProvider as read-callback
            $this->parameters->register($this->getPlatform()->getMapAdapter($valueProvider, NULL, array()), $parameterName);
        } elseif ($valueProvider instanceof \Nethgui\Adapter\AdapterInterface) {
            $this->parameters->register($valueProvider, $parameterName);
        } elseif (is_array($valueProvider)) {
            $this->parameters[$parameterName] = $this->getAdapterForParameter($parameterName, $valueProvider);
        } elseif (is_null($valueProvider)) {
            $this->parameters[$parameterName] = NULL;
        } else {
            throw new \InvalidArgumentException(sprintf('%s: Invalid `valueProvider` argument', get_class($this)), 1322149487);
        }
    }

    /**
     * Enqueue an event to be signalled lately
     * 
     * The given $eventName is required to be signalled after 
     * all database changes.
     *
     * @see signalAllEventsFinally()
     * @param string $eventName
     * @param array $eventArgs Arguments to the event. You can pass a callback function as argument provider. The callback will be invoked with the event name as first argument.
     * @param \Nethgui\System\EventObserverInterface $observer Optional
     */
    protected function requireEvent($eventName, $eventArgs = array(), $observer = NULL)
    {
        if (is_string($eventName)) {
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
            $type = \Nethgui\Client\AbstractNotification::NOTIFY_ERROR;
            $messageTemplate = $eventName . '_failure';
        } else {
            $type = \Nethgui\Client\AbstractNotification::NOTIFY_SUCCESS;
            $messageTemplate = $eventName . '_success';
        }

        $this->dialogBoxes[] = array(array($messageTemplate, $messageArgs), array(), $type);
    }

    /**
     * Raise enqueued events.
     *
     * Signals all required events, flushing the event queue.
     *
     * @see requireEvent()
     */
    protected function signalAllEventsFinally()
    {
        while ($eventCall = array_shift($this->requiredEvents)) {
            $this->getPlatform()->signalEventFinally($eventCall[0], $eventCall[1], $eventCall[2]);
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
        $validator = $this->createValidator();

        switch ($ruleCode) {
            case self::VALID_ANYTHING:
                return $validator->forceResult(TRUE);

            case self::VALID_ANYTHING_COLLECTION:
                return $validator->orValidator($this->createValidator()->isEmpty(), $this->createValidator()->collectionValidator($this->createValidator()->forceResult(TRUE)));

            case self::VALID_USERNAME_COLLECTION:
                return $validator->orValidator($this->createValidator()->isEmpty(), $this->createValidator()->collectionValidator($this->createValidator()->username()));

            case self::VALID_SERVICESTATUS:
                return $validator->memberOf('enabled', 'disabled');

            case self::VALID_USERNAME:
                return $validator->username();

            case self::VALID_HOSTNAME:
                return $validator->hostname();

            case self::VALID_HOSTADDRESS:
                return $validator->orValidator($this->createValidator()->ipV4Address(), $this->createValidator()->hostname());

            case self::VALID_NOTEMPTY:
                return $validator->notEmpty();

            case self::VALID_DATE:
                return $validator->date();

            case self::VALID_TIME:
                return $validator->time();

            case self::VALID_IP:
            case self::VALID_IPv4:
                return $validator->ipV4Address();

            case self::VALID_NETMASK:
            case self::VALID_IPv4_NETMASK:
                return $validator->ipV4Netmask();

            case self::VALID_MACADDRESS:
                return $validator->macAddress();

            case self::VALID_POSITIVE_INTEGER:
                return $validator->integer()->positive();

            case self::VALID_PORTNUMBER:
                return $validator->integer()->greatThan(0)->lessThan(65535);

            case self::VALID_BOOLEAN:
                return $validator->memberOf('1', 'yes', '0', '');

            case self::VALID_IP_OR_EMPTY:
            case self::VALID_IPv4_OR_EMPTY:
                return $validator->orValidator($this->createValidator()->ipV4Address(), $this->createValidator()->isEmpty());

            case self::VALID_YES_NO:
                return $validator->memberOf('yes', 'no');
        }

        throw new \InvalidArgumentException(sprintf('%s: Unknown standard validator code: %s', get_class($this), $ruleCode), 1322149658);
    }

    /**
     * @return \Nethgui\System\Validator
     */
    protected function createValidator()
    {
        if ( ! $this->getPlatform() instanceof \Nethgui\System\PlatformInterface) {
            throw new \UnexpectedValueException(sprintf('%s: the platform instance has not been set!', get_class($this)), 1322822430);
        }
        return $this->getPlatform()->createValidator();
    }

    /**
     * Helps in creation of complex adapters.
     * @param array|callable $args
     * @return \Nethgui\Adapter\AdapterInterface
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
            if (is_array($args) && isset($args[0]) && (is_string($args[0]) || $args[0] instanceof \ArrayAccess)) {
                $args = array($args);
            }

            $adapterObject = $this->getPlatform()->getMapAdapter(
                array($this, $readerCallback), array($this, $writerCallback), $args
            );
        } elseif (isset($args[0], $args[1])) {
            // Get an identity adapter:
            $database = $args[0];
            $key = (string) $args[1];
            $prop = isset($args[2]) ? (string) $args[2] : NULL;
            $separator = isset($args[3]) ? (string) $args[3] : NULL;

            $adapterObject = $this->getPlatform()->getIdentityAdapter($database, $key, $prop, $separator);
        }

        if (is_null($adapterObject)) {
            throw new \InvalidArgumentException(sprintf('%s: Cannot create an adapter for parameter `%s`', get_class($this), $parameterName), 1322149696);
        }

        return $adapterObject;
    }

    public function bind(\Nethgui\Core\RequestInterface $request)
    {
        $this->request = $request;
        foreach ($this->parameters->getKeys() as $parameterName) {
            if ($request->hasParameter($parameterName)) {
                $this->parameters[$parameterName] = $request->getParameter($parameterName);
            }
        }
    }

    public function validate(\Nethgui\Core\ValidationReportInterface $report)
    {
        foreach ($this->parameters->getKeys() as $parameterName) {
            if ( ! $this->getRequest()->hasParameter($parameterName)) {
                continue; // missing parameters are not validated
            }

            if ( ! isset($this->validators[$parameterName])) {
                throw new \Nethgui\Exception\HttpException(sprintf('%s: Do not know how to validate `%s`', get_class($this), $parameterName), 400, 1322148402);
            }

            $validator = $this->validators[$parameterName];

            $isValid = $validator->evaluate($this->parameters[$parameterName]);
            if ($isValid !== TRUE) {
                $report->addValidationError(new \Nethgui\Client\ModuleSurrogate($this), $parameterName, $validator);
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

    public function prepareView(\Nethgui\Core\ViewInterface $view)
    {
        parent::prepareView($view);
        $view->copyFrom($this->parameters);
        foreach ($this->dialogBoxes as $dialogArgs) {
           // TODO invoke showNotification command
        }
        if ($view->getTargetFormat() === $view::TARGET_XHTML) {
            $view['__invalidParameters'] = $this->invalidParameters;
        }
    }

    /**
     *
     * @return \Nethgui\Core\RequestInterface
     */
    protected function getRequest()
    {
        return $this->request;
    }

}

