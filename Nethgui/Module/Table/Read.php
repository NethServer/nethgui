<?php
/**
 * @package Module
 * @subpackage Table
 * @author Davide Principi <davide.principi@nethesis.it>
 */

/**
 * Treats the table read case.
 * 
 * @see Nethgui_Module_Table_Modify
 * @see Nethgui_Module_TableController
 * @package Module
 * @subpackage Table 
 */
class Nethgui_Module_Table_Read extends Nethgui_Module_Table_Action
{

    /**
     *
     * @param array $columns
     */
    private $columns;

    /**
     *
     * @param string $identifier Module identifier
     * @param Nethgui_Adapter_AdapterInterface $tableAdapter Data source
     * @param array $columns The columns of the table
     * @param array $actions A list of actions that apply on the whole table
     * @param array $viewTemplate Optional
     */
    public function __construct($identifier, $columns)
    {
        parent::__construct($identifier, NULL);
        $this->columns = array();

        foreach ($columns as $columnInfo) {
            if (is_array($columnInfo)) {
                $this->columns[] = $columnInfo;
            } else {
                // FIXME: setting here the default buttonList formatter for Actions column:
                $this->columns[] = array('name' => strval($columnInfo), 'formatter' => ($columnInfo == 'Actions' ? 'Buttonset' : NULL));
            }
        }
    }

    public function prepareView(Nethgui_Core_ViewInterface $view, $mode)
    {
        parent::prepareView($view, $mode);
        $view['rows'] = $this->prepareRows($view, $mode);
        if ($mode == self::VIEW_SERVER) {
            $view['columns'] = $this->columns;
            // FIXME: implement pagination - on the client side:
            $view['tableClass'] = count($view['rows']) > PHP_INT_MAX ? 'large-dataTable' : 'small-dataTable';
            $view['tableClass'] .= ' ' . $view->getClientEventTarget('rows');
            $view['tableId'] = $view->getUniqueId();
        } elseif ($mode == self::VIEW_HELP) {
            // Ignore the view in help mode:
            $view->setTemplate(FALSE);
        }
    }

    protected function getActionIdentifier(Nethgui_Core_ModuleInterface $m)
    {
        return $m->getIdentifier();
    }

    private function prepareRows(Nethgui_Core_ViewInterface $view, $mode)
    {
        $rows = new ArrayObject();

        foreach ($this->tableAdapter as $key => $values) {
            $row = new ArrayObject();

            foreach ($this->columns as $columnIndex => $columnInfo) {
                $row[] = $this->prepareColumn($view, $mode, $columnIndex, $columnInfo['name'], $key, $values);
            }

            $rows[] = $row;
        }

        return $rows;
    }

    private function prepareColumn(Nethgui_Core_ViewInterface $view, $mode, $columnIndex, $column, $key, $values)
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

    public function prepareViewForColumnKey(Nethgui_Core_ViewInterface $view, $mode, $key, $values)
    {
        return strval($key);
    }

    /**
     *
     * @param Nethgui_Core_ViewInterface $view
     * @param int $mode
     * @param string $key The data row key
     * @param array $values The data row values
     * @return Nethgui_Core_ViewInterface 
     */
    public function prepareViewForColumnActions(Nethgui_Core_ViewInterface $view, $mode, $key, $values)
    {
        $cellView = $view->spawnView($this->getParent());
        $cellView->setTemplate(array($this, 'renderColumnActions'));

        foreach ($this->getParent()->getRowActions() as $action) {
            $actionId = $action->getIdentifier();
            $actionInfo = array();
            $actionInfo[] = $cellView->translate($actionId . '_label');
            $actionInfo[] = Nethgui_Framework::getInstance()->buildModuleUrl($this, array('..', $action->getIdentifier(), $key, '#' . $cellView->getUniqueId($actionId)));

            $cellView[$actionId] = $actionInfo;
        }

        return $cellView;
    }

    public function renderColumnActions(Nethgui_Renderer_Abstract $view)
    {
        $elementList = $view->elementList(Nethgui_Renderer_Abstract::BUTTONSET)
            ->setAttribute('maxElements', 1);

        foreach ($view as $actionId => $actionInfo) {
            $button = $view
                ->button($actionId, Nethgui_Renderer_Abstract::BUTTON_LINK)
                ->setAttribute('value', $actionInfo[1]);
            $elementList->insert($button);
        }

        return $elementList;
    }

}