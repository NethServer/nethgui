<?php
namespace Nethgui\Controller\Table;

/*
 * Copyright (C) 2011 Nethesis S.r.l.
 * 
 * This script is part of NethServer.
 * 
 * NethServer is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * NethServer is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with NethServer.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Display a table adapter as a TABLE element
 *
 * Each column is rendered through a prepareColumn<columnName>() callback that
 * you should define on the parent module or on the inheritor class.
 *
 * Note that a simple implementation for 'Key' and 'Actions'
 * columns is provided by this class.
 * 
 * @see Modify
 * @see \Nethgui\Controller\Table
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 * @api
 */
class Read extends AbstractAction
{

    /**
     * @param array $columns
     */
    private $columns;

    public function __construct($identifier = NULL)
    {
        parent::__construct('read');
    }

    /**
     * Set the column formatting attributes
     *
     * Call before prepareView()
     *
     * @param array $columns
     * @return Read
     */
    public function setColumns($columns)
    {
        foreach ($columns as $columnInfo) {
            if (is_array($columnInfo)) {
                $this->columns[] = $columnInfo;
            } else {
                // FIXME: setting here the default buttonList formatter for Actions column:
                $this->columns[] = array('name' => strval($columnInfo), 'formatter' => ($columnInfo == 'Actions' ? 'fmtButtonset' : NULL));
            }
        }
        return $this;
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);
        $view->setTemplate('Nethgui\Template\Table\Read');
        
        $view['rows'] = $this->prepareRows($view);

        if ($view->getTargetFormat() !== $view::TARGET_XHTML) {
            return;
        }

        $view['columns'] = $this->columns;

        // FIXME: implement pagination - on the client side:
        $view['tableClass'] = sprintf('%s %s', count($view['rows']) > 10 ? 'large-dataTable' : 'small-dataTable', $view->getClientEventTarget('rows'));
        $view['tableId'] = $view->getUniqueId();
        $view['tableTitle'] = $view->getTranslator()->translate($this->getParent(), $this->getParent()->getAttributesProvider()->getTitle());
        $view['TableActions'] = $view->spawnView($this->getParent());
        $view['TableActions']->setTemplate(array($this, 'renderTableActions'));
    }

    /**
     * Generate the table actions button list
     *
     * @param \Nethgui\Renderer\Xhtml $view A parent controller view
     * @return \Nethgui\Renderer\WidgetInterface
     */
    public function renderTableActions(\Nethgui\Renderer\Xhtml $view)
    {
        $tableActions = $view->getModule()->getTableActions();
        $buttonList = $view->elementList()
            ->setAttribute('class', 'Buttonlist')
            ->setAttribute('wrap', 'div/');

        foreach ($tableActions as $tableAction) {
            $actionId = $tableAction->getIdentifier();

            if ($tableAction instanceof Help) {
                $button = $view->button('Help', \Nethgui\Renderer\WidgetFactoryInterface::BUTTON_HELP);
            } else {
                $button = $view
                    ->button($actionId, \Nethgui\Renderer\WidgetFactoryInterface::BUTTON_LINK)
                    ->setAttribute('value', $view->getModuleUrl($actionId));
            }

            $buttonList->insert($button);
        }
        return $buttonList;
    }

    private function prepareRows(\Nethgui\View\ViewInterface $view)
    {
        $rows = new \ArrayObject();

        if ( ! $this->hasAdapter()) {
            throw new \UnexpectedValueException(sprintf('%s: the table adapter has not been set.', __CLASS__), 1326474696);
        }

        foreach ($this->getAdapter() as $key => $values) {
            $row = new \ArrayObject();
            $rowMetadata = new \ArrayObject(array('rowCssClass' => '', 'columns' => array()));
            $row[] = $rowMetadata;

            foreach ($this->columns as $columnIndex => $columnInfo) {
                $row[] = $this->prepareColumn($view, $columnIndex, $columnInfo['name'], $key, $values, $rowMetadata);
            }

            $rows[] = $row;
        }

        return $rows;
    }

    private function prepareColumn(\Nethgui\View\ViewInterface $view, $columnIndex, $column, $key, $values, &$rowMetadata)
    {
        $methodName = 'prepareViewForColumn' . ucfirst($column);

        if (method_exists($this->getParent(), $methodName)) {
            $columnValue = $this->getParent()->$methodName($this, $view, $key, $values, $rowMetadata);
        } elseif (method_exists($this, $methodName)) {
            $columnValue = $this->$methodName($view, $key, $values, $rowMetadata);
        } else {
            $columnValue = isset($values[$column]) ? $values[$column] : NULL;
        }

        return $columnValue;
    }

    public function prepareViewForColumnKey(\Nethgui\View\ViewInterface $view, $key, $values, &$rowMetadata)
    {
        return strval($key);
    }

    /**
     *
     * @param \Nethgui\View\ViewInterface $view
     * @param string $key The data row key
     * @param array $values The data row values
     * @param array &$rowMetadata The metadadata row values, like css classes
     * @return \Nethgui\View\ViewInterface 
     */
    public function prepareViewForColumnActions(\Nethgui\View\ViewInterface $view, $key, $values, &$rowMetadata)
    {
        $cellView = $view->spawnView($this->getParent());
        $cellView->setTemplate(array($this, 'renderColumnActions'));

        foreach ($this->getParent()->getRowActions() as $action) {
            $actionId = $action->getIdentifier();
            $actionInfo = array();
            $actionInfo[] = $cellView->translate($actionId . '_label');
            $actionInfo[] = $cellView->getModuleUrl(sprintf('%s/%s', $action->getIdentifier(), $key));
            $cellView[$actionId] = $actionInfo;
        }

        return $cellView;
    }

    public function renderColumnActions(\Nethgui\Renderer\Xhtml $view)
    {
        $buttonList = $view->buttonList(\Nethgui\Renderer\WidgetFactoryInterface::BUTTONSET)
            ->setAttribute('maxElements', 1);

        foreach ($view as $actionId => $actionInfo) {
            $button = $view
                ->button($actionId, \Nethgui\Renderer\WidgetFactoryInterface::BUTTON_LINK)
                ->setAttribute('value', $actionInfo[1]);
            $buttonList->insert($button);
        }

        return $buttonList;
    }

    public function nextPath()
    {
        // Deferred response must not change the current view, so keep the current view path.
        return $this->getRequest()->hasArgument('deferred') ? FALSE : parent::nextPath();
    }

}
