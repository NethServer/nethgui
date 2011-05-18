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
    /**
     *
     * @var array
     */
    private $requiredEvents;

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

        $this->requiredEvents = array();
        foreach ($requireEvents as $eventName) {
            $this->requiredEvents[] = $eventName;
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
        } else {
            $arguments = $request->getArguments();
            $key = isset($arguments[0]) ? $arguments[0] : NULL;
            $this->parameters[$this->key] = $key;

            if ( ! is_null($key)) {
                foreach ($this->parameterSchema as $parameterDeclaration) {
                    $parameterName = $parameterDeclaration[0];
                    if ($parameterName == $this->key) {
                        continue;
                    } elseif (isset($this->tableAdapter[$arguments[0]])) {
                        $values = $this->tableAdapter[$arguments[0]];
                        $this->parameters[$parameterName] = $values[$parameterName];
                    }
                }
            }
        }
    }

    public function process(NethGui_Core_NotificationCarrierInterface $carrier)
    {
        parent::process($carrier);
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
                $carrier->addRedirectOrder($this->getParent());
            } elseif ($action == 'create' || $action == 'update') {

                $values = $this->parameters->getArrayCopy();

                $key = $this->parameters[$this->key];

                if (isset($values[$this->key])) {
                    unset($values[$this->key]);
                }

                $this->tableAdapter[$key] = $values;

                // Redirect to parent controller module
                $carrier->addRedirectOrder($this->getParent());
            } else {
                throw new NethGui_Exception_HttpStatusClientError('Not found', 404);
            }
            
            $changes = $this->tableAdapter->save();
            if($changes > 0) {
                foreach($this->requiredEvents as $eventName) {
                    $this->getHostConfiguration()->signalEventAsync($eventName);
                }
            }
        }
    }

    public function prepareView(NethGui_Core_ViewInterface $view, $mode)
    {
        parent::prepareView($view, $mode);
        if ($mode == self::VIEW_REFRESH) {
            $view['__key'] = $this->key;
            $view['__action'] = $this->getIdentifier();
        }
    }

}