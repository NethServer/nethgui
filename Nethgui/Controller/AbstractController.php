<?php
namespace Nethgui\Controller;

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
 * 
 * 
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 * @api
 */
abstract class AbstractController extends \Nethgui\Module\AbstractModule implements \Nethgui\Controller\RequestHandlerInterface
{
    /**
     * This collection holds the parameter values as primitive datatype or adapter objects.
     *
     * @api
     * @var \Nethgui\Adapter\ParameterSet
     */
    protected $parameters;

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
     * @var \Nethgui\Controller\RequestInterface
     */
    private $request;

    /**
     * @param string $identifier
     */
    public function __construct($identifier = NULL)
    {
        parent::__construct($identifier);
        $this->parameters = new \Nethgui\Adapter\ParameterSet();
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
            $validator = $this->createValidator($validator);
        }

        // At this point $validator MUST be an object implementing the right interface
        if ( ! $validator instanceof \Nethgui\System\ValidatorInterface) {
            throw new \InvalidArgumentException(sprintf('%s: Invalid validator instance for parameter `%s`', get_class($this), $parameterName), 1322149486);
        }

        $this->validators[$parameterName] = $validator;

        if (is_callable($valueProvider)) {
            // Create a read-only Map Adapter using $valueProvider as read-callback
            $this->parameters->addAdapter($this->getPlatform()->getMapAdapter($valueProvider, NULL, array()), $parameterName);
        } elseif ($valueProvider instanceof \Nethgui\Adapter\AdapterInterface) {
            $this->parameters->addAdapter($valueProvider, $parameterName);
        } elseif (is_array($valueProvider)) {
            $this->parameters[$parameterName] = $this->getAdapterForParameter($parameterName, $valueProvider);
        } elseif (is_null($valueProvider)) {
            $this->parameters[$parameterName] = NULL;
        } else {
            throw new \InvalidArgumentException(sprintf('%s: Invalid `valueProvider` argument', get_class($this)), 1322149487);
        }
    }

    /**
     * Get the validator object for the given parameter. 
     * 
     * You must declare the parameter prior to call this method.
     * 
     * @api
     * @param string $parameterName The name of the validated parameter
     * @return \Nethgui\System\ValidatorInterface The validator object associated to the given parameter
     */
    protected function getValidator($parameterName) {
        if(! $this->validators[$parameterName] instanceof \Nethgui\System\ValidatorInterface) {
            throw new \LogicException(sprintf('%s: you must declare a parameter to obtain its validator object', __CLASS__), 1337002629);
        }
        return $this->validators[$parameterName];
    }
    
    /**
     * @param integer $ruleCode See \Nethgui\System\PlatformInterface::createValidator()
     * @return \Nethgui\System\Validator
     */
    protected function createValidator($ruleCode = NULL)
    {
        if ( ! $this->getPlatform() instanceof \Nethgui\System\PlatformInterface) {
            throw new \UnexpectedValueException(sprintf('%s: the platform instance has not been set!', get_class($this)), 1322822430);
        }
        return $this->getPlatform()->createValidator($ruleCode);
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

        if ( ! $adapterObject instanceof \Nethgui\Adapter\AdapterInterface) {
            throw new \InvalidArgumentException(sprintf('%s: Cannot create an adapter for parameter `%s`', get_class($this), $parameterName), 1322149696);
        }

        return $adapterObject;
    }

    public function bind(\Nethgui\Controller\RequestInterface $request)
    {
        $this->request = $request;
        foreach ($this->parameters->getKeys() as $parameterName) {
            if ($request->hasParameter($parameterName)) {
                $this->parameters[$parameterName] = $request->getParameter($parameterName);
            }
        }
    }

    public function validate(\Nethgui\Controller\ValidationReportInterface $report)
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
                $report->addValidationError($this, $parameterName, $validator);
                $this->invalidParameters[] = $parameterName;
            }
        }
    }

    /**
     * Save parameters on mutation
     */
    public function process()
    {
        if ($this->getRequest()->isMutation()) {

            $changes = $this->parameters->getModifiedKeys();

            if ($this->parameters->save()) {
                $this->onParametersSaved($changes);
            }
        }
    }

    protected function onParametersSaved($changedParameters)
    {
        // NOOP
    }

    public function nextPath()
    {
        return FALSE;
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);
        $view->copyFrom($this->parameters);
        if ($view->getTargetFormat() === $view::TARGET_XHTML) {
            $view['__invalidParameters'] = $this->invalidParameters;
        }
    }

    /**
     * @return \Nethgui\Controller\RequestInterface
     */
    protected function getRequest()
    {
        return $this->request;
    }

}
