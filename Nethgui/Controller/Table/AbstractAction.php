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
 * A Table Action receives the table adapter object through the setAdapter()
 * method.
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 * @api
 */
abstract class AbstractAction extends \Nethgui\Controller\AbstractController implements \Nethgui\Adapter\AdapterAggregateInterface
{
    /**
     *
     * @var \Nethgui\Adapter\AdapterInterface
     */
    private $adapter;

    /**
     * @return \Nethgui\Adapter\AdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * @return bool
     */
    public function hasAdapter()
    {
        return $this->getAdapter() instanceof \Nethgui\Adapter\AdapterInterface;
    }

    /**
     * Receive the adapter object from the TableController
     * 
     * @param \Nethgui\Adapter\AdapterInterface $adapter
     * @return \Nethgui\Controller\Table\AbstractAction 
     */
    public function setAdapter(\Nethgui\Adapter\AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
        return $this;
    }

    /**
     * Add default Cancel and FormAction URLs 
     * 
     * @param \Nethgui\View\ViewInterface $view 
     */
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

    /**
     * @return bool The adapter save() exit status
     */
    protected function saveParameters()
    {

        $save1 = parent::saveParameters();
        $save2 = $this->getAdapter()->save();
        return $save1 || $save2;
    }

    public function nextPath()
    {
        return $this->getRequest()->isMutation() ? 'read' : $this->getIdentifier();
    }
}
