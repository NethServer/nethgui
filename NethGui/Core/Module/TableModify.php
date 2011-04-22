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
     * @var NethGui_Core_AdapterInterface
     */
    private $tableAdapter;

    public function __construct($identifier, NethGui_Core_AdapterInterface $tableAdapter, $parameterSchema, $viewTemplate = NULL)
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

    public function process()
    {
        parent::process();
        if ($this->performAction) {

            $action = $this->getActionName();

            if ($action == 'delete') {
                unset($this->tableAdapter[$this->parameters[$this->key]]);
            } elseif ($action == 'create' || $action == 'update') {

                $values = $this->parameters->getArrayCopy();

                $key = $this->parameters[$this->key];

                if (isset($values[$this->key])) {
                    unset($values[$this->key]);
                }

                $this->tableAdapter[$key] = $values;
            } else {
                throw new NethGui_Exception_HttpStatusClientError('Not found', 404);
            }
        }
    }

    public function prepareView(NethGui_Core_ViewInterface $view, $mode)
    {
        $view['key'] = $this->key;
        parent::prepareView($view, $mode);
    }

}