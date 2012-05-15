<?php
namespace Nethgui\Controller;

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
class TabsController extends \Nethgui\Controller\CompositeController
{

    public function renderIndex(\Nethgui\Renderer\Xhtml $view)
    {
        $view->includeFile('Nethgui/Js/jquery.nethgui.tabs.js');
        $view->includeFile('Nethgui/Js/jquery.nethgui.controller.js');

        $tabs = $view->tabs()->setAttribute('receiver', '');

        foreach ($this->getChildren() as $module) {
            $moduleIdentifier = $module->getIdentifier();

            $flags = \Nethgui\Renderer\WidgetFactoryInterface::INSET_WRAP;

            if ($this->needsAutoFormWrap($module)) {
                $flags |= \Nethgui\Renderer\WidgetFactoryInterface::INSET_FORM;
            }

            $action = $view->inset($moduleIdentifier, $flags)
                ->setAttribute('class', 'Action')
                ->setAttribute('title', $view->getTranslator()->translate($module, $moduleIdentifier . '_Title'))
                ;

            $tabs->insert($action);
        }

        return $tabs;
    }

}
