<?php
namespace Nethgui\Module;
/*
 * Copyright (C) 2013 Nethesis S.r.l.
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
 * TODO: add component description here
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
class Language extends \Nethgui\Controller\AbstractController
{
    public function initialize()
    {
        parent::initialize();
        $this->declareParameter('switch', \Nethgui\System\PlatformInterface::ANYTHING);
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);
        if($this->parameters['switch']) {
            $view->getCommandList()
                ->httpHeader('HTTP/1.1 302 Found')
                ->httpHeader('Location: ' . $view->getSiteUrl() . $view->getPathUrl() .$this->parameters['switch'] . '/'  . implode('/', $this->getRequest()->getOriginalPath()));
        }
    }
}