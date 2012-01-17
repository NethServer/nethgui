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
abstract class Action extends \Nethgui\Controller\Standard implements \Nethgui\Adapter\AdapterAggregateInterface
{

    /**
     * @return \Nethgui\Adapter\AdapterInterface
     */
    public function getAdapter()
    {
        if ( ! $this->getParent() instanceof \Nethgui\Adapter\AdapterAggregateInterface) {
            throw new \LogicException(sprintf('%s: the parent module must implement \Nethgui\Adapter\AdapterAggregateInterface', __CLASS__), 1326732824);
        }

        return $this->getParent()->getAdapter();
    }

    /**
     *
     * @return bool
     */
    public function hasAdapter()
    {
        return $this->getAdapter() instanceof \Nethgui\Adapter\AdapterInterface;
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);
        if ( ! isset($view['Cancel'])) {
            $view['Cancel'] = $view->getModuleUrl('/' . \Nethgui\array_head($view->resolvePath('')));
        }
        if ( ! $this instanceof \Nethgui\Module\ModuleCompositeInterface) {
            $view['FormAction'] = $view->getModuleUrl(implode('/', $this->getRequest()->getPath()));
        }
    }

    public function nextActionPath()
    {
        return '../read';
    }

}

