<?php
/**
 * @package Core
 * @subpackage Table
 * @author Davide Principi <davide.principi@nethesis.it>
 */

/**
 * @package Core
 * @subpackage Table
 */
class NethGui_Core_Table_ActionRead extends NethGui_Core_Module_Action
{

    public function initialize()
    {
        parent::initialize();
        $this->declareParameter('rows');
        $this->declareParameter('columns');
        $this->viewTemplate = 'NethGui_Core_View_ActionRead';
    }

    public function prepareView(NethGui_Core_ViewInterface $view, $mode)
    {
        $this->parameters['rows'] = $this->prepareRows($view, $mode);
        $this->parameters['columns'] = $this->getParent()->getTableColumns();
        parent::prepareView($view, $mode);
    }

    private function prepareRows(NethGui_Core_ViewInterface $view, $mode)
    {
        $rows = array();

        $columns = $this->getParent()->getTableColumns();
        $keyType = $this->getParent()->getKeyType();
        $db = $this->getParent()->getDatabase();

        foreach ($db->getAll($keyType) as $key => $values) {
            $row = array();

            // adds the key to the values:
            $values[$columns[0]] = $key;

            foreach ($columns as $columnIndex => $column) {
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