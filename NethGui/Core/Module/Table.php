<?php
/**
 * NethGui
 *
 * @package NethGuiFramework
 */

/**
 * TODO: describe class
 *
 * @package NethGuiFramework
 * @subpackage StandardImplementation
 */
class NethGui_Core_Module_Table extends NethGui_Core_Module_Composite
{
    const READ = 0;
    const CREATE = 1;
    const UPDATE = 2;
    const DELETE = 3;

    /**
     * Action type.
     * @var integer
     */
    private $action;
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
     * @var bool;
     */
    private $readonly;
    /**
     *
     * @param array $columns
     */
    private $columns;

    /**
     *
     * @param string $database
     * @param string $type
     * @param array $columns
     * @param NethGui_Core_Module_TableDialog|array
     */
    public function __construct($database, $type, $columns, $dialog = NULL, $events = array())
    {
        parent::__construct($database . '_' . $type);
        $this->autosave = FALSE; // disable auto saving of parameters in process()
        $this->database = $database;
        $this->type = $type;
        $this->columns = array_values($columns);
        $this->events = $events;

        if (is_array($dialog)) {
            $dialog = $this->createDialogFromArray($dialog);
        }

        if ($dialog instanceof NethGui_Core_Module_TableDialog) {
            $this->addChild($dialog);
            $this->readonly = FALSE;
        } else {
            $this->readonly = TRUE;
        }
    }

    public function initialize()
    {
        parent::initialize();

        if ($this->readonly) {
            $actionValidator = $this->getValidator()->memberOf('READ');
        } else {
            $actionValidator = $this->getValidator()->memberOf('READ', 'CREATE', 'UPDATE', 'DELETE');
        }

        $this->declareParameter('action', $actionValidator, NULL, 'READ');

        $this->declareParameter('key', FALSE, NULL, NULL);

        $this->declareParameter('page', FALSE, NULL, 1);

        $this->declareParameter('size', $this->getValidator()->memberOf(10, 20, 50, 100), NULL, 20);

        $this->declareParameter('rows', FALSE, NULL, NULL);

        $this->declareImmutable('columns', $this->columns);
    }

    /**
     * @todo Do implementation!
     * @param array $dialogArguments
     * @return NethGui_Core_Module_TableDialog
     */
    private function createDialogFromArray($dialogArguments)
    {
        throw new Exception('not implemented!');
    }

    public function bind(NethGui_Core_RequestInterface $request)
    {
        parent::bind($request);

        if ( ! $request->isSubmitted()) {
            $action = $request->getParameter('0');
            $key = $request->getParameter('1');
        } else {
            $action = $request->getParameter('action');
            $key = NULL;
        }

        $this->parameters['size'] = 20;
        $this->parameters['key'] = $key;
        $this->parameters['page'] = 0;
        $this->parameters['rows'] = $this->fetchRows();

        if (strtolower($action) == 'update') {
            $this->action = self::UPDATE;
            $this->loadDialogValues($key);
            $this->parameters['action'] = 'UPDATE';
        } elseif (strtolower($action) == 'create') {
            $this->action = self::CREATE;
            $this->parameters['action'] = 'CREATE';
        } elseif (strtolower($action) == 'delete') {
            $this->action = self::DELETE;
            $this->parameters['action'] = 'DELETE';
        } else {
            $this->action = self::READ;
            $this->parameters['action'] = 'READ';
        }
    }

    private function fetchRows()
    {
        $rows = array();

        foreach($this->getHostConfiguration()->getDatabase($this->database)->getAll($this->type) as $key => $values)
        {
            $row = array();

            foreach($this->columns as $columnIndex => $column) {
                if($columnIndex == 0) {
                    $row[] = $key;
                } else {
                    $row[] = isset($values[$column]) ? $values[$column] : NULL;
                }
            }

            $rows[] = $row;
        }

        return $rows;
    }

    private function loadDialogValues($key)
    {
        $dialog = array_shift($this->getChildren());
        if ( ! $dialog instanceof NethGui_Core_Module_TableDialog
            || is_null($key)) {
            return;
        }
        $db = $this->getHostConfiguration()->getDatabase($this->database);
        $values = $db->getKey($key);
        $dialog->loadValues($key, $values);
    }

    /**
     *
     * @param string $key
     * @param array $values
     */
    public function onDialogSave($key, $values)
    {
        $db = $this->getHostConfiguration()->getDatabase($this->database);

        switch ($this->action) {
            case self::UPDATE:
                $success = $db->setProp($key, $values);
                break;

            case self::CREATE:
                $success = $db->setKey($key, $this->type, $values);
                break;
            default:
                $success = FALSE;
        }

        if ($success) {
            // TODO: trigger events ?
        }
    }

}