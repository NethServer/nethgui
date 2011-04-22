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
     * @var ArrayAccess
     */
    private $tableAdapter;

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
        $this->columns = $columns;

        // XXX: create a real adapter.
        $this->tableAdapter = new ArrayObject();

        // set the default action
        $this->addChild(new NethGui_Core_Module_ActionIndex('index'));

        
        $actionObjects = array(0 => FALSE); // set the read action object placeholder.
        $tableActions = array();

        foreach ($actions as $actionArguments) {

            list($actionName, $viewTemplate, $isTableAction) = $actionArguments;

            if($isTableAction === TRUE) {
                $tableActions[] = $actionName;
            }

            if ($actionArguments instanceof NethGui_Core_Module_Action) {
                $actionObjects[] = $actionArguments;
            } else {
                $actionObjects[] = new NethGui_Core_Module_TableModify($actionName, $this->tableAdapter, $parameterSchema, $viewTemplate);
            }
        }

        // add the read case
        $actionObjects[0] = new NethGui_Core_Module_TableRead('read', $this->tableAdapter, $columns, $tableActions);

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
        parent::process();
        if ($this->autosave === TRUE) {
            //$this->tableAdapter->save();
        }
    }

    public function prepareView(NethGui_Core_ViewInterface $view, $mode)
    {
        $view['action'] = $this->getCurrentAction()->getIdentifier();
        parent::prepareView($view, $mode); 
    }

}