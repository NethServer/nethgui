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
class NethGui_Core_Module_TableRead extends NethGui_Core_Module_Action
{

    /**
     *
     * @var ArrayAccess
     */
    private $tableAdapter;
    /**
     *
     * @param array $columns
     */
    private $columns;
    /**
     * A list of actions that apply on the whole table
     * @var array
     */
    private $actions;

    /**
     *
     * @param string $identifier Module identifier
     * @param NethGui_Core_AdapterInterface $tableAdapter Data source
     * @param array $columns The columns of the table
     * @param array $actions A list of actions that apply on the whole table
     * @param array $viewTemplate Optional
     */
    public function __construct($identifier, NethGui_Core_AdapterInterface $tableAdapter, $columns, $actions, $viewTemplate = NULL)
    {
        parent::__construct($identifier);
        $this->columns = $columns;
        $this->viewTemplate = $viewTemplate;
        $this->tableAdapter = $tableAdapter;
        $this->actions = $actions;
    }

    public function prepareView(NethGui_Core_ViewInterface $view, $mode)
    {
        if ($mode == self::VIEW_REFRESH) {
            $view['columns'] = $this->columns;
            $view['actions'] = array_values($this->actions);
        }
        $view['rows'] = $this->prepareRows($view, $mode);
        parent::prepareView($view, $mode);
    }

    private function prepareRows(NethGui_Core_ViewInterface $view, $mode)
    {
        $rows = array();
               
        foreach ($this->tableAdapter as $key => $values) {
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

    private function prepareColumnValue(NethGui_Core_ViewInterface $view, $mode, $columnIndex, $column, $values)
    {
        $methodName = 'prepareColumn' . ucfirst($column);

        if (method_exists($this, $methodName)) {
            $columnValue = call_user_func(array($this, $methodName), $view, $mode, $values);
        } elseif (method_exists($this->getParent(), $methodName)) {
            $columnValue = call_user_func(array($this->getParent(), $methodName), $view, $mode, $values);
        } else {
            $columnValue = isset($values[$column]) ? $values[$column] : NULL;
        }

        return $columnValue;
    }

    public function prepareColumnActions(NethGui_Core_ViewInterface $view, $mode, $values)
    {
        if ($mode == self::VIEW_REFRESH) {
            $columnView = $view->spawnView($this);
            $columnView->setTemplate('NethGui_Core_View_TableActions');
        } else {
            $columnView = array();
        }

        $columnView['update'] = $view->buildUrl('..', 'update', $values['network']);
        $columnView['delete'] = $view->buildUrl('..', 'delete', $values['network']);

        return $columnView;
    }

}