<?php
/**
 * Nethgui
 *
 * @package Module
 */

/**
 * A Controller for handling a generic table CRUD scenario, and any other
 * action defined on a table.
 * 
 * - Tracks the actions involving a row
 * - Tracks the actions involving the whole table
 *
 * @see Nethgui\Module\Table\Modify
 * @see Nethgui\Module\Table\Read
 * @package Module
 */
class Nethgui\Module\TableController extends Nethgui\Core\Module\Controller 
{

    /**
     *
     * @var array
     */
    private $tableAdapterArguments;

    /**
     * @var array
     */
    private $rowActions;

    /**
     * @var array
     */
    private $tableActions;

    /**
     * @param string $identifier
     * @param array $tableAdapterArguments     
     * @param array $columns
     * @param array $rowActions
     * @param array $tableActions
     */
    public function __construct($identifier, $tableAdapterArguments, $columns, $rowActions, $tableActions)
    {
        parent::__construct($identifier);
        $this->tableAdapterArguments = $tableAdapterArguments;

        /*
         *  Create and add the READ action, that displays the table.
         */
        $this->addChild(new Nethgui\Module\Table\Read('read', $columns));

        foreach ($rowActions as $actionArguments) {
            $actionObject = $this->createActionObject($actionArguments);
            $this->addRowAction($actionObject);
        }

        foreach ($tableActions as $actionArguments) {
            $actionObject = $this->createActionObject($actionArguments);
            $this->addTableAction($actionObject);
        }
    }

    public function initialize()
    {
        /*
         * Create the table adapter object and assign it to every children, if
         * it has not been done before.
         */
        $tableAdapter = call_user_func_array(array($this->getPlatform(), 'getTableAdapter'), $this->tableAdapterArguments);
        foreach ($this->getChildren() as $action) {
            if ($action instanceof Nethgui\Module\Table\Action
                && ! $action->hasTableAdapter())
            {
                $action->setTableAdapter($tableAdapter);
            }
        }

        /**
         * Calling the parent method at this point ensures that the table
         * adapter has been set BEFORE the child initialization
         */
        parent::initialize();
    }

    /**
     * A column action is executed in a row context (i.e. row updating, deletion...)
     * @see getRowActions()
     */
    public function addRowAction(Nethgui\Core\ModuleInterface $a)
    {
        $this->rowActions[] = $a;
        $this->addChild($a);
    }

    /**
     * Actions for a single row of the table
     * @return array
     */
    public function getRowActions()
    {
        return $this->rowActions;
    }

    /**
     * A table action involves the whole table (i.e. create a new row, 
     * print the table...)
     * @see getTableActions()
     */
    public function addTableAction(Nethgui\Core\ModuleInterface $a)
    {
        $this->tableActions[] = $a;
        $this->addChild($a);
    }

    /**
     * Actions for the whole table
     * @return array
     */
    public function getTableActions()
    {
        return $this->tableActions;
    }

    private function createActionObject($actionArguments, $tableAdapter = NULL)
    {
        $actionObject = NULL;

        if (is_array($actionArguments)) {

            list($actionName, $parameterSchema, $requireEvents, $viewTemplate) = $actionArguments;

            if (is_string($requireEvents)) {
                $requireEvents = array($requireEvents);
            }

            $actionObject = new Nethgui\Module\Table\Modify($actionName, $parameterSchema, $requireEvents, $viewTemplate);
        }

        if ($actionArguments instanceof Nethgui\Module\Table\Action) {
            if ( ! is_null($tableAdapter))
            {
                $actionArguments->setTableAdapter($tableAdapter);
            }
            $actionObject = $actionArguments;
        } elseif ($actionArguments instanceof Nethgui\Core\Module\Standard) {
            $actionObject = $actionArguments;
        }

        return $actionObject;
    }

    protected function getCurrentActionParameter($parameter)
    {
        if ( ! isset($this->currentAction)) {
            return NULL;
        }

        $currentActionRequest = $this->getRequest()->getParameterAsInnerRequest($this->currentAction->getIdentifier());

        if ( ! $currentActionRequest->hasParameter($parameter)) {
            return NULL;
        }

        return $currentActionRequest->getParameter($parameter);
    }

    /**
     * @todo refactor into parent class
     */
    public function prepareView(Nethgui\Core\ViewInterface $view, $mode)
    {
        parent::prepareView($view, $mode);

        if (is_object($this->currentAction)
            && $mode == self::VIEW_CLIENT
            && $this->getRequest()->isSubmitted()
            && $this->hasAction('read')) {
            // Load 'read' action when some other action has occurred,
            // to refresh the tabular data.
            $readAction = $this->getAction('read');
            $innerView = $view->spawnView($readAction, TRUE);
            $readAction->prepareView($innerView, $mode);
        }
    }

    /**
     *
     * @param array $createDefaults
     * @return Nethgui\Module\TableController
     */
    protected function setCreateDefaults($createDefaults)
    {
        $create = $this->getAction('create');
        if (is_null($create)) {
            return $this;
        }

        $create->setCreateDefaults($createDefaults);

        return $this;
    }

    public function getDefaultUiStyleFlags()
    {
        return self::STYLE_CONTAINER_TABLE | parent::getDefaultUiStyleFlags();
    }

}
