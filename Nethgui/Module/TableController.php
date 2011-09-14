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
 * @see Nethgui_Module_Table_Modify
 * @see Nethgui_Module_Table_Read
 * @package Module
 */
class Nethgui_Module_TableController extends Nethgui_Core_Module_Controller
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
        $this->addChild(new Nethgui_Module_Table_Read('read', $columns));

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
        $tableAdapter = call_user_func_array(array($this->getHostConfiguration(), 'getTableAdapter'), $this->tableAdapterArguments);
        foreach ($this->getChildren() as $action) {
            if ($action instanceof Nethgui_Module_Table_Action
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
    public function addRowAction(Nethgui_Core_ModuleInterface $a)
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
    public function addTableAction(Nethgui_Core_ModuleInterface $a)
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

            $actionObject = new Nethgui_Module_Table_Modify($actionName, $parameterSchema, $requireEvents, $viewTemplate);
        }

        if ($actionArguments instanceof Nethgui_Module_Table_Action) {
            if ( ! is_null($tableAdapter))
            {
                $actionArguments->setTableAdapter($tableAdapter);
            }
            $actionObject = $actionArguments;
        } elseif ($actionArguments instanceof Nethgui_Core_Module_Standard) {
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
     * This callback template is invoked if the current view is not defined.
     * @param Nethgui_Renderer_Abstract $view
     * @return Nethgui_Renderer_WidgetInterface
     *
     * @todo refactor into parent class
     */
    public function renderDefault(Nethgui_Renderer_Abstract $view)
    {
        $widget = $view->panel();
        $widget->setAttribute('class', 'Table');

        $tableRead = $view->panel()->setAttribute('class', 'TableAction TableRead raised');
        $widget->insert($tableRead);

        foreach ($this->getChildren() as $index => $child) {
            // The FIRST child must ALWAYS be the "READ" action (default)
            if ($index === 0) {
                // insert the 'read' action
                $tableRead->insert($view->inset($child->getIdentifier()));
            } else {
                $flags = Nethgui_Renderer_Abstract::STATE_DISABLED;
                
                // Subsequent children are embedded into a DISABLED dialog frame.
                $actionWidget = $view->panel($flags)->insert(
                    $this->wrapFormAroundChild($view, $child->getIdentifier(), $flags)
               );

                $actionWidget->setAttribute('name', $child->getIdentifier());

                if ($child instanceof Nethgui_Module_Table_Action && $child->isModal()) {
                    $actionWidget->setAttribute('class', 'Dialog');
                } else {
                    $actionWidget->setAttribute('class', 'TableAction');
                }

                $widget->insert($actionWidget);
            }
        }

        $elementList = $view->elementList()->setAttribute('class', 'buttonList');

        foreach ($this->getTableActions() as $tableAction) {
            $action = $tableAction->getIdentifier();

            $button = $view
                ->button($action, Nethgui_Renderer_Abstract::BUTTON_LINK)
                ->setAttribute('value', array($action, '#' . $view->getUniqueId($action)));

            $elementList->insert($button);
        }

        $tableRead->insert($elementList);

        return $widget;
    }

    /**
     * @todo refactor into parent class
     */
    public function prepareView(Nethgui_Core_ViewInterface $view, $mode)
    {
        parent::prepareView($view, $mode);

        if (is_object($this->currentAction)
            && $this->getRequest()->isSubmitted()
            && $this->hasAction('read')) {
            // Load 'read' action when some other action has occurred,
            // to refresh the tabular data.
            $readAction = $this->getAction('read');
            $innerView = $view->spawnView($readAction, TRUE);
            $readAction->prepareView($innerView, $mode);
        } elseif (is_null($this->currentAction)) {
            // Handle a NULL current action, rendering all the children in a
            // "DISABLED" state. This is the default controller state,
            // where the table action buttons are displayed.
            foreach ($this->getChildren() as $childModule) {
                $innerView = $view->spawnView($childModule, TRUE);
                $childModule->prepareView($innerView, $mode);
            }

            $view->setTemplate(array($this, 'renderDefault'));
        }
    }

}