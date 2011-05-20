<?php
/**
 * NethGui
 *
 * @package Module
 */

/**
 * A Controller that handles a generic table CRUD scenario
 *
 * @see NethGui_Module_TableModify
 * @see NethGui_Module_TableRead
 * @package Module
 */
class NethGui_Module_TableController extends NethGui_Core_Module_Controller
{

    /**
     *
     * @param array $columns
     */
    private $columns;
    /**
     *
     * @var array
     */
    private $tableAdapterArguments;
    /**
     * @var array
     */
    private $parameterSchema;
    /**
     * @var array
     */
    private $actions;

    /**
     * @param string $identifier
     * @param array $tableAdapterArguments     
     * @param NethGui_Core_Validator|int $keyValidator
     * @param array $columns
     * @param array $actions
     */
    public function __construct($identifier, $tableAdapterArguments, $parameterSchema, $columns, $actions)
    {
        parent::__construct($identifier);
        $this->tableAdapterArguments = $tableAdapterArguments;
        $this->columns = $columns;
        $this->parameterSchema = $parameterSchema;
        $this->actions = $actions;
    }

    public function initialize()
    {
        parent::initialize();        

        $tableAdapter = call_user_func_array(array($this->getHostConfiguration(), 'getTableAdapter'), $this->tableAdapterArguments);
        
        $actionObjects = array(0 => FALSE); // set the read action object placeholder.
        $tableActions = array();
        $columnActions = array();

        foreach ($this->actions as $actionArguments) {

            list($actionName, $requireEvents, $viewTemplate, $isTableAction) = $actionArguments;

            if ($isTableAction === TRUE) {
                $tableActions[] = $actionName;
            } else {
                $columnActions[] = $actionName;
            }

            if (is_string($requireEvents)) {
                $requireEvents = array($requireEvents);
            }

            if ($actionArguments instanceof NethGui_Core_Module_Standard) {
                $actionObjects[] = $actionArguments;
            } else {
                $actionObjects[] = new NethGui_Module_TableModify($actionName, $tableAdapter, $this->parameterSchema, $requireEvents, $viewTemplate);
            }
        }

        // add the read case
        $actionObjects[0] = new NethGui_Module_TableRead('read', $tableAdapter, $this->columns, $tableActions, $columnActions);

        // Finally add all the actions
        foreach ($actionObjects as $actionObject) {
            $this->addChild($actionObject);
        }
    }

}