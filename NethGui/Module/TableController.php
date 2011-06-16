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
    private $columnActions, $tableActions;

    /**
     * @param string $identifier
     * @param array $tableAdapterArguments     
     * @param array $parameterSchema
     * @param array $columns
     * @param array $columnActions
     * @param array $tableActions
     */
    public function __construct($identifier, $tableAdapterArguments, $parameterSchema, $columns, $columnActions, $tableActions)
    {
        parent::__construct($identifier);
        $this->tableAdapterArguments = $tableAdapterArguments;
        $this->columns = $columns;
        $this->parameterSchema = $parameterSchema;
        $this->columnActions = $columnActions;
        $this->tableActions = $tableActions;
    }

    public function initialize()
    {
        parent::initialize();

        $tableAdapter = call_user_func_array(array($this->getHostConfiguration(), 'getTableAdapter'), $this->tableAdapterArguments);

        $actionObjects = array(0 => FALSE); // set the read action object placeholder.

        foreach ($this->columnActions as $actionArguments) {
            $actionObject = $this->createActionObject($actionArguments, $tableAdapter);
            $columnActions[] = $actionObject->getIdentifier();
            $actionObjects[] = $actionObject;
        }

        foreach ($this->tableActions as $actionArguments) {
            $actionObject = $this->createActionObject($actionArguments, $tableAdapter);
            $tableActions[] = $actionObject->getIdentifier();
            $actionObjects[] = $actionObject;
        }

        // add the read case
        $actionObjects[0] = new NethGui_Module_TableRead('read', $tableAdapter, $this->columns, $tableActions, $columnActions);

        // Finally add all the actions
        foreach ($actionObjects as $actionObject) {
            $this->addChild($actionObject);
        }
    }

    private function createActionObject($actionArguments, $tableAdapter)
    {
        $actionObject = NULL;

        if ($actionArguments instanceof NethGui_Core_Module_Standard) {
            $actionObject = $actionArguments;
        } else {

            list($actionName, $requireEvents, $viewTemplate) = $actionArguments;

            if (is_string($requireEvents)) {
                $requireEvents = array($requireEvents);
            }

            $actionObject = new NethGui_Module_TableModify($actionName, $tableAdapter, $this->parameterSchema, $requireEvents, $viewTemplate);
        }

        return $actionObject;
    }

    public function renderDisabledActions(NethGui_Renderer_Abstract $view)
    {

        // Only a root module emits FORM tag:
        if (is_null($this->getParent())) {
            $renderer = $view->form();
        } else {
            $renderer = $view;
        }

        foreach ($this->getChildren() as $index => $child) {
            if ($index === 0) {
                $renderer->inset($child->getIdentifier());
            } else {
                // FIXME: specify dialog style elsewhere...
                if($child->getIdentifier() == 'delete') {
                    $dialogStyle = NethGui_Renderer_Abstract::DIALOG_MODAL;
                } else {
                    $dialogStyle = NethGui_Renderer_Abstract::DIALOG_EMBEDDED;
                }
                
                $renderer                
                    ->dialog($child->getIdentifier(), $dialogStyle | NethGui_Renderer_Abstract::STATE_DISABLED)
                    ->inset($child->getIdentifier())
                ;
            }
        }

        return $view;
    }

    public function prepareView(NethGui_Core_ViewInterface $view, $mode)
    {
        parent::prepareView($view, $mode);
        if ( ! is_null($this->currentAction)) {
            return;
        }

        // Handle a NULL current action:
        foreach ($this->getChildren() as $childModule) {
            $innerView = $view->spawnView($childModule, TRUE);
            $childModule->prepareView($innerView, $mode);
        }

        $view->setTemplate(array($this, 'renderDisabledActions'));
    }

}