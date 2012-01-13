<?php
namespace Nethgui\Module;

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
 * A List of modules that forwards request handling to its parts.
 * 
 * A List executes no action. It forwards each call to its subparts. 
 *
 * @see Composite
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 * @api
 */
class ListComposite extends Composite implements \Nethgui\Core\RequestHandlerInterface
{

    public function bind(\Nethgui\Core\RequestInterface $request)
    {
        $arguments = $request->getPath();
        $currentModuleIdentifier = \Nethgui\array_head($arguments);        
        foreach ($this->getChildren() as $childModule) {
            if ( ! $childModule instanceof \Nethgui\Core\RequestHandlerInterface) {
                continue;
            } elseif ($currentModuleIdentifier === $childModule->getIdentifier()) {
                // Forward arguments to submodule:
                $childModule->bind($request->spawnRequest($currentModuleIdentifier, \Nethgui\array_rest($arguments)));
            } else {
                $childModule->bind($request->spawnRequest($childModule->getIdentifier()));
            } 
        }
    }

    public function validate(\Nethgui\Core\ValidationReportInterface $report)
    {
        foreach ($this->getChildren() as $childModule) {
            if ( ! $childModule instanceof \Nethgui\Core\RequestHandlerInterface) {
                continue;
            }
            $childModule->validate($report);
        }
    }

    public function process()
    {
        foreach ($this->getChildren() as $childModule) {
            if ( ! $childModule instanceof \Nethgui\Core\RequestHandlerInterface) {
                continue;
            }
            $childModule->process();
        }
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);
        foreach ($this->getChildren() as $child) {
            $innerView = $view->spawnView($child, TRUE);
            $child->prepareView($innerView);
        }
    }

}
