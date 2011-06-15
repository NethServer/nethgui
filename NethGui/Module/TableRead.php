<?php
/**
 * @package Module
 * @author Davide Principi <davide.principi@nethesis.it>
 */

/**
 * Treats the table read case.
 * 
 * @see NethGui_Module_TableModify
 * @see NethGui_Module_TableController
 * @package Module
 */
class NethGui_Module_TableRead extends NethGui_Core_Module_Standard
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
    private $tableActions;
    /**
     * Actions on columns
     * @var array
     */
    private $columnActions;

    /**
     *
     * @param string $identifier Module identifier
     * @param NethGui_Adapter_AdapterInterface $tableAdapter Data source
     * @param array $columns The columns of the table
     * @param array $actions A list of actions that apply on the whole table
     * @param array $viewTemplate Optional
     */
    public function __construct($identifier, NethGui_Adapter_AdapterInterface $tableAdapter, $columns, $tableActions, $columnActions, $viewTemplate = NULL)
    {
        parent::__construct($identifier);
        $this->columns = $columns;
        $this->viewTemplate = $viewTemplate;
        $this->tableAdapter = $tableAdapter;
        $this->tableActions = $tableActions;
        $this->columnActions = $columnActions;
    }

    public function prepareView(NethGui_Core_ViewInterface $view, $mode)
    {
        parent::prepareView($view, $mode);
        if ($mode == self::VIEW_REFRESH) {
            $view['columns'] = $this->columns;
            $view['tableActions'] = array_values($this->tableActions);
        }
        $view['rows'] = $this->prepareRows($view, $mode);        
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
               
        foreach ($this->columnActions as $action) {            
            $columnView[$action] = array($action, $key);
        }

        return $columnView;
    }

    public function renderColumnActions(NethGui_Renderer_Abstract $view)
    {
        $output = '';                       
        
        $fragmentPrefix = implode('_', array_slice($view->getModulePath(), 0, -1));
                        
        foreach ($this->columnActions as $action) {            
            $actionSegments = $view[$action];    
            
            $fragment = '#' . $fragmentPrefix . '_' . $action;
            
            // Must go up one level (we are in `read` action...)
            array_unshift($actionSegments, '..');
            
            $actionSegments[] = $fragment;
            
            $output .= '<li>' . $view->button($action, NethGui_Renderer_Abstract::BUTTON_LINK, $actionSegments) . '</li>';
        }
        return '<ul class="actions">' . $output . '</ul>';
    }

}