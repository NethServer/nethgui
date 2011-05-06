<?php
/**
 * NethGui
 *
 * @package Core
 * @subpackage Module
 */

/**
 * TODO: describe class
 *
 * @package Core
 * @subpackage Module
 */
class NethGui_Core_Module_TableController extends NethGui_Core_Module_Controller
{

    /**
     *
     * @param array $columns
     */
    private $columns;
    /**
     *
     * @var NethGui_Adapter_TableAdapter
     */
    private $tableAdapter;

    /**
     * @var string
     */
    private $databaseName;

    /**
     * @var string
     */
    private $keyType;

    /**
     * @var array
     */
    private $parameterSchema;

    /**
     * @var array
     */
    private $actions;

    /**
     *
     * @param string $identifier
     * @param string $database
     * @param string $type
     * @param NethGui_Core_Validator|int $keyValidator
     * @param array $columns
     * @param array $actions
     */
    public function __construct($identifier, $database, $type, $parameterSchema, $columns, $actions)
    {
        parent::__construct($identifier);
        $this->viewTemplate = NULL; // use default

        $this->databaseName = $database;
        $this->keyType = $type;
        $this->columns = $columns;
        $this->parameterSchema = $parameterSchema;
        $this->actions = $actions;
    }

    public function initialize()
    {
        parent::initialize();
        $this->tableAdapter = new NethGui_Adapter_TableAdapter($this->getHostConfiguration()->getDatabase($this->databaseName), $this->keyType);


        // set the default action
        $this->addChild(new NethGui_Core_Module_ActionIndex('index'));

        $actionObjects = array(0 => FALSE); // set the read action object placeholder.
        $tableActions = array();
        $columnActions = array();

        foreach ($this->actions as $actionArguments) {

            list($actionName, $viewTemplate, $isTableAction) = $actionArguments;

            if ($isTableAction === TRUE) {
                $tableActions[] = $actionName;
            } else {
                $columnActions[] = $actionName;
            }

            if ($actionArguments instanceof NethGui_Core_Module_Standard) {
                $actionObjects[] = $actionArguments;
            } else {
                $actionObjects[] = new NethGui_Core_Module_TableModify($actionName, $this->tableAdapter, $this->parameterSchema, $viewTemplate);
            }
        }

        // add the read case
        $actionObjects[0] = new NethGui_Core_Module_TableRead('read', $this->tableAdapter, $this->columns, $tableActions, $columnActions);

        // Finally add all the actions
        foreach ($actionObjects as $actionObject) {
            $this->addChild($actionObject);
        }
    }

    public function bind(NethGui_Core_RequestInterface $request)
    {
        parent::bind($request);
        if ( ! $request->isSubmitted()) {
            $this->autosave = FALSE;
        }
    }

    public function process()
    {
        $exitCode = parent::process();
        if ($this->autosave === TRUE) {
            $this->tableAdapter->save();
        }
        return $exitCode;
    }

}