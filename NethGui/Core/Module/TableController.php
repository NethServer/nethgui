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
class NethGui_Core_Module_TableController extends NethGui_Core_Module_Composite implements NethGui_Core_Module_DialogDataProviderInterface
{

    /**
     *
     * @var string
     */
    private $database;
    /**
     *
     * @var string
     */
    private $type;
    /**
     *
     * @param array $columns
     */
    private $columns;
    /**
     * The association action - dialog
     * @var array
     */
    private $actionDialogMap;
    /**
     * This holds data returned by the active dialog
     * @var array
     */
    private $dialogData;

    private $skipActionProcessing;

    /**
     * 
     * @var mixed
     */
    private $actionState;

    /**
     *
     * @param string $identifier
     * @param string $database
     * @param string $type
     * @param array $columns
     * @param NethGui_Core_Module_TableDialog|array $dialogs An 'update' dialog case or an array of action/dialog cases
     * @param array $events
     */
    public function __construct($identifier, $database, $type, $columns, $dialogs = NULL, $events = array())
    {
        parent::__construct($identifier);
        $this->autosave = FALSE; // disable auto saving of parameters in process()
        $this->database = $database;
        $this->type = $type;
        $this->columns = array_values($columns);
        $this->events = $events;
        $this->dialogData = array();
        $this->skipActionProcessing = FALSE;

        if ($dialogs instanceof NethGui_Core_Module_TableDialog) {
            $dialogs = array(
                'create' => $dialogs,
                'update' => $dialogs,
            );
        }

        if (is_array($dialogs)) {
            $this->registerDialogs($dialogs);
        }
    }

    /**
     * @param array $dialogs
     * @return NethGui_Core_Module_TableDialog
     */
    private function registerDialogs($dialogs)
    {
        foreach ($dialogs as $action => $dialog) {
            if ( ! $dialog instanceof NethGui_Core_Module_TableDialog) {
                throw new InvalidArgumentException('Invalid NethGui_Core_Module_TableDialog instance');
            }
            if ($dialog->getParent() === NULL && $dialog !== $this) {
                $this->addChild($dialog);
            }

            $this->actionDialogMap[$action] = $dialog;
        }
    }

    public function initialize()
    {
        parent::initialize();

        $this->declareParameter('action', $this->getValidator()->memberOf($this->getHandledActions()), NULL, 'read');
        $this->declareParameter('key', FALSE, NULL, NULL);
        $this->declareParameter('rows', FALSE, NULL, NULL);

        $this->declareImmutable('columns', $this->columns);
    }

    /**
     * Inspects the current object, searching for 'processAction*' methods.
     *
     * @param array $filter An array of disabled actions
     *
     * @return array The array of actions handled by this object
     */
    protected function getHandledActions($filter = array())
    {
        $handledActions = array();
        $classMethods = get_class_methods($this);
        if ( ! is_null($classMethods)) {
            foreach ($classMethods as $methodName) {
                if (substr($methodName, 0, 13) == 'processAction') {
                    $handledActions[] = strtolower(substr($methodName, 13));
                }
            }
        }
        $handledActions = array_diff($handledActions, $filter);

        // read action can't be disabled.
        array_unshift($handledActions, 'read');

        return $handledActions;
    }

    public function bind(NethGui_Core_RequestInterface $request)
    {
        /*
         * If request is not submitted by the User we have to fetch
         * the state from GET vars before parent::bind.
         */
        if ( ! $request->isSubmitted()) {
            $this->parameters['action'] = 'read';

            if ($request->hasParameter('0')) {
                $this->parameters['action'] = $request->getParameter('0');
            }

            if ($request->hasParameter('1')) {
                $this->parameters['key'] = $request->getParameter('1');
            }
        }



        if ($this->actionHasDialog($this->parameters['action'])) {
            $this->fetchDialogData();

            if( ! $request->isSubmitted()) {
                $this->skipActionProcessing = TRUE;
            }
        }

        parent::bind($request);

        if (isset($this->parameters['action'], $this->actionDialogMap[$this->parameters['action']])) {
            $dialog = $this->actionDialogMap[$this->parameters['action']];
            if ($dialog instanceof NethGui_Core_Module_TableDialog) {
                $dialog->setAction($this->parameters['action']);
            }
        }
    }

    /**
     * @param string $action The action name to check
     * @return bool True, if the $action is handled through a dialog module.
     */
    private function actionHasDialog($action)
    {
        return in_array($action, array_keys($this->actionDialogMap));
    }

