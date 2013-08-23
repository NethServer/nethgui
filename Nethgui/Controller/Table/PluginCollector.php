<?php
namespace Nethgui\Controller\Table;

/*
 * Copyright (C) 2012 Nethesis S.r.l.
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
 * A collection of modules automatically loaded from a specific directory
 *
 * Transfers the parent's adapter to all of its children
 * 
 * Collaborations:
 * - AdapterAggregateInterface, as the parent module
 * - AbstractAction, as children members of this composition
 * 
 * Refs #1091
 * 
 * @api
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
class PluginCollector extends \Nethgui\Controller\ListComposite implements \Nethgui\Adapter\AdapterAggregateInterface
{

    public function hasAdapter()
    {
        return $this->getAdapter() instanceof \Nethgui\Adapter\AdapterInterface;
    }

    public function getAdapter()
    {
        if ( ! $this->getParent() instanceof \Nethgui\Adapter\AdapterAggregateInterface) {
            throw new \LogicException(sprintf('%s: the parent module must implement \Nethgui\Adapter\AdapterAggregateInterface', __CLASS__), 1326732823);
        }
        return $this->getParent()->getAdapter();
    }

    /**
     * Add a child module, propagating the adpater settings.
     * 
     * @api
     * @param \Nethgui\Module\ModuleInterface $childModule
     * @return array
     */
    public function addChild(\Nethgui\Module\ModuleInterface $childModule)
    {
        if($this->hasAdapter() && $childModule instanceof AbstractAction) {
            $childModule->setAdapter($this->getAdapter());
        }
        return parent::addChild($childModule);
    }

    /**
     * Pass the request object and path segments to all of its children
     * 
     * @param \Nethgui\Controller\RequestInterface $request 
     */
    public function bind(\Nethgui\Controller\RequestInterface $request)
    {
        foreach ($this->getChildren() as $module) {
            if ($module instanceof \Nethgui\Controller\RequestHandlerInterface) {
                $module->bind($request->spawnRequest($module->getIdentifier()));
            }
        }
    }
   
}
