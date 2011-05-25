<?php
/**
 * @package Module
 * @author Davide Principi <davide.principi@nethesis.it>
 */

/**
 * Processes a table modification actions: create, update, delete
 *
 * @see NethGui_Module_TableRead
 * @package Module
 */
class NethGui_Module_TableModify extends NethGui_Core_Module_Standard
{

    private $parameterSchema;
    private $performAction = FALSE;
    /**
     * This holds the name of the key parameter
     * @var string
     */
    private $key;
    /**
     *
     * @var NethGui_Adapter_AdapterInterface
     */
    private $tableAdapter;


    public function __construct($identifier, NethGui_Adapter_AdapterInterface $tableAdapter, $parameterSchema, $requireEvents, $viewTemplate = NULL)
    {
        if ( ! in_array($identifier, array('create', 'delete', 'update'))) {
            throw new InvalidArgumentException('Module identifier must be one of `create`, `delete`, `update` values.');
        }

        parent::__construct($identifier);
        $this->viewTemplate = $viewTemplate;
        $this->tableAdapter = $tableAdapter;
        $this->parameterSchema = $parameterSchema;
        $this->key = $this->parameterSchema[0][0];
        $this->autosave = FALSE;
        
        foreach ($requireEvents as $eventData) {
            if (is_string($eventData)) {
                $this->requireEvent($eventData);
            } elseif (is_array($eventData)) {
                if(!isset($eventData[1])) {
                    $eventData[1] = array();
                }        
                if(!isset($eventData[2])) {
                    $eventData[2] = NULL;
                }                    
                $this->requireEvent($eventData[0], $eventData[1], $eventData[2]);
            }
        }
    }

    public function initialize()
    {
        parent::initialize();
        foreach ($this->parameterSchema as $args) {
            call_user_func_array(array($this, 'declareParameter'), $args);
        }
    }

    public function bind(NethGui_Core_RequestInterface $request)
    {
        parent::bind($request);
        if ($request->isSubmitted()) {
            $this->performAction = TRUE;
            return; // All values are in $request parameters. We can exit here.
        }

        // We have to fetch the parameter values from the data source.

        $arguments = $request->getArguments();
        $keyValue = isset($arguments[0]) ? $arguments[0] : NULL;

        if (is_null($keyValue)) {
            return; // Nothing to do: the key is not set.
        }

        // Bind the key value to key parameter
        $this->parameters[$this->key] = $keyValue;

        if ( ! $this->tableAdapter->offsetExists($keyValue)) {
            return; // Nothing to do: the data we are missing the data row
        }
        
        $values = $this->tableAdapter[$keyValue];
            
       
        // Bind other values following the order defined into parameterSchema                 
        foreach ($this->parameterSchema as $parameterDeclaration) {
            $parameterName = $parameterDeclaration[0];

            if ($parameterName == $this->key) {
                continue; // Skip the key, we have it already.
            }

            // Bind the n-th value to $parameterName.
            $this->parameters[$parameterName] = $values[$parameterName];
        }
    }

    public function process()
    {
        parent::process();
        if ($this->performAction) {

            $action = $this->getIdentifier();

            if ($action == 'delete') {

                $key = $this->parameters[$this->key];

                if (isset($this->tableAdapter[$key])) {
                    unset($this->tableAdapter[$key]);
                } else {
                    throw new NethGui_Exception_Process('Cannot delete `' . $key . '`');
                }

                // Redirect to parent controller module              
                $this->getUser()->setRedirect($this->getParent());
            } elseif ($action == 'create' || $action == 'update') {

                $values = $this->parameters->getArrayCopy();

                $key = $this->parameters[$this->key];

                if (isset($values[$this->key])) {
                    unset($values[$this->key]);
                }

                $this->tableAdapter[$key] = $values;

                // Redirect to parent controller module                
                $this->getUser()->setRedirect($this->getParent());
            } else {
                throw new NethGui_Exception_HttpStatusClientError('Not found', 404);
            }

            $changes = $this->tableAdapter->save();
            if ($changes > 0) {
                $this->signalAllEventsAsync();
            }
        }
    }

    public function prepareView(NethGui_Core_ViewInterface $view, $mode)
    {
        parent::prepareView($view, $mode);
        if ($mode == self::VIEW_REFRESH) {
            $view['__key'] = $this->key;
        }
    }

}