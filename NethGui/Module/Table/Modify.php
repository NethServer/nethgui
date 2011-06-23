<?php
/**
 * @package Module
 * @subpackage Table
 * @author Davide Principi <davide.principi@nethesis.it>
 */

/**
 * Processes the table modification actions: create, update, delete
 *
 * @see NethGui_Module_Table_Read
 * @package Module
 * @subpackage Table
 * 
 */
class NethGui_Module_Table_Modify extends NethGui_Module_Table_Action
{
    const KEY = 10;
    const FIELD = 11;

    private $parameterSchema;
    /**
     * This holds the name of the key parameter
     * @var string
     */
    private $key;

    public function __construct($identifier, $parameterSchema, $requireEvents, $viewTemplate = NULL)
    {
        if ( ! in_array($identifier, array('create', 'delete', 'update'))) {
            throw new InvalidArgumentException('Module identifier must be one of `create`, `delete`, `update` values.');
        }

        parent::__construct($identifier);
        $this->viewTemplate = $viewTemplate;
        $this->parameterSchema = $parameterSchema;

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

    private function getTheKey(NethGui_Core_RequestInterface $request, $parameterName)
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

    /**
     * We have to declare all the parmeters of parameterSchema here,
     * binding the actual key/row from tableAdapter.
     * @param NethGui_Core_RequestInterface $request 
     */
    public function bind(NethGui_Core_RequestInterface $request)
    {
        $key = NULL;

        foreach ($this->parameterSchema as $declarationIndex => $parameterDeclaration) {

            $parameterName = array_shift($parameterDeclaration);
            $validator = array_shift($parameterDeclaration);
            $valueProvider = array_shift($parameterDeclaration);

            $useTableAdapter = $this->hasTableAdapter()
                && is_integer($valueProvider);

            $isKeyDeclaration = ($useTableAdapter && $valueProvider === self::KEY)
                || ($declarationIndex === 0 && $valueProvider === NULL);

            $isFieldDeclaration = $useTableAdapter
                && $valueProvider === self::FIELD
                && ! is_null($key);


            if ($isKeyDeclaration) {
                $valueProvider = NULL;
                $key = $this->getTheKey($request, $parameterName);
                $this->key = $parameterName;
            } elseif ($isFieldDeclaration) {

                $prop = array_shift($parameterDeclaration);
                $separator = array_shift($parameterDeclaration);

                if (is_null($prop)) {
                    $prop = $parameterName;
                }

                $valueProvider = array($this->tableAdapter, $key, $prop, $separator);
            } elseif ($useTableAdapter && is_null($key)) {
                $valueProvider = NULL;
            }

            $parameterDeclaration = array($parameterName, $validator, $valueProvider);

            call_user_func_array(array($this, 'declareParameter'), $parameterDeclaration);

            // set the parameter
            if ($isKeyDeclaration) {
                $this->parameters[$parameterName] = $key;
            }
        }

        parent::bind($request);
    }

    public function process()
    {
        parent::process();
        if ( ! $this->getRequest()->isSubmitted()) {
            return;
        }

        $action = $this->getIdentifier();



        if ($action == 'delete') {
            $key = $this->parameters[$this->key];

            if (isset($this->tableAdapter[$key])) {
                unset($this->tableAdapter[$key]);
            } else {
                throw new NethGui_Exception_Process('Cannot delete `' . $key . '`');
            }
        } elseif ($action == 'create') {
            // PASS
        } elseif ($action == 'update') {
            // PASS 
        } else {
            throw new NethGui_Exception_HttpStatusClientError('Not found', 404);
        }

        // Redirect to parent controller module              

        $this->getRequest()->getUser()->setRedirect($this->getParent());

        $changes = $this->tableAdapter->save();
        if ($changes > 0) {
            $this->signalAllEventsAsync();
        }
    }

    public function prepareView(NethGui_Core_ViewInterface $view, $mode)
    {
        parent::prepareView($view, $mode);
        if ($mode == self::VIEW_REFRESH) {
            $view['__key'] = $this->key;
        }
    }

    public function isModal()
    {
        if ($this->getIdentifier() == 'delete')
        {
            return TRUE;
        }

        return parent::isModal();
    }

}