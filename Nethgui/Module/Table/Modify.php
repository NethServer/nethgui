<?php
/**
 * @package Module
 * @subpackage Table
 * @author Davide Principi <davide.principi@nethesis.it>
 */

/**
 * Processes the table modification actions: create, update, delete
 *
 * @see Nethgui\Module\Table\Read
 * @package Module
 * @subpackage Table
 * 
 */
class Nethgui\Module\Table\Modify extends Nethgui\Module\Table\Action
{
    const KEY = 10;
    const FIELD = 11;

    private $parameterSchema;

    /**
     * This holds the name of the key parameter
     * @var string
     */
    private $key;

    /**
     * Values passed into the view in GET/create
     * @var array
     */
    private $createDefaults = array();

    public function __construct($identifier, $parameterSchema, $requireEvents, $viewTemplate = NULL)
    {
        if ( ! in_array($identifier, array('create', 'delete', 'update'))) {
            throw new InvalidArgumentException('Module identifier must be one of `create`, `delete`, `update` values.');
        }

        parent::__construct($identifier);
        $this->setViewTemplate($viewTemplate);
        $this->parameterSchema = $parameterSchema;
        $this->autosave = FALSE;

        foreach ($requireEvents as $eventData) {
            if (is_string($eventData)) {
                $this->requireEvent($eventData);
            } elseif (is_array($eventData)) {
                /*
                 * Sanitize requireEvent arguments:
                 */
                if ( ! isset($eventData[1])) {
                    $eventData[1] = array();
                }
                if ( ! isset($eventData[2])) {
                    $eventData[2] = NULL;
                }
                $this->requireEvent($eventData[0], $eventData[1], $eventData[2]);
            }
        }
    }

    private function getTheKey(Nethgui\Core\RequestInterface $request, $parameterName)
    {
        if ($request->isSubmitted()) {
            if ($request->hasParameter($parameterName)) {
                $keyValue = $request->getParameter($parameterName);
            } else {
                $keyValue = NULL;
            }
        } else {
            // Unsubmitted request.
            // - The key (if set) is the first of the $request arguments        

            $arguments = $request->getArguments();
            $keyValue = isset($arguments[0]) ? $arguments[0] : NULL;
        }

        return $keyValue;
    }

    public function initialize()
    {
        parent::initialize();
        foreach ($this->parameterSchema as $declarationIndex => $parameterDeclaration) {
            $parameterName = array_shift($parameterDeclaration);
            $validator = array_shift($parameterDeclaration);
            $valueProvider = array_shift($parameterDeclaration);

            $useTableAdapter = $this->hasTableAdapter()
                && is_integer($valueProvider);

            if ($useTableAdapter && $valueProvider === self::KEY) {
                $this->key = $parameterName;
                break;
            }
        }
    }

    /**
     * We have to declare all the parmeters of parameterSchema here,
     * binding the actual key/row from tableAdapter.
     * @param Nethgui\Core\RequestInterface $request 
     */
    public function bind(Nethgui\Core\RequestInterface $request)
    {
        $key = NULL;

        foreach ($this->parameterSchema as $declarationIndex => $parameterDeclaration) {

            $parameterName = array_shift($parameterDeclaration);
            $validator = array_shift($parameterDeclaration);
            $valueProvider = array_shift($parameterDeclaration);

            $useTableAdapter = $this->hasTableAdapter()
                && is_integer($valueProvider);

            $isKeyDeclaration = ($useTableAdapter && $valueProvider === self::KEY);

            // Deprecated key declaration warning:
            if ($declarationIndex === 0 && $valueProvider === NULL) {
                $isKeyDeclaration = TRUE;
                $this->getLog()->warning('Deprecated key declaration form. See..');
            }

            $isFieldDeclaration = $useTableAdapter
                && $valueProvider === self::FIELD
                && ! is_null($key);


            if ($isKeyDeclaration) {
                $valueProvider = NULL;
                $key = $this->getTheKey($request, $parameterName);
            } elseif ($isFieldDeclaration) {

                $prop = array_shift($parameterDeclaration);
                $separator = array_shift($parameterDeclaration);

                if (is_null($prop)) {
                    // expect the table column name is the same as parameter name
                    $prop = $parameterName;
                }

                $valueProvider = array($this->tableAdapter, $key, $prop, $separator);
            } elseif ($useTableAdapter && is_null($key)) {
                $valueProvider = NULL;
            }

            $parameterDeclaration = array($parameterName, $validator, $valueProvider);

            call_user_func_array(array($this, 'declareParameter'), $parameterDeclaration);

            if ($isKeyDeclaration) {
                $this->parameters[$parameterName] = $key;
            }
        }

        parent::bind($request);

        if ( ! $request->isSubmitted()
            && $this->getIdentifier() == 'create') {
            foreach ($this->createDefaults as $paramName => $paramValue) {
                $this->parameters[$paramName] = $paramValue;
            }
        }
    }

    public function process()
    {
        parent::process();
        if ( ! $this->getRequest()->isSubmitted()) {
            return;
        }

        $action = $this->getIdentifier();
        $key = $this->parameters[$this->key];

        if ($action == 'delete') {
            $this->processDelete($key);
        } elseif ($action == 'create') {
            $this->processCreate($key);
        } elseif ($action == 'update') {
            $this->processUpdate($key);
        } else {
            throw new Nethgui\Exception\HttpStatusClientError('Not found', 404);
        }

        // Transfer all parameters values into tableAdapter (and DB):
        $changes = $this->parameters->save();

        // Transfer all tableAdapter values into DB
        $changes += $this->tableAdapter->save();
        if ($changes > 0) {
            $this->signalAllEventsFinally();
        }               
    }

    protected function processDelete($key)
    {
        if (isset($this->tableAdapter[$key])) {
            unset($this->tableAdapter[$key]);
        } else {
            throw new Nethgui\Exception\Process('Cannot delete `' . $key . '`');
        }
        $this->addUiClientCommand('cancel');
    }

    protected function processCreate($key)
    {
        $this->addUiClientCommand('cancel');
    }

    protected function processUpdate($key)
    {
        $this->addUiClientCommand('cancel');
    }

    public function prepareView(Nethgui\Core\ViewInterface $view, $mode)
    {
        parent::prepareView($view, $mode);
        if ($mode == self::VIEW_SERVER) {
            $view['__key'] = $this->key;
        }
    }

    /**
     * Set the default parameter values in "create" action
     * @param array $defaultValues
     * @return Nethgui\Module\Table\Modify
     */
    public function setCreateDefaults($defaultValues)
    {
        $this->createDefaults = $defaultValues;
        return $this;
    }

}