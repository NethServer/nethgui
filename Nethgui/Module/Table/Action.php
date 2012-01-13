<?php
namespace Nethgui\Module\Table;

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
 * A Table Action receives a TableAdapter to modify a table
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 * @api
 */
abstract class Action extends \Nethgui\Module\Standard implements TableActionInterface, \Nethgui\Module\ActionInterface
{

    /**
     * @var \Nethgui\Adapter\AdapterInterface
     */
    protected $tableAdapter;

    public function setTableAdapter(\Nethgui\Adapter\AdapterInterface $tableAdapter)
    {
        if ( ! $this->hasTableAdapter()) {
            $this->tableAdapter = $tableAdapter;
        }
        return $this;
    }

    public function hasTableAdapter()
    {
        return ! is_null($this->tableAdapter);
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);
        if ( ! isset($view['Cancel'])) {
            $view['Cancel'] = $view->getModuleUrl('/' . \Nethgui\array_head($view->resolvePath('')));
        }
        if ( ! $this instanceof \Nethgui\Core\ModuleCompositeInterface) {
            $view['FormAction'] = $view->getModuleUrl(implode('/', $this->getRequest()->getPath()));
        }
    }

    public function getNextActionPath()
    {
        return '../read';
    }

}

