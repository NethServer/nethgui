<?php
/**
 * @package Core
 * @subpackage Module
 * @author Davide Principi <davide.principi@nethesis.it>
 */

/**
 * @package Core
 * @subpackage Module
 */
class NethGui_Core_Module_TableModify extends NethGui_Core_Module_Action
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
     * @var ArrayAccess
     */
    private $tableAdapter;

    public function __construct($identifier, $tableAdapter, $parameterSchema, $viewTemplate = NULL)
    {
        parent::__construct($identifier);
        $this->viewTemplate = $viewTemplate;
        $this->tableAdapter = $tableAdapter;
        $this->parameterSchema = $parameterSchema;
    }

    public function initialize()
    {
        parent::initialize();
        foreach ($this->parameterSchema as $args) {
            call_user_func_array(array($this, 'declareParameter'), $args);
        }

        if ( ! isset($this->key)) {
            $this->key = $this->parameterSchema[0][0];
        }
    }

    public function bind(NethGui_Core_RequestInterface $request)
    {
        parent::bind($request);
        if ($request->isSubmitted()) {
            $this->performAction = TRUE;
        } else {
            $arguments = $this->getArguments();
            $this->parameters[$this->key] = isset($arguments[0]) ? $arguments[0] : NULL;            
        }
    }

    public function process()
    {
        parent::process();
        if ($this->performAction) {

            $action = $this->getActionName();

            $db = $this->getParent()->getDatabase();

            if ($action == 'delete') {
                $success = $db->deleteKey($this->parameters[$this->key]);
                //unset($db[$this->parameters['key']]);
                //$success = $db->save();
            } elseif ($action == 'create') {
                throw new Exception('Not Implemented');
            } elseif ($action == 'update') {
                throw new Exception('Not Implemented');
            } else {
                throw new NethGui_Exception_HttpStatusClientError('Not found', 404);
            }

            if ( ! $success) {
                throw new NethGui_Exception_Process(ucfirst($action) . ' on key ' . $key . ' failed!');
            }
        }
    }

}