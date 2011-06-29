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

            foreach (array_map(array($this, 'getActionIdentifier'), $this->getParent()->getTableActions()) as $tableAction) {
                $fragment = implode('_', array_slice($view->getModulePath(), 0, -1)) . '_' . $tableAction;
                $view['tableActions'][] = array($tableAction, NethGui_Renderer_Abstract::BUTTON_LINK, '../' . $tableAction . '/#' . $fragment);
            }

            $view['tableClass'] = count($view['rows']) > 10 ? 'large-dataTable' : 'small-dataTable';
        }
    }

    protected function getActionIdentifier(NethGui_Core_ModuleInterface $m)
    {
        return $m->getIdentifier();
    }

    private function prepareRows(NethGui_Core_ViewInterface $view, $mode)
    {
        $rows = array();

        foreach ($this->tableAdapter as $key => $values) {
            $row = array();


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
        if ($mode == self::VIEW_REFRESH) {
            $columnView = $view->spawnView($this);
            $columnView->setTemplate(array($this, 'renderColumnActions'));
        } else {
            $columnView = array();
        }

        foreach (array_map(array($this, 'getActionIdentifier'), $this->getParent()->getRowActions()) as $action) {
            if ($mode == self::VIEW_UPDATE) {
                // FIXME: using a module surrogate method where no surrogate is needed:
                // refactor ModuleSurrogate, relocating the useful code elsewhere.
                $s = new NethGui_Core_ModuleSurrogate($this);
                $columnView[$action] = array(T($action . '_label', NULL, NULL, $s->getLanguageCatalog()), array($action, $key));
            } else {
                $columnView[$action] = $key;
            }
        }

        return $columnView;
    }

    public function renderColumnActions(NethGui_Renderer_Abstract $view)
    {
        $fragmentPrefix = implode('_', array_slice($view->getModulePath(), 0, -1));
        $buttons = array();

        foreach (array_map(array($this, 'getActionIdentifier'), $this->getParent()->getRowActions()) as $action) {
            $actionSegments = array('..', $action, $view[$action], '#' . $fragmentPrefix . '_' . $action);
            $buttons[] = array($action, NethGui_Renderer_Abstract::BUTTON_LINK, $actionSegments);
        }

        return $view->buttonList($buttons);
    }

}