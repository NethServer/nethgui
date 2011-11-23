<?php
/**
 * @package Module
 * @subpackage Table
 * @author Davide Principi <davide.principi@nethesis.it>
 */

/**
 * Treats the table read case.
 * 
 * @see Nethgui\Module\Table\Modify
 * @see Nethgui\Module\TableController
 * @package Module
 * @subpackage Table 
 */
class Nethgui\Module\Table\Read extends Nethgui\Module\Table\Action
{

    /**
     *
     * @param array $columns
     */
    private $columns;

    /**
     *
     * @param string $identifier Module identifier
     * @param Nethgui\Adapter\AdapterInterface $tableAdapter Data source
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
                $this->columns[] = array('name' => strval($columnInfo), 'formatter' => ($columnInfo == 'Actions' ? 'fmtButtonset' : NULL));
            }
        }
    }

    public function prepareView(Nethgui\Core\ViewInterface $view, $mode)
    {
        parent::prepareView($view, $mode);
        $view['rows'] = $this->prepareRows($view, $mode);
        if ($mode == self::VIEW_SERVER) {
            $view['columns'] = $this->columns;
            // FIXME: implement pagination - on the client side:
            $view['tableClass'] = count($view['rows']) > 10 ? 'large-dataTable' : 'small-dataTable';
            $view['tableClass'] .= ' ' . $view->getClientEventTarget('rows');
            $view['tableId'] = $view->getUniqueId();
            $view['TableActions'] = $view->spawnView($this->getParent());
            $view['TableActions']->setTemplate(array($this, 'renderTableActions'));
        } elseif ($mode == self::VIEW_HELP) {
            // Ignore the view in help mode:
            $view->setTemplate(FALSE);
        }
    }

    public function renderTableActions(Nethgui\Renderer\Abstract $view)
    {
        $tableActions = $view->getModule()->getTableActions();
        $buttonList = $view->elementList()
            ->setAttribute('class', 'Buttonlist')
            ->setAttribute('wrap', 'div/');

        foreach ($tableActions as $tableAction) {
            $action = $tableAction->getIdentifier();

            if ($tableAction instanceof Nethgui\Module\Table\Help) {
                $button = $view->button('Help', Nethgui\Renderer\WidgetFactoryInterface::BUTTON_HELP);
            } else {
                $button = $view
                    ->button($action, Nethgui\Renderer\WidgetFactoryInterface::BUTTON_LINK)
                    ->setAttribute('value', array($action, '#' . $view->getUniqueId($action)));
            }

            $buttonList->insert($button);
        }
        return $buttonList;
    }

    protected function getActionIdentifier(Nethgui\Core\ModuleInterface $m)
    {
        return $m->getIdentifier();
    }

    private function prepareRows(Nethgui\Core\ViewInterface $view, $mode)
    {
        $rows = new ArrayObject();

        foreach ($this->tableAdapter as $key => $values) {
            $row = new ArrayObject();
            $rowMetadata = new ArrayObject(array('rowCssClass' => '', 'columns' => array()));
            $row[] = $rowMetadata;

            foreach ($this->columns as $columnIndex => $columnInfo) {
                $row[] = $this->prepareColumn($view, $mode, $columnIndex, $columnInfo['name'], $key, $values, $rowMetadata);
            }

            $rows[] = $row;
        }

        return $rows;
    }

    private function prepareColumn(Nethgui\Core\ViewInterface $view, $mode, $columnIndex, $column, $key, $values, &$rowMetadata)
    {
        $methodName = 'prepareViewForColumn' . ucfirst($column);

        if (method_exists($this->getParent(), $methodName)) {
            $columnValue = $this->getParent()->$methodName ($this, $view, $mode, $key, $values, $rowMetadata);
        } elseif (method_exists($this, $methodName)) {
            $columnValue =   $this->$methodName($view, $mode, $key, $values, $rowMetadata);
        } else {
            $columnValue = isset($values[$column]) ? $values[$column] : NULL;
        }

        return $columnValue;
    }

    public function prepareViewForColumnKey(Nethgui\Core\ViewInterface $view, $mode, $key, $values, &$rowMetadata)
    {
        return strval($key);
    }

    /**
     *
     * @param Nethgui\Core\ViewInterface $view
     * @param int $mode
     * @param string $key The data row key
     * @param array $values The data row values
     * @param array &$rowMetadata The metadadata row values, like css classes
     * @return Nethgui\Core\ViewInterface 
     */
    public function prepareViewForColumnActions(Nethgui\Core\ViewInterface $view, $mode, $key, $values, &$rowMetadata)
    {
        $cellView = $view->spawnView($this->getParent());
        $cellView->setTemplate(array($this, 'renderColumnActions'));

        foreach ($this->getParent()->getRowActions() as $action) {
            $actionId = $action->getIdentifier();
            $actionInfo = array();
            $actionInfo[] = $cellView->translate($actionId . '_label');

            if ($mode == self::VIEW_CLIENT) {
                $actionInfo[] = $cellView->getModuleUrl(sprintf('%s/%s#%s', $action->getIdentifier(), $key, $cellView->getUniqueId($actionId)));
            } else {
                $actionInfo[] = array($action->getIdentifier(), $key, '#' . $cellView->getUniqueId($actionId));
            }

            $cellView[$actionId] = $actionInfo;
        }

        return $cellView;
    }

    public function renderColumnActions(Nethgui\Renderer\Abstract $view)
    {
        $elementList = $view->elementList(Nethgui\Renderer\WidgetFactoryInterface::BUTTONSET)
            ->setAttribute('maxElements', 1);

        foreach ($view as $actionId => $actionInfo) {
            $button = $view
                ->button($actionId, Nethgui\Renderer\WidgetFactoryInterface::BUTTON_LINK)
                ->setAttribute('value', $actionInfo[1]);
            $elementList->insert($button);
        }

        return $elementList;
    }

}
