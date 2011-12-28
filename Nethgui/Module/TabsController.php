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
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 * @api
 */
class TabsController extends \Nethgui\Core\Module\Controller
{

    public function renderIndex(\Nethgui\Renderer\Xhtml $view)
    {
        $view->includeFile('jquery.nethgui.tabs.js', 'Nethgui');

        $tabs = $view->tabs();

        foreach ($this->getChildren() as $module) {
            $moduleIdentifier = $module->getIdentifier();

            $flags = \Nethgui\Renderer\WidgetFactoryInterface::STATE_UNOBSTRUSIVE
                | \Nethgui\Renderer\WidgetFactoryInterface::INSET_WRAP;

            if ($module instanceof \Nethgui\Core\RequestHandlerInterface) {
                $flags |= \Nethgui\Renderer\WidgetFactoryInterface::INSET_FORM;
            }

            $action = $view->inset($moduleIdentifier, $flags)->setAttribute('class', 'Action');

            $tabs->insert($action);
        }

        return $tabs;
    }

}
