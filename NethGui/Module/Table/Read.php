<?php
/**
 * @package Module
 * @subpackage Table
 * @author Davide Principi <davide.principi@nethesis.it>
 */

/**
 * Treats the table read case.
 * 
 * @see NethGui_Module_Table_Modify
 * @see NethGui_Module_TableController
 * @package Module
 * @subpackage Table 
 */
class NethGui_Module_Table_Read extends NethGui_Module_Table_Action
{

    /**
     *
     * @param array $columns
     */
    private $columns;

    /**
     *
     * @param string $identifier Module identifier
     * @param NethGui_Adapter_AdapterInterface $tableAdapter Data source
     * @param array $columns The columns of the table
     * @param array $actions A list of actions that apply on the whole table
     * @param array $viewTemplate Optional
     */
    public function __construct($identifier, $columns)
    {
        parent::__construct($identifier, NULL);
        $this->columns = $columns;
    }

    public function prepareView(NethGui_Core_ViewInterface $view, $mode)
    {
        parent::prepareView($view, $mode);
        $view['rows'] = $this->prepareRows($view, $mode);
        if ($mode == self::VIEW_REFRESH) {
            $view['columns'] = $this->columns;

            $view['tableActions'] = new ArrayObject();

            foreach ($this->getParent()->getTableActions() as $tableAction) {
                $tableActionView = $view->spawnView($tableAction);
                $tableActionId = $tableAction->getIdentifier();
                $args = array('..', $tableActionId,  '#' . $tableActionView->getUniqueId());
                $view['tableActions'][] = array($tableActionId, NethGui_Renderer_Abstract::BUTTON_LINK, $args);
            }

            $view['tableClass'] = count($view['rows']) > 10 ? 'large-dataTable' : 'small-dataTable';
            $view['tableId'] = $view->getUniqueId();
        }
    }

    protected function getActionIdentifier(NethGui_Core_ModuleInterface $m)
    {
        return $m->getIdentifier();
    }

    private function prepareRows(NethGui_Core_ViewInterface $view, $mode)
    {
        $rows = new ArrayObject();

        foreach ($this->tableAdapter as $key => $values) {
            $row = new ArrayObject();

            foreach ($this->columns as $columnIndex => $column) {
                $row[] = $this->prepareColumn($view, $mode, $columnIndex, $column, $key, $values);
            }

            $rows[] = $row;
        }

        return $rows;
    }

    private function prepareColumn(NethGui_Core_ViewInterface $view, $mode, $columnIndex, $column, $key, $values)
    {
        $methodName = 'prepareViewForColumn' . ucfirst($column);

        if (method_exists($this->getParent(), $methodName)) {
            $columnValue = call_user_func(array($this->getParent(), $methodName), $this, $view, $mode, $key, $values);
        } elseif (method_exists($this, $methodName)) {
            $columnValue = call_user_func(array($this, $methodName), $view, $mode, $key, $values);
        } else {
            $columnValue = isset($values[$column]) ? $values[$column] : NULL;
        }

        return $columnValue;
    }

    public function prepareViewForColumnKey(NethGui_Core_ViewInterface $view, $mode, $key, $values)
    {
        return strval($key);
    }

    /**
     *
     * @param NethGui_Core_ViewInterface $view
     * @param int $mode
     * @param string $key The data row key
     * @param array $values The data row values
     * @return NethGui_Core_ViewInterface 
     */
    public function prepareViewForColumnActions(NethGui_Core_ViewInterface $view, $mode, $key, $values)
    {
        $columnView = $view->spawnView($this);
        $columnView->setTemplate(array($this, 'renderColumnActions'));

        foreach ($this->getParent()->getRowActions() as $action) {
            $actionView = $columnView->spawnView($action, TRUE);
            $actionView[] = $actionView->translate($action->getIdentifier() . '_label');
            $actionView[] = array($action->getIdentifier(), $key);
        }

        return $columnView;
    }

    public function renderColumnActions(NethGui_Renderer_Abstract $view)
    {
        $buttons = array();

        foreach ($view as $action => $actionView) {
            $args = array('..', $action, $actionView[1][1], '#' . $actionView->getUniqueId());
            $buttons[] = array($action, NethGui_Renderer_Abstract::BUTTON_LINK, $args);
        }

        return $view->buttonList($buttons);
    }

}