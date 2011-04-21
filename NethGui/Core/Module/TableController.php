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
class NethGui_Core_Module_TableController extends NethGui_Core_Module_Composite
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
     * @param string $identifier
     * @param string $database
     * @param string $type
     * @param array $columns
     * @param NethGui_Core_Module_TableDialog|array
     */
    public function __construct($identifier, $database, $type, $columns, $dialog = NULL, $events = array())
    {
        parent::__construct($identifier);
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
            $actionValidator = $this->getValidator()->memberOf('read');
        } else {
            $actionValidator = $this->getValidator()->memberOf('read', 'create', 'update', 'delete');
        }

        $this->declareParameter('action', $actionValidator, NULL, 'read');

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
            if ($request->hasParameter('0')) {
                $this->parameters['action'] = $request->getParameter('0');
            } else {
                $this->parameters['action'] = 'read';
            }
            if ($request->hasParameter('1')) {
                $this->parameters['key'] = $request->getParameter('1');
            } else {
                $this->parameters['key'] = NULL;
            }
        }

        $this->parameters['size'] = 20;
        $this->parameters['page'] = 0;

        if ($this->parameters['action'] == 'update') {
            $this->loadDialogValues($this->parameters['key']);
        }
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
        $dialog->loadValues($this->parameters['action'], $key, $values);
    }

    /**
     *
     * @param string $key
     * @param array $values
     */
    public function onDialogSave($key, $values)
    {
        $db = $this->getHostConfiguration()->getDatabase($this->database);

        $success = FALSE;

        switch ($this->parameters['action']) {
            case 'update':
                $success = $db->setProp($key, $values);
                break;

            case 'create':
                // XXX: check if a key exists by querying its type.
                if ($db->getType($key) === '') {
                    $success = $db->setKey($key, $this->type, $values);
                } else {
                    throw new NethGui_Exception_Process('Key already exists');
                }
                break;

            default:
                $success = FALSE;
        }

        if ($success) {
            // TODO: trigger events ?
        }
    }

    public function process()
    {
        parent::process();

        if ($this->parameters['action'] == 'delete') {
            $db = $this->getHostConfiguration()->getDatabase($this->database);

            $success = $db->deleteKey($this->parameters['key']);
        }
    }




}