    /**
     * Initialize dialogData member, reading values from database.
     */
    private function fetchDialogData()
    {
        $db = $this->getHostConfiguration()->getDatabase($this->database);
        $values = $db->getKey($this->parameters['key']);
        $values[$this->type] = $this->parameters['key'];
        $this->dialogData = $values;
    }

    public function process()
    {
        parent::process();

        // if action is read we are done.
        if($this->skipActionProcessing || $this->parameters['action'] == 'read') {
            return;
        }

        $actionExitState = $this->invokeActionProcessing(array($this->dialogData));

        $this->actionState = array(
            $this->parameters['action'],
            $actionExitState
        );

        if ($actionExitState !== FALSE) {
            $this->parameters['action'] = 'read';
            $this->shutdownDialogs();
            // TODO: trigger events ?
        }
    }

    /*
     * 
     */
    private function shutdownDialogs()
    {
        $dialogs = array_values($this->actionDialogMap);        
        foreach ($dialogs as $dialog) {
            $dialog->setAction('read');
        }
    }

    private function invokeActionProcessing($args = array())
    {

        $actionMethod = 'processAction' . ucFirst($this->parameters['action']);

        // prepend action, db and key to action method arguments
        array_unshift($args,
            $this->getHostConfiguration()->getDatabase($this->database),
            $this->parameters['key']
        );

        return call_user_func_array(array($this, $actionMethod), $args);
    }

    /**
     * `Delete` action handler.
     *
     * Deletes a database key identified by the `key` parameter.
     */
    protected function processActionDelete(NethGui_Core_ConfigurationDatabase $db, $key, $values)
    {
        return $db->deleteKey($key);
    }

    protected function processActionUpdate(NethGui_Core_ConfigurationDatabase $db, $key, $values)
    {
        $success = $db->setProp($key, $values);
        if(!$success) {
         return FALSE;
        }

        return $key;
    }

    protected function processActionCreate(NethGui_Core_ConfigurationDatabase $db, $key, $values)
    {
        if ($db->getType($key) === '') {
            return $db->setKey($key, $this->type, $values);
        }
        throw new NethGui_Exception_Process('Key already exists');
    }


    public function prepareView(NethGui_Core_ViewInterface $view, $mode)
    {
        parent::prepareView($view, $mode);
        $view['rows'] = $this->prepareRows($view, $mode);
        $view['exitState'] = $this->actionState;

    }

    /**
     * Fetches the table rows, invoking column `prepare*` callbacks.
     * @param NethGui_Core_ViewInterface $view
     * @param integer $mode
     * @return array
     */
    private function prepareRows(NethGui_Core_ViewInterface $view, $mode)
    {
        $rows = array();

        foreach ($this->getHostConfiguration()->getDatabase($this->database)->getAll($this->type) as $key => $values) {
            $row = array();

            // adds the key to the values:
            $values[$this->columns[0]] = $key;

            foreach ($this->columns as $columnIndex => $column) {
                $row[] = $this->prepareColumnValue($view, $mode, $columnIndex, $column, $values);
            }

            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * Prepare the view state for a specific column.
     *
     * It tries to invoke a `prepareColumn<columnName>()` method, if it has been
     * defined. Default is returning the database value, if defined,
     * or NULL otherwise.
     *
     * @param NethGui_Core_ViewInterface $view
     * @param integer $mode
     * @param integer $columnIndex
     * @param string $columnName
     * @param array $values
     * @return mixed
     */
    private function prepareColumnValue($view, $mode, $columnIndex, $columnName, $values)
    {
        $methodName = 'prepareColumn' . ucfirst($columnName);

        if (method_exists($this, $methodName)) {
            $columnValue = call_user_func(array($this, $methodName), $view, $mode, $values);
        } else {
            $columnValue = isset($values[$columnName]) ? $values[$columnName] : NULL;
        }

        return $columnValue;
    }

    protected function prepareColumnActions(NethGui_Core_ViewInterface $view, $mode, $values)
    {
        if ($mode == self::VIEW_REFRESH) {
            $columnView = $view->spawnView($this);
            $columnView->setTemplate('NethGui_Core_View_TableActions');
        } else {
            $columnView = array();
        }

        $key = array_pop($values);

        foreach ($this->actionDialogMap as $actionName => $dialog) {
            $columnView[$actionName] = $view->buildUrl($actionName, $key);
        }

        $columnView['delete'] = $view->buildUrl('delete', $key);

        unset($columnView['create']);

        return $columnView;
    }

    public function getDialogData()
    {
        return $this->dialogData;
    }

    public function setDialogData($values)
    {
        $this->parameters['key'] = array_shift($values);
        $this->dialogData = $values;
    }

